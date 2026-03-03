# Run after composer install. Sets up DB, runs ingest, then starts API server.
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (-not (Test-Path "vendor/autoload.php")) {
    Write-Host "Run 'composer install' first." -ForegroundColor Red
    exit 1
}

$dbPath = "database\database.sqlite"
if (-not (Test-Path $dbPath)) {
    New-Item -ItemType File -Path $dbPath -Force | Out-Null
    Write-Host "Created $dbPath"
}

Write-Host "Running migrations..." -ForegroundColor Cyan
php artisan migrate --force

Write-Host "Running dashboard ingest..." -ForegroundColor Cyan
php artisan dashboard:ingest

Write-Host ""
Write-Host "Ready. Start API server with:" -ForegroundColor Green
Write-Host "  php -S localhost:8000 -t public public/router.php"
Write-Host ""
Write-Host "Then open: http://localhost:8000" -ForegroundColor Gray
