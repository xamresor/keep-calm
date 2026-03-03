<?php

namespace App\Console\Commands;

use App\Contracts\IngestServiceInterface;
use Illuminate\Console\Command;

class IngestDashboardCommand extends Command
{
    protected $signature = 'dashboard:ingest
                            {when?* : Relative date expression, e.g. "-1 day" or "-10 days"}
                            {--when= : Relative date expression option (recommended for negative offsets)}';

    protected $description = 'Fetch dashboard from Gemini (or file), parse, store in DB, refresh cache';

    public function handle(IngestServiceInterface $ingest): int
    {
        $whenInput = $this->argument('when');
        $whenOption = $this->option('when');
        $when = is_string($whenOption) && trim($whenOption) !== ''
            ? $whenOption
            : (is_array($whenInput) && count($whenInput) > 0
            ? implode(' ', $whenInput)
            : 'now');

        $this->info('Starting dashboard ingest...');
        $this->line("Requested date expression: {$when}");

        $result = $ingest->run($when);
        if ($result['success']) {
            $this->info("Ingest completed. last_updated: {$result['last_updated']}");
            return self::SUCCESS;
        }

        $this->error($result['message']);
        return self::FAILURE;
    }
}
