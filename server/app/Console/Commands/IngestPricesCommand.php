<?php

namespace App\Console\Commands;

use App\Contracts\DashboardCacheInterface;
use App\Models\Snapshot;
use App\Services\Prices\PriceFeed;
use Illuminate\Console\Command;

class IngestPricesCommand extends Command
{
    protected $signature = 'prices:ingest
                            {--range=1mo : How far back to load daily prices (1mo, 6mo, 1y, 2y)}
                            {--rewrite-snapshots : Replace the prices stored in every past snapshot with the real ones}';

    protected $description = 'Load real gold, Brent, VIX and policy-uncertainty values and store them by day';

    public function handle(PriceFeed $feed, DashboardCacheInterface $cache): int
    {
        $failed = false;
        foreach ($feed->refresh((string) $this->option('range')) as $series => $result) {
            if (is_int($result)) {
                $this->info("{$series}: {$result} points");
            } else {
                $failed = true;
                $this->error("{$series}: {$result}");
            }
        }

        if ($this->option('rewrite-snapshots')) {
            $changed = 0;
            foreach (Snapshot::query()->orderBy('last_updated')->get() as $snapshot) {
                $real = $feed->valuesOn($snapshot->last_updated->format('Y-m-d'));
                $merged = array_merge($snapshot->key_indicators ?? [], $real);
                if ($merged != ($snapshot->key_indicators ?? [])) {
                    $snapshot->key_indicators = $merged;
                    $snapshot->save();
                    $changed++;
                }
            }
            $this->info("Snapshots rewritten: {$changed}");
        }

        $cache->forget();

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
