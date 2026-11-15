<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

#[Signature('db:initialize
                {database : Target database name}')]
#[Description('Create database if it does not exist')]
class InitializeDatabase extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $database = $this->argument('database');

        // Validate database name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            $this->error('Invalid database name.');
            return self::FAILURE;
        }

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('.env file not found.');
            return self::FAILURE;
        }

        // Store original configuration
        $originalDatabase = config('database.connections.mysql.database');

        try {
            // Connect to MySQL without selecting a database
            Config::set('database.connections.mysql.database', null);

            DB::purge('mysql');

            DB::connection('mysql')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$database}`
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci"
            );

            $this->info("Database '{$database}' is ready.");

            // Update DB_DATABASE in .env
            $envContent = file_get_contents($envPath);

            $pattern = '/^\s*#?\s*DB_DATABASE\s*=.*$/m';

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace(
                    $pattern,
                    "DB_DATABASE={$database}",
                    $envContent
                );
            } else {
                $envContent .= "\nDB_DATABASE={$database}\n";
            }

            if (file_put_contents($envPath, $envContent, LOCK_EX) === false) {
                throw new \RuntimeException('Failed to update .env file.');
            }

            $this->info(".env updated: DB_DATABASE={$database}");

            // Clear configuration cache
            $this->call('config:clear');

            $this->info('Database initialization completed successfully.');

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;

        } finally {
            DB::purge('mysql');

            Config::set(
                'database.connections.mysql.database',
                $originalDatabase
            );
        }
    }
}
