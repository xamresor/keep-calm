# Massive.com API Integration

This document describes the integration of Massive.com stock market data API into the KeepCalm application.

## Overview

The integration provides real-time and historical market data including:
- **Market Indices** (S&P 500, Dow Jones, Nasdaq, etc.)
- **Stock Data** (Top gainers/losers, market movers)
- **Economic Indicators** (Inflation, labor market, treasury yields)
- **Market News** (Real-time financial news articles)

## Setup

### 1. Get Your API Key

1. Sign up at [https://massive.com](https://massive.com)
2. Navigate to your dashboard
3. Copy your API key

### 2. Configure Environment

Add your Massive.com API key to `.env`:

```bash
MASSIVE_API_KEY=your_api_key_here
MASSIVE_BASE_URI=https://api.massive.com
```

### 3. Run Migrations

Create the database tables:

```bash
php artisan migrate
```

Or if using Lumen:

```bash
php artisan migrate --path=database/migrations
```

## Usage

### Manual Data Ingestion

Ingest all data types:
```bash
php artisan massive:ingest --all
```

Ingest specific data types:
```bash
# Market indices only
php artisan massive:ingest --indices

# Stocks only
php artisan massive:ingest --stocks

# Economic indicators only
php artisan massive:ingest --economy

# News only
php artisan massive:ingest --news
```

### Automated Scheduling

The integration is configured to run **every hour** automatically via Laravel's scheduler.

To enable the scheduler, add this to your crontab:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Or for this project:

```bash
* * * * * cd /home/roman/PhpstormProjects/KeepCalm/server && php artisan schedule:run >> /dev/null 2>&1
```

## API Endpoints

### Market Overview
Get a comprehensive market overview including indices, top movers, and news.

```
GET /api/market/overview
```

**Response:**
```json
{
  "success": true,
  "date": "2025-01-15",
  "indices": [...],
  "top_gainers": [...],
  "top_losers": [...],
  "latest_news": [...]
}
```

### Market Indices
Get market indices data (S&P 500, Dow Jones, Nasdaq, etc.)

```
GET /api/market/indices?date=2025-01-15
```

**Query Parameters:**
- `date` (optional): Date in Y-m-d format, defaults to today

**Response:**
```json
{
  "success": true,
  "date": "2025-01-15",
  "count": 5,
  "data": [
    {
      "ticker": "I:SPX",
      "name": "S&P 500",
      "value": 4783.45,
      "change": 23.12,
      "change_percent": 0.49,
      "open": 4760.33,
      "high": 4790.12,
      "low": 4755.67,
      "close": 4783.45
    }
  ]
}
```

### Stocks
Get stock market data (top gainers/losers)

```
GET /api/market/stocks?date=2025-01-15&limit=50
```

**Query Parameters:**
- `date` (optional): Date in Y-m-d format, defaults to today
- `limit` (optional): Number of results (max 100), defaults to 50

**Response:**
```json
{
  "success": true,
  "date": "2025-01-15",
  "count": 40,
  "data": [
    {
      "ticker": "AAPL",
      "price": 185.50,
      "change": 5.25,
      "change_percent": 2.91,
      "volume": 52341234
    }
  ]
}
```

### Economic Indicators
Get economic indicators (inflation, labor market, treasury yields)

```
GET /api/market/economy?type=inflation&limit=50
```

**Query Parameters:**
- `type` (optional): Filter by indicator type (`inflation`, `labor_market`, `treasury_yields`)
- `limit` (optional): Number of results (max 100), defaults to 50

**Response:**
```json
{
  "success": true,
  "type": "inflation",
  "count": 10,
  "data": [
    {
      "indicator_type": "inflation",
      "indicator_name": "CPI",
      "value": 3.2,
      "unit": "percent",
      "date": "2025-01-01"
    }
  ]
}
```

### Market News
Get real-time market news

```
GET /api/market/news?ticker=AAPL&limit=20
```

**Query Parameters:**
- `ticker` (optional): Filter news by ticker symbol
- `limit` (optional): Number of results (max 100), defaults to 20

**Response:**
```json
{
  "success": true,
  "count": 20,
  "data": [
    {
      "article_id": "abc123",
      "publisher": "Bloomberg",
      "title": "Market Update...",
      "author": "John Doe",
      "published_utc": "2025-01-15 14:30:00",
      "article_url": "https://...",
      "tickers": ["AAPL", "MSFT"]
    }
  ]
}
```

## Architecture

### Components

1. **MassiveApiClient** (`app/Services/Massive/MassiveApiClient.php`)
   - HTTP client wrapper for Massive.com API
   - Handles authentication, requests, and error handling
   - Provides methods for all major API endpoints

2. **IngestMassiveDataCommand** (`app/Console/Commands/IngestMassiveDataCommand.php`)
   - Console command for data ingestion
   - Supports selective ingestion (--indices, --stocks, --economy, --news)
   - Scheduled to run hourly

3. **Models**
   - `MarketIndex`: Store market indices data
   - `MarketStock`: Store stock market data
   - `EconomicIndicator`: Store economic indicators
   - `MarketNews`: Store market news articles

4. **MassiveDataController** (`app/Http/Controllers/MassiveDataController.php`)
   - REST API endpoints to expose market data
   - Provides filtering and pagination

### Data Flow

```
Massive.com API
      ↓
MassiveApiClient (HTTP requests)
      ↓
IngestMassiveDataCommand (scheduled hourly)
      ↓
Database (SQLite/MySQL)
      ↓
MassiveDataController (API endpoints)
      ↓
Frontend Application
```

## Database Schema

### market_indices
- `ticker`: Index ticker (e.g., I:SPX)
- `name`: Index name
- `value`: Current value
- `change`, `change_percent`: Daily change
- `open`, `high`, `low`, `close`: OHLC data
- `snapshot_date`: Date of snapshot

### market_stocks
- `ticker`: Stock ticker
- `price`: Current price
- `change`, `change_percent`: Daily change
- `open`, `high`, `low`, `close`: OHLC data
- `volume`: Trading volume
- `snapshot_date`: Date of snapshot

### economic_indicators
- `indicator_type`: Type (inflation, labor_market, treasury_yields)
- `indicator_name`: Specific indicator name
- `value`: Indicator value
- `date`: Date of measurement

### market_news
- `article_id`: Unique article identifier
- `title`, `description`: Article content
- `publisher`, `author`: Source information
- `published_utc`: Publication timestamp
- `tickers`: Related stock tickers (JSON)

## Best Practices

### API Rate Limits
- Massive.com has rate limits based on your subscription tier
- The hourly schedule helps stay within limits
- Consider caching responses for frequently accessed data

### Error Handling
- All API calls are wrapped in try-catch blocks
- Failed ingestions are logged but don't stop the entire process
- Check logs for any API errors

### Data Retention
- Consider implementing data cleanup for old records
- Archive historical data if needed
- Monitor database size growth

### Performance
- Indices are created on frequently queried columns
- Use `snapshot_date` for efficient date-based queries
- Consider adding Redis caching for hot data

## Troubleshooting

### "MASSIVE_API_KEY is not configured"
- Ensure you've added `MASSIVE_API_KEY` to your `.env` file
- Restart your application after updating `.env`

### No data returned
- Check if the command has been run: `php artisan massive:ingest --all`
- Verify your API key is valid
- Check application logs for errors

### Scheduler not running
- Ensure cron job is configured correctly
- Test manually: `php artisan schedule:run`
- Check cron logs: `grep CRON /var/log/syslog`

## Resources

- [Massive.com Documentation](https://massive.com/docs)
- [Massive.com API Reference](https://massive.com/docs/rest/quickstart)
- [Massive.com Pricing](https://massive.com/pricing)
- [GitHub - Massive Client Libraries](https://github.com/massive-com)

## License

This integration uses the Massive.com API which requires a valid API key and subscription. Please review Massive.com's terms of service and pricing before use.
