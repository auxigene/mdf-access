<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-key:generate {name? : The name/description for this API key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API key for accessing the Excel API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        if (!$name) {
            $name = $this->ask('Enter a name/description for this API key');
        }

        if (empty($name)) {
            $this->error('API key name is required');
            return Command::FAILURE;
        }

        $key = ApiKey::create([
            'key' => ApiKey::generateKey(),
            'name' => $name,
            'is_active' => true,
        ]);

        $this->newLine();
        $this->info('API Key generated successfully!');
        $this->newLine();
        $this->line('<fg=green>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->line("  <fg=bright-white>Name:</> <fg=yellow>{$key->name}</>");
        $this->line("  <fg=bright-white>Key:</> <fg=cyan>{$key->key}</>");
        $this->line("  <fg=bright-white>Created:</> {$key->created_at->format('Y-m-d H:i:s')}");
        $this->line('<fg=green>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->newLine();
        $this->warn('Keep this API key secure! It cannot be retrieved later.');
        $this->newLine();

        return Command::SUCCESS;
    }
}
