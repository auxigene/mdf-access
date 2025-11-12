<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MethodologyTemplate;
use App\Models\PhaseTemplate;

class MethodologyTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Methodology Templates...');

        // 1. Méthodologie PMBOK Waterfall (Système)
        $this->createPmbokWaterfall();

        // 2. Méthodologie Agile Scrum (Système)
        $this->createAgileScrum();

        // 3. Méthodologie Hybrid (Système)
        $this->createHybrid();

        $this->command->info('✅ Methodology Templates seeded successfully!');
        $this->command->info('   - Methodologies: ' . MethodologyTemplate::count());
        $this->command->info('   - Phase Templates: ' . PhaseTemplate::count());
    }

    /**
     * Créer la méthodologie PMBOK Waterfall
     */
    private function createPmbokWaterfall(): void
    {
        $this->command->info('📘 Creating PMBOK Waterfall methodology...');

        $pmbok = MethodologyTemplate::create([
            'name' => 'PMBOK Waterfall',
            'name_fr' => 'PMBOK Cascade',
            'slug' => 'pmbok-waterfall',
            'category' => 'pmbok',
            'organization_id' => null,              // Système (disponible pour tous)
            'parent_methodology_id' => null,
            'is_system' => true,
            'is_active' => true,
            'description' => 'Méthodologie standard PMBOK 7th Edition avec les 5 groupes de processus. Approche séquentielle en cascade où chaque phase doit être complétée avant de passer à la suivante.',
        ]);

        // Phase 1: Initiation
        $initiation = PhaseTemplate::create([
            'methodology_template_id' => $pmbok->id,
            'parent_phase_id' => null,
            'name' => 'Initiation',
            'name_fr' => 'Initialisation',
            'description' => 'Phase de démarrage du projet où l\'on définit le projet à un niveau élevé et obtient l\'autorisation de démarrage.',
            'phase_type' => 'initiation',
            'sequence' => 1,
            'level' => 1,
            'typical_duration_days' => null,
            'typical_duration_percent' => 10.00,
            'key_activities' => [
                'Développer la charte du projet',
                'Identifier les parties prenantes clés',
                'Définir les objectifs et le périmètre de haut niveau',
                'Identifier les contraintes et hypothèses',
                'Obtenir l\'autorisation formelle de démarrage',
                'Nommer le chef de projet',
            ],
            'key_deliverables' => [
                'Charte du projet (Project Charter)',
                'Registre des parties prenantes',
                'Document de vision du projet',
                'Business case',
                'Étude de faisabilité',
            ],
            'entry_criteria' => [
                'Besoin d\'affaires identifié',
                'Budget préliminaire alloué',
                'Sponsor identifié',
            ],
            'exit_criteria' => [
                'Charte projet approuvée',
                'Chef de projet nommé',
                'Parties prenantes identifiées',
                'Autorisation formelle obtenue',
            ],
        ]);

        // Phase 2: Planning
        $planning = PhaseTemplate::create([
            'methodology_template_id' => $pmbok->id,
            'parent_phase_id' => null,
            'name' => 'Planning',
            'name_fr' => 'Planification',
            'description' => 'Phase d\'élaboration détaillée du plan de management du projet et de tous les plans subsidiaires.',
            'phase_type' => 'planning',
            'sequence' => 2,
            'level' => 1,
            'typical_duration_days' => null,
            'typical_duration_percent' => 20.00,
            'key_activities' => [
                'Développer le plan de management du projet',
                'Définir et documenter le contenu (scope)',
                'Créer la WBS (Work Breakdown Structure)',
                'Définir et séquencer les activités',
                'Estimer les ressources et durées',
                'Développer le calendrier',
                'Estimer les coûts et établir le budget',
                'Planifier la qualité',
                'Planifier les ressources humaines',
                'Planifier les communications',
                'Identifier et analyser les risques',
                'Planifier les réponses aux risques',
                'Planifier les approvisionnements',
            ],
            'key_deliverables' => [
                'Plan de management du projet',
                'Énoncé du contenu détaillé',
                'WBS et dictionnaire WBS',
                'Planning détaillé (diagramme de Gantt)',
                'Budget détaillé',
                'Plan de management des risques',
                'Registre des risques',
                'Plan de management de la qualité',
                'Plan de management des communications',
                'Plan de management des ressources',
                'Plan d\'approvisionnement',
            ],
            'entry_criteria' => [
                'Charte projet approuvée',
                'Équipe projet constituée',
                'Budget alloué',
            ],
            'exit_criteria' => [
                'Plan de management du projet approuvé',
                'Tous les plans subsidiaires approuvés',
                'Baseline (référence de base) établie',
                'Autorisation de démarrer l\'exécution obtenue',
            ],
        ]);

        // Phase 3: Execution
        $execution = PhaseTemplate::create([
            'methodology_template_id' => $pmbok->id,
            'parent_phase_id' => null,
            'name' => 'Execution',
            'name_fr' => 'Exécution',
            'description' => 'Phase de réalisation du travail défini dans le plan de management du projet pour satisfaire les exigences.',
            'phase_type' => 'execution',
            'sequence' => 3,
            'level' => 1,
            'typical_duration_days' => null,
            'typical_duration_percent' => 50.00,
            'key_activities' => [
                'Diriger et gérer le travail du projet',
                'Gérer les connaissances du projet',
                'Acquérir et développer l\'équipe projet',
                'Diriger l\'équipe projet',
                'Gérer les communications',
                'Mettre en œuvre la réponse aux risques',
                'Conduire les approvisionnements',
                'Gérer l\'engagement des parties prenantes',
                'Assurer la qualité',
            ],
            'key_deliverables' => [
                'Livrables du projet',
                'Données de performance du travail',
                'Demandes de modification',
                'Mises à jour du plan de projet',
                'Rapports d\'avancement',
                'Registre des problèmes',
            ],
            'entry_criteria' => [
                'Plan de management approuvé',
                'Équipe mobilisée',
                'Ressources allouées',
                'Contrats signés (si applicable)',
            ],
            'exit_criteria' => [
                'Livrables produits et validés',
                'Critères d\'acceptation satisfaits',
                'Documentation complète',
            ],
        ]);

        // Phase 4: Monitoring & Controlling
        $monitoring = PhaseTemplate::create([
            'methodology_template_id' => $pmbok->id,
            'parent_phase_id' => null,
            'name' => 'Monitoring & Controlling',
            'name_fr' => 'Surveillance et Maîtrise',
            'description' => 'Phase continue de suivi, révision et régulation de l\'avancement et de la performance du projet.',
            'phase_type' => 'monitoring',
            'sequence' => 4,
            'level' => 1,
            'typical_duration_days' => null,
            'typical_duration_percent' => 15.00,
            'key_activities' => [
                'Surveiller et maîtriser le travail du projet',
                'Effectuer le contrôle intégré des modifications',
                'Valider le contenu',
                'Maîtriser le contenu',
                'Maîtriser le calendrier',
                'Maîtriser les coûts',
                'Maîtriser la qualité',
                'Maîtriser les ressources',
                'Surveiller les communications',
                'Surveiller les risques',
                'Maîtriser les approvisionnements',
                'Surveiller l\'engagement des parties prenantes',
            ],
            'key_deliverables' => [
                'Rapports de performance',
                'Prévisions',
                'Demandes de modification approuvées/rejetées',
                'Mises à jour du plan de projet',
                'Rapports d\'avancement',
                'Mesures de performance (EVM)',
                'Livrables validés',
            ],
            'entry_criteria' => [
                'Travaux d\'exécution démarrés',
                'Système de suivi en place',
            ],
            'exit_criteria' => [
                'Performance du projet maîtrisée',
                'Écarts identifiés et corrigés',
                'Modifications approuvées et implémentées',
            ],
        ]);

        // Phase 5: Closure
        $closure = PhaseTemplate::create([
            'methodology_template_id' => $pmbok->id,
            'parent_phase_id' => null,
            'name' => 'Closure',
            'name_fr' => 'Clôture',
            'description' => 'Phase de finalisation de toutes les activités, obtention de l\'acceptation formelle et clôture administrative du projet.',
            'phase_type' => 'closure',
            'sequence' => 5,
            'level' => 1,
            'typical_duration_days' => null,
            'typical_duration_percent' => 5.00,
            'key_activities' => [
                'Clôturer le projet ou la phase',
                'Obtenir l\'acceptation finale des livrables',
                'Transférer les livrables au client',
                'Archiver tous les documents du projet',
                'Capturer les leçons apprises',
                'Libérer les ressources projet',
                'Clôturer les contrats',
                'Célébrer les succès',
            ],
            'key_deliverables' => [
                'Livrable final accepté',
                'Document de clôture du projet',
                'Rapport final du projet',
                'Leçons apprises documentées',
                'Archives du projet',
                'Libération formelle des ressources',
            ],
            'entry_criteria' => [
                'Tous les livrables produits',
                'Critères d\'acceptation satisfaits',
                'Approbation du sponsor obtenue',
            ],
            'exit_criteria' => [
                'Acceptation formelle signée',
                'Contrats clôturés',
                'Ressources libérées',
                'Documentation archivée',
                'Leçons apprises capturées',
            ],
        ]);

        $this->command->info('   ✅ PMBOK Waterfall: 5 phases created');
    }

    /**
     * Créer la méthodologie Agile Scrum
     */
    private function createAgileScrum(): void
    {
        $this->command->info('📗 Creating Agile Scrum methodology...');

        $scrum = MethodologyTemplate::create([
            'name' => 'Agile Scrum',
            'name_fr' => 'Agile Scrum',
            'slug' => 'agile-scrum',
            'category' => 'agile',
            'organization_id' => null,
            'parent_methodology_id' => null,
            'is_system' => true,
            'is_active' => true,
            'description' => 'Framework Agile Scrum avec itérations courtes (sprints) de 2-4 semaines. Approche itérative et incrémentale favorisant la flexibilité et l\'adaptation.',
        ]);

        // Phase 0: Sprint 0 (Setup)
        PhaseTemplate::create([
            'methodology_template_id' => $scrum->id,
            'parent_phase_id' => null,
            'name' => 'Sprint 0 - Project Setup',
            'name_fr' => 'Sprint 0 - Configuration Projet',
            'description' => 'Phase de préparation initiale avant les sprints de développement.',
            'phase_type' => 'initiation',
            'sequence' => 1,
            'level' => 1,
            'typical_duration_days' => 14,
            'typical_duration_percent' => null,
            'key_activities' => [
                'Constituer l\'équipe Scrum',
                'Créer le Product Backlog initial',
                'Définir la vision du produit',
                'Préparer l\'environnement de développement',
                'Établir la Definition of Done',
                'Former l\'équipe si nécessaire',
            ],
            'key_deliverables' => [
                'Product Backlog initial',
                'Vision du produit',
                'Definition of Done',
                'Environnement technique prêt',
                'Équipe Scrum constituée',
            ],
        ]);

        // Phase 1: Sprints (Template générique)
        PhaseTemplate::create([
            'methodology_template_id' => $scrum->id,
            'parent_phase_id' => null,
            'name' => 'Development Sprints',
            'name_fr' => 'Sprints de Développement',
            'description' => 'Itérations de développement de 2-4 semaines produisant un incrément de produit potentiellement livrable.',
            'phase_type' => 'execution',
            'sequence' => 2,
            'level' => 1,
            'typical_duration_days' => 14,
            'typical_duration_percent' => null,
            'key_activities' => [
                'Sprint Planning',
                'Daily Scrum (standup quotidien)',
                'Développement des user stories',
                'Sprint Review',
                'Sprint Retrospective',
            ],
            'key_deliverables' => [
                'Incrément de produit',
                'Sprint Backlog mis à jour',
                'Documentation technique',
                'Résultats des tests',
            ],
        ]);

        // Phase 2: Release
        PhaseTemplate::create([
            'methodology_template_id' => $scrum->id,
            'parent_phase_id' => null,
            'name' => 'Release & Deployment',
            'name_fr' => 'Mise en Production',
            'description' => 'Phase de déploiement et mise en production du produit.',
            'phase_type' => 'closure',
            'sequence' => 3,
            'level' => 1,
            'typical_duration_days' => 7,
            'typical_duration_percent' => null,
            'key_activities' => [
                'Tests d\'acceptation finaux',
                'Déploiement en production',
                'Formation des utilisateurs',
                'Transfert au support',
            ],
            'key_deliverables' => [
                'Produit déployé',
                'Documentation utilisateur',
                'Support transféré',
            ],
        ]);

        $this->command->info('   ✅ Agile Scrum: 3 phases created');
    }

    /**
     * Créer la méthodologie Hybrid
     */
    private function createHybrid(): void
    {
        $this->command->info('📙 Creating Hybrid PMBOK + Agile methodology...');

        $hybrid = MethodologyTemplate::create([
            'name' => 'Hybrid PMBOK + Agile',
            'name_fr' => 'Hybride PMBOK + Agile',
            'slug' => 'hybrid-pmbok-agile',
            'category' => 'hybrid',
            'organization_id' => null,
            'parent_methodology_id' => null,
            'is_system' => true,
            'is_active' => true,
            'description' => 'Approche hybride combinant la structure PMBOK pour l\'initiation et la planification, avec des pratiques Agile pour l\'exécution itérative.',
        ]);

        // Phase 1: Initiation (PMBOK)
        PhaseTemplate::create([
            'methodology_template_id' => $hybrid->id,
            'parent_phase_id' => null,
            'name' => 'Initiation',
            'name_fr' => 'Initialisation',
            'description' => 'Phase initiale structurée selon PMBOK.',
            'phase_type' => 'initiation',
            'sequence' => 1,
            'level' => 1,
            'typical_duration_percent' => 10.00,
            'key_activities' => [
                'Développer la charte du projet',
                'Identifier les parties prenantes',
                'Définir la vision produit',
            ],
            'key_deliverables' => [
                'Charte du projet',
                'Vision produit',
                'Registre des parties prenantes',
            ],
        ]);

        // Phase 2: Planning (PMBOK)
        PhaseTemplate::create([
            'methodology_template_id' => $hybrid->id,
            'parent_phase_id' => null,
            'name' => 'Planning',
            'name_fr' => 'Planification',
            'description' => 'Planification de haut niveau avec roadmap agile.',
            'phase_type' => 'planning',
            'sequence' => 2,
            'level' => 1,
            'typical_duration_percent' => 15.00,
            'key_activities' => [
                'Créer la roadmap produit',
                'Établir le budget',
                'Planifier les releases',
                'Identifier les risques majeurs',
            ],
            'key_deliverables' => [
                'Roadmap produit',
                'Budget',
                'Plan de releases',
            ],
        ]);

        // Phase 3: Agile Iterations
        PhaseTemplate::create([
            'methodology_template_id' => $hybrid->id,
            'parent_phase_id' => null,
            'name' => 'Agile Iterations',
            'name_fr' => 'Itérations Agile',
            'description' => 'Exécution agile avec sprints itératifs.',
            'phase_type' => 'execution',
            'sequence' => 3,
            'level' => 1,
            'typical_duration_percent' => 60.00,
            'key_activities' => [
                'Sprints itératifs',
                'Reviews fréquentes',
                'Adaptation continue',
            ],
            'key_deliverables' => [
                'Incréments de produit',
                'Feedback continu',
            ],
        ]);

        // Phase 4: Closure (PMBOK)
        PhaseTemplate::create([
            'methodology_template_id' => $hybrid->id,
            'parent_phase_id' => null,
            'name' => 'Closure',
            'name_fr' => 'Clôture',
            'description' => 'Clôture formelle du projet.',
            'phase_type' => 'closure',
            'sequence' => 4,
            'level' => 1,
            'typical_duration_percent' => 5.00,
            'key_activities' => [
                'Acceptation finale',
                'Leçons apprises',
                'Clôture administrative',
            ],
            'key_deliverables' => [
                'Produit final',
                'Documentation',
                'Leçons apprises',
            ],
        ]);

        $this->command->info('   ✅ Hybrid: 4 phases created');
    }
}
