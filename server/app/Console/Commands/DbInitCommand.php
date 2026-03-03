<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbInitCommand extends Command
{
    protected $signature = 'db:init';

    protected $description = 'Initialize database schema (SQLite)';

    public function handle(): int
    {
        $driver = config('database.default');

        if ($driver !== 'sqlite') {
            $this->warn('db:init is for SQLite. For MySQL, run: php artisan migrate');
            return self::FAILURE;
        }

        $schemaPath = base_path('database/schema.sql');
        if (!is_readable($schemaPath)) {
            $this->error("Schema file not found: {$schemaPath}");
            return self::FAILURE;
        }

        $sql = file_get_contents($schemaPath);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (str_starts_with($statement, '--') || $statement === '') {
                continue;
            }
            DB::unprepared($statement);
        }

        $this->info('Database initialized.');
        return self::SUCCESS;
    }
}
