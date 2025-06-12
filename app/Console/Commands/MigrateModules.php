<?php

namespace App\Console\Commands;

use Database\Seeders\InitialUserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:orderly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute migrations in a specific order: Principal first, then others.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Executing Principal Module Migration");
        $this->call('migrate', ['--path' => 'database/migrations/Principal']);

        $this->info("Executing remaining Module Migrations");

        // Get all files inside database/migrations
        $folders = File::directories(database_path('migrations'));

        foreach ($folders as $folder) {
            if (str_contains($folder, 'Principal')) {
                continue;
            }
            $relativePath = "database/migrations/" . basename($folder);

            $this->info("Executing migrations in folder: $relativePath");
            $this->call('migrate', [
                '--path' => $relativePath,
            ]);
        }

        $this->info("All migrations executed successfully.");

        $this->call(InitialUserSeeder::class);
        $this->info("Initial user seeding completed successfully.");
    }
}
