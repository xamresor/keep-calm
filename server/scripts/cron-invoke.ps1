# Cron substitute for Windows / manual run
# Usage: .\scripts\cron-invoke.ps1
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root
php artisan dashboard:ingest
