#!/bin/bash
# Run after composer install. Sets up DB, runs ingest, then starts API server.
set -e

# Navigate to server root
cd "$(dirname "$0")/.."

if [ ! -f "vendor/autoload.php" ]; then
    echo -e "\033[0;31mRun 'composer install' first.\033[0m"
    exit 1
fi

DB_PATH="database/database.sqlite"
if [ ! -f "$DB_PATH" ]; then
    mkdir -p database
    touch "$DB_PATH"
    echo "Created $DB_PATH"
fi

echo -e "\033[0;36mRunning migrations...\033[0m"
php artisan migrate --force

echo -e "\033[0;36mRunning dashboard ingest...\033[0m"
php artisan dashboard:ingest

echo ""
echo -e "\033[0;32mReady. Start API server with:\033[0m"
echo "  php -S localhost:8000 -t public public/router.php"
echo ""
echo -e "\033[0;90mThen open: http://localhost:8000\033[0m"
