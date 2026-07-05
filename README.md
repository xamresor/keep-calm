# Keep Calm

A calm, at-a-glance dashboard for turbulent times — a "global chaos" gauge plus per-country
liquidity / logistics / legitimacy readings, scenarios and family-safety notes. Designed to
inform without inducing panic.

**Live:** https://keep-calm.sereda.lv

## How it works

`Cron → Gemini → JSON → parse & store (SQLite) → cache → frontend`

A scheduled ingest asks **Gemini** for a structured snapshot (global chaos, ~23 countries,
scenarios), parses and stores it, and the frontend renders it (Chart.js). An optional
[Massive.com](https://massive.com) integration adds live market indices/stocks/economy/news.

## Structure

- `server/` — **Lumen** backend + the static dashboard (`public/index.html`, Chart.js and fonts
  self-hosted under `public/vendor/`, no external CDN). See `server/README.md` for setup.
- `server/README_MASSIVE.md`, `server/MASSIVE_INTEGRATION.md` — the market-data integration.

## Quick start

```bash
cd server
composer install
cp .env.example .env          # set GEMINI_API_KEY (and MASSIVE_API_KEY for market data)
php artisan migrate
php artisan dashboard:ingest   # generate the first snapshot
php -S 0.0.0.0:8000 -t public
```

Schedule `php artisan dashboard:ingest` (daily) to keep the board fresh.

## License

Personal project by Romans Sereda.
## License

[PolyForm Noncommercial License 1.0.0](LICENSE.md) — free for any **noncommercial** use (personal, research, education, hobbies); **commercial use is not permitted**.

Required Notice: Copyright 2026 Romans Sereda (https://romans.sereda.lv)
