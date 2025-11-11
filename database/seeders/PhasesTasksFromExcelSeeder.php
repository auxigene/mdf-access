<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\PhasesImport;
use App\Imports\TasksImport;
use Maatwebsite\Excel\Facades\Excel;

class PhasesTasksFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        // Import Phases
        $phasesFile = storage_path('app/excel/data/06_phases.xlsx');
        if (file_exists($phasesFile)) {
            $this->command->info("📥 Import des phases...");
            Excel::import(new PhasesImport, $phasesFile);
            $this->command->info("✅ Phases: " . \App\Models\Phase::count());
        }

        // Import Tasks
        $tasksFile = storage_path('app/excel/data/07_tasks.xlsx');
        if (file_exists($tasksFile)) {
            $this->command->info("📥 Import des tâches...");
            Excel::import(new TasksImport, $tasksFile);
            $this->command->info("✅ Tâches: " . \App\Models\Task::count());
        }
    }
}
