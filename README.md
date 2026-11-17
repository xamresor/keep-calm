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

**Prices are real, not generated.** Gold, Brent and VIX are daily closes from the Yahoo Finance chart
endpoint; the Global Economic Policy Uncertainty index is the monthly `GEPUCURRENT` series from FRED.
Neither needs an API key. They are stored by day in `price_points`. The daily ingest loads them first,
gives them to Gemini as facts and stores them in the snapshot, so the model never supplies a price.
The charts on the page read their history from the database: 180 days of prices, 90 days of country
scores as a 7-day average (the daily scores jump by ±10).

**News are real, not generated.** The model has no web access. The ingest loads headlines of the last
36 hours from public RSS feeds (BBC, Al Jazeera, DW, France 24, The Guardian, LSM), stores them with
links in `news_items` and gives them to the model as the only source of current events. The model
also gets last week's average country scores as a starting point and must move the countries that the
headlines touch. Without that starting point the scores were noise; with it and no headlines they froze.

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

Price history: `php artisan prices:ingest --range=1y` loads a year of daily prices. Add
`--rewrite-snapshots` once on an old database to replace the prices stored in past snapshots with the
real ones. Planned work is in `ROADMAP.md`.

## License

[GPL-3.0-or-later](LICENSE) — free software: use, study, share and modify it. Copyright (C) 2026 Romans Sereda. Derivative works must also be licensed under the GPL (copyleft).
