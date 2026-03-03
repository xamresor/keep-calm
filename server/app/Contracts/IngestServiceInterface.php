<?php

namespace App\Contracts;

interface IngestServiceInterface
{
    /**
     * Run the full ingest: fetch → parse → store → refresh cache.
     *
     * @return array{success: bool, last_updated: ?string, message: string}
     */
    public function run(string $when = 'now'): array;
}
