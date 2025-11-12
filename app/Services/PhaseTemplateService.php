<?php

namespace App\Services;

use App\Models\Project;
use App\Models\MethodologyTemplate;
use App\Models\PhaseTemplate;
use App\Models\Phase;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class PhaseTemplateService
{
    /**
     * Instancie toutes les phases d'une méthodologie dans un projet
     *
     * @param Project $project
     * @param MethodologyTemplate $methodology
     * @param bool $includeSubPhases Inclure les sous-phases des templates
     * @return Collection<Phase>
     */
    public function instantiateForProject(
        Project $project,
        MethodologyTemplate $methodology,
        bool $includeSubPhases = true
    ): Collection {
        $phases = collect();

        // Récupérer toutes les phases racines (incluant héritées du parent)
        $phaseTemplates = $methodology->getAllPhases()->filter(fn($p) => $p->isRoot());

        foreach ($phaseTemplates as $template) {
            $phase = $this->instantiatePhaseTemplate($project, $template);
            $phases->push($phase);

            // Instancier récursivement les sous-phases si demandé
            if ($includeSubPhases && $template->hasChildren()) {
                $subPhases = $this->instantiateChildPhases($project, $template, $phase);
                $phases = $phases->merge($subPhases);
            }
        }

        return $phases;
    }

    /**
     * Instancie une phase template unique dans un projet
     *
     * @param Project $project
     * @param PhaseTemplate $template
     * @param Phase|null $parentPhase
     * @return Phase
     */
    public function instantiatePhaseTemplate(
        Project $project,
        PhaseTemplate $template,
        ?Phase $parentPhase = null
    ): Phase {
        // Calculer les dates basées sur project start_date + typical_duration
        $startDate = $this->calculatePhaseStartDate($project, $template, $parentPhase);
        $endDate = $this->calculatePhaseEndDate($project, $template, $startDate);

        return Phase::create([
            'project_id' => $project->id,
            'phase_template_id' => $template->id,
            'parent_phase_id' => $parentPhase?->id,
            'level' => $template->level,
            'name' => $template->name,
            'description' => $template->description,
            'sequence' => $template->sequence,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'not_started',
            'completion_percentage' => 0,
        ]);
    }

    /**
     * Instancie les sous-phases récursivement
     *
     * @param Project $project
     * @param PhaseTemplate $template
     * @param Phase $parentPhase
     * @return Collection<Phase>
     */
    private function instantiateChildPhases(
        Project $project,
        PhaseTemplate $template,
        Phase $parentPhase
    ): Collection {
        $childPhases = collect();

        foreach ($template->childPhases as $childTemplate) {
            $childPhase = $this->instantiatePhaseTemplate($project, $childTemplate, $parentPhase);
            $childPhases->push($childPhase);

            // Récursion pour sous-sous-phases
            if ($childTemplate->hasChildren()) {
                $subChildren = $this->instantiateChildPhases($project, $childTemplate, $childPhase);
                $childPhases = $childPhases->merge($subChildren);
            }
        }

        return $childPhases;
    }

    /**
     * Fait hériter les phases d'une méthodologie parent
     * Duplique toutes les phases du parent dans la méthodologie enfant
     *
     * @param MethodologyTemplate $methodology
     * @return Collection<PhaseTemplate>
     */
    public function inheritPhasesFromParent(MethodologyTemplate $methodology): Collection
    {
        if (!$methodology->hasParent()) {
            return collect();
        }

        $inheritedPhases = collect();
        $parentPhases = $methodology->parentMethodology->phaseTemplates()->rootPhases()->get();

        foreach ($parentPhases as $parentPhase) {
            // Dupliquer la phase dans la méthodologie enfant
            $duplicatedPhase = $this->duplicatePhaseTemplate($parentPhase, $methodology);
            $inheritedPhases->push($duplicatedPhase);
        }

        return $inheritedPhases;
    }

    /**
     * Duplique un template de phase (avec ses sous-phases) dans une nouvelle méthodologie
     *
     * @param PhaseTemplate $source Template source à dupliquer
     * @param MethodologyTemplate $targetMethodology Méthodologie cible
     * @param PhaseTemplate|null $newParent Nouveau parent (pour sous-phases)
     * @return PhaseTemplate
     */
    public function duplicatePhaseTemplate(
        PhaseTemplate $source,
        MethodologyTemplate $targetMethodology,
        ?PhaseTemplate $newParent = null
    ): PhaseTemplate {
        // Dupliquer la phase
        $duplicate = $source->replicate();
        $duplicate->methodology_template_id = $targetMethodology->id;
        $duplicate->parent_phase_id = $newParent?->id;
        $duplicate->save();

        // Dupliquer récursivement les sous-phases
        foreach ($source->childPhases as $childPhase) {
            $this->duplicatePhaseTemplate($childPhase, $targetMethodology, $duplicate);
        }

        return $duplicate;
    }

    /**
     * Calcule la date de début d'une phase
     *
     * @param Project $project
     * @param PhaseTemplate $template
     * @param Phase|null $parentPhase
     * @return Carbon|null
     */
    private function calculatePhaseStartDate(
        Project $project,
        PhaseTemplate $template,
        ?Phase $parentPhase = null
    ): ?Carbon {
        // Si sous-phase, utiliser la date de début du parent
        if ($parentPhase) {
            return $parentPhase->start_date?->copy();
        }

        // Si phase racine, calculer depuis le début du projet
        if (!$project->start_date) {
            return null;
        }

        // Trouver toutes les phases précédentes
        $previousPhases = PhaseTemplate::where('methodology_template_id', $template->methodology_template_id)
                                       ->where('sequence', '<', $template->sequence)
                                       ->whereNull('parent_phase_id')
                                       ->get();

        // Calculer la durée cumulée des phases précédentes
        $cumulativeDays = 0;
        foreach ($previousPhases as $prevPhase) {
            $duration = $prevPhase->getTypicalDurationDays($project->getDuration());
            if ($duration) {
                $cumulativeDays += $duration;
            }
        }

        return $project->start_date->copy()->addDays($cumulativeDays);
    }

    /**
     * Calcule la date de fin d'une phase
     *
     * @param Project $project
     * @param PhaseTemplate $template
     * @param Carbon|null $startDate
     * @return Carbon|null
     */
    private function calculatePhaseEndDate(
        Project $project,
        PhaseTemplate $template,
        ?Carbon $startDate
    ): ?Carbon {
        if (!$startDate) {
            return null;
        }

        $duration = $template->getTypicalDurationDays($project->getDuration());

        if (!$duration) {
            // Si pas de durée définie, utiliser 30 jours par défaut
            $duration = 30;
        }

        return $startDate->copy()->addDays($duration - 1); // -1 car le dernier jour est inclus
    }

    /**
     * Met à jour les dates de toutes les phases d'un projet
     * Utile après modification des dates du projet ou d'une phase
     *
     * @param Project $project
     * @return void
     */
    public function recalculatePhaseDates(Project $project): void
    {
        $rootPhases = $project->phases()->rootPhases()->ordered()->get();

        $currentDate = $project->start_date?->copy();

        foreach ($rootPhases as $phase) {
            if (!$currentDate) {
                break;
            }

            // Calculer la durée de la phase
            $template = $phase->template;
            $duration = 30; // Défaut

            if ($template) {
                $duration = $template->getTypicalDurationDays($project->getDuration()) ?? 30;
            } elseif ($phase->start_date && $phase->end_date) {
                // Si pas de template, utiliser la durée actuelle
                $duration = $phase->start_date->diffInDays($phase->end_date) + 1;
            }

            // Mettre à jour les dates
            $phase->start_date = $currentDate->copy();
            $phase->end_date = $currentDate->copy()->addDays($duration - 1);
            $phase->save();

            // Mettre à jour les sous-phases
            $this->recalculateSubPhaseDates($phase);

            // Avancer à la phase suivante
            $currentDate->addDays($duration);
        }
    }

    /**
     * Recalcule les dates des sous-phases d'une phase
     *
     * @param Phase $parentPhase
     * @return void
     */
    private function recalculateSubPhaseDates(Phase $parentPhase): void
    {
        $subPhases = $parentPhase->childPhases;

        if ($subPhases->isEmpty()) {
            return;
        }

        $currentDate = $parentPhase->start_date?->copy();

        foreach ($subPhases as $subPhase) {
            if (!$currentDate) {
                break;
            }

            $template = $subPhase->template;
            $duration = 15; // Défaut pour sous-phases

            if ($template) {
                // Pour sous-phases, utiliser la durée du parent comme base
                $parentDuration = $parentPhase->getDuration() ?? 30;
                $duration = $template->getTypicalDurationDays($parentDuration) ?? 15;
            } elseif ($subPhase->start_date && $subPhase->end_date) {
                $duration = $subPhase->start_date->diffInDays($subPhase->end_date) + 1;
            }

            $subPhase->start_date = $currentDate->copy();
            $subPhase->end_date = $currentDate->copy()->addDays($duration - 1);
            $subPhase->save();

            // Récursion pour sous-sous-phases
            $this->recalculateSubPhaseDates($subPhase);

            $currentDate->addDays($duration);
        }
    }

    /**
     * Crée une nouvelle méthodologie custom en héritant d'une méthodologie existante
     *
     * @param MethodologyTemplate $parent
     * @param string $name
     * @param int|null $organizationId
     * @param string|null $description
     * @return MethodologyTemplate
     */
    public function createCustomMethodologyFromParent(
        MethodologyTemplate $parent,
        string $name,
        ?int $organizationId = null,
        ?string $description = null
    ): MethodologyTemplate {
        // Créer la nouvelle méthodologie
        $methodology = MethodologyTemplate::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => $description,
            'category' => 'custom',
            'organization_id' => $organizationId,
            'parent_methodology_id' => $parent->id,
            'is_system' => false,
            'is_active' => true,
        ]);

        // Hériter les phases du parent
        $this->inheritPhasesFromParent($methodology);

        return $methodology;
    }

    /**
     * Ajoute une phase custom à une méthodologie
     *
     * @param MethodologyTemplate $methodology
     * @param string $name
     * @param int $sequence
     * @param array $additionalData
     * @return PhaseTemplate
     */
    public function addCustomPhase(
        MethodologyTemplate $methodology,
        string $name,
        int $sequence,
        array $additionalData = []
    ): PhaseTemplate {
        return PhaseTemplate::create(array_merge([
            'methodology_template_id' => $methodology->id,
            'name' => $name,
            'sequence' => $sequence,
            'level' => 1,
            'phase_type' => 'custom',
        ], $additionalData));
    }
}
