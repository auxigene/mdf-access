<?php

namespace App\Imports;

use App\Models\ResourceAllocation;
use App\Models\Resource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ResourceAllocationsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        // Trouver la ressource via l'email de l'utilisateur
        $user = User::where('email', $row['resource_user_email'])->first();
        if (!$user) {
            throw new \Exception("Utilisateur non trouvé: {$row['resource_user_email']}");
        }

        $resource = Resource::where('user_id', $user->id)->first();
        if (!$resource) {
            throw new \Exception("Ressource non trouvée pour l'utilisateur: {$row['resource_user_email']}");
        }

        $project = Project::where('code', $row['project_code'])->first();
        if (!$project) {
            throw new \Exception("Projet non trouvé: {$row['project_code']}");
        }

        $taskId = null;
        if (!empty($row['task_name'])) {
            $task = Task::where('project_id', $project->id)
                        ->where('name', $row['task_name'])
                        ->first();
            $taskId = $task?->id;
        }

        return new ResourceAllocation([
            'resource_id' => $resource->id,
            'project_id' => $project->id,
            'task_id' => $taskId,
            'allocation_percentage' => $row['allocation_percentage'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'hours_allocated' => 0,
            'hours_worked' => 0,
        ]);
    }
}
