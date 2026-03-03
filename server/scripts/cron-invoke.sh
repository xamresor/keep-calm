#!/bin/sh
# Cron: 0 6 * * * /path/to/KeepKalmIndex/server/scripts/cron-invoke.sh
cd "$(dirname "$0")/.."
php artisan dashboard:ingest
