# Keep Calm API — Backend

Lumen backend: **Cron → Gemini API → JSON → parse & store in DB → cache → frontend**.

## Requirements

- PHP 8.2+
- Composer ([getcomposer.org](https://getcomposer.org/download/))
- SQLite (or MySQL)

## Setup

**1. Install dependencies** (required — creates `vendor/`):

```bash
cd server
composer install
```

If `composer` is not in your PATH, use the full path or download [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe) (Windows) and run it, then open a new terminal and run `composer install` from the `server` folder.

**2. Environment:**

```powershell
copy .env.example .env
# Edit .env if needed (DB, optional GEMINI_API_KEY)
```

**3. Continue (DB + ingest + run):**

```powershell
.\scripts\continue.ps1
```

This creates `database/database.sqlite` if missing, runs migrations, runs ingest (loads sample JSON), then tells you how to start the server. Or run manually:

```powershell
php artisan migrate --force
php artisan dashboard:ingest
php -S localhost:8000 -t public public/router.php
```

(The router sends non-file requests like `/api/dashboard` to the app; without it the built-in server would 404 on API routes.)

### Database (SQLite)

1. Create the DB file: `touch database/database.sqlite`
2. Set in `.env`: `DB_CONNECTION=sqlite`, `DB_DATABASE` can stay empty (uses `database/database.sqlite` by default)
3. Run migrations: `php artisan migrate`  
   Or for SQLite quick init: `php artisan db:init`

### Data source

- **With Gemini**: Set `GEMINI_API_KEY` in `.env`. Ingest will call the API.
- **Without Gemini** (dev/test): Leave `GEMINI_API_KEY` empty. Ingest uses `storage/app/sample_dashboard.json`. Set `DASHBOARD_FILE` if using a different path.
- **Windows TLS note**: If ingest fails with `cURL error 60` (certificate problem), download a CA bundle (e.g. `cacert.pem`) and set `GEMINI_CA_BUNDLE` to its absolute path in `.env`.

## Run

### API server

```bash
php -S localhost:8000 -t public public/router.php
```

- `GET /` — frontpage (HTML)
- `GET /api/dashboard` — dashboard JSON (cache → DB fallback)

### Ingest ( Cron → Gemini → DB → cache )

```bash
php artisan dashboard:ingest
```

### Cron

Add to crontab to run ingest daily:

```
0 6 * * * cd /path/to/KeepKalmIndex/server && php artisan dashboard:ingest
```

## SOLID layout

| Layer | Interface | Implementation |
|-------|-----------|----------------|
| Data source | `DashboardDataProviderInterface` | `GeminiDataProvider` / `FileDataProvider` |
| Parse | `DashboardParserInterface` | `DashboardParser` |
| Persist | `DashboardRepositoryInterface` | `DashboardRepository` |
| Cache | `DashboardCacheInterface` | `DashboardCache` |
| Orchestrate | `IngestServiceInterface` | `IngestService` |

All secrets via `.env`; never in code.
