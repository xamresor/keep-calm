# Massive.com API Integration - Quick Start Guide

## 🚀 Quick Setup (5 minutes)

### Step 1: Get Your API Key
1. Go to [https://massive.com](https://massive.com)
2. Sign up for a free account
3. Navigate to your dashboard and copy your API key

### Step 2: Configure
Add your API key to `.env`:
```bash
MASSIVE_API_KEY=your_api_key_here
```

### Step 3: Run Migrations
```bash
cd /home/roman/PhpstormProjects/KeepCalm/server
php artisan migrate
```

### Step 4: Test the Integration
```bash
# Ingest all market data
php artisan massive:ingest --all

# Or ingest specific data types
php artisan massive:ingest --indices
php artisan massive:ingest --stocks
php artisan massive:ingest --economy
php artisan massive:ingest --news
```

### Step 5: Access the Data via API
```bash
# Get market overview
curl http://localhost:8000/api/market/overview

# Get market indices
curl http://localhost:8000/api/market/indices

# Get stocks
curl http://localhost:8000/api/market/stocks

# Get economic indicators
curl http://localhost:8000/api/market/economy

# Get news
curl http://localhost:8000/api/market/news
```

## 📅 Enable Hourly Auto-Updates

Add to your crontab (run `crontab -e`):
```bash
* * * * * cd /home/roman/PhpstormProjects/KeepCalm/server && php artisan schedule:run >> /dev/null 2>&1
```

This will automatically fetch fresh market data every hour!

## 📊 Available Endpoints

| Endpoint | Description | Example |
|----------|-------------|---------|
| `GET /api/market/overview` | Complete market snapshot | All data in one call |
| `GET /api/market/indices` | Market indices (S&P, Dow, Nasdaq) | `?date=2025-01-15` |
| `GET /api/market/stocks` | Top gainers/losers | `?limit=50&date=2025-01-15` |
| `GET /api/market/economy` | Economic indicators | `?type=inflation&limit=50` |
| `GET /api/market/news` | Market news | `?ticker=AAPL&limit=20` |

## 🎯 What Data You Get

### Market Indices
- S&P 500 (I:SPX)
- Dow Jones (I:DJI)
- Nasdaq 100 (I:NDX)
- Russell 2000 (I:RUT)
- VIX (I:VIX)

### Stocks
- Top 20 gainers
- Top 20 losers
- Real-time prices, volume, OHLC data

### Economic Indicators
- Inflation (CPI, PCE)
- Labor market (unemployment, job openings)
- Treasury yields (2Y, 10Y, 30Y)

### News
- Real-time financial news
- Filtered by ticker
- Publisher, author, timestamps

## 🔧 Troubleshooting

**No data showing?**
```bash
# Run the ingest command manually
php artisan massive:ingest --all

# Check for errors
tail -f storage/logs/lumen.log
```

**API key not working?**
- Make sure you copied the entire key
- Check that `.env` file has `MASSIVE_API_KEY=your_key`
- Restart your server after updating `.env`

**Scheduler not running?**
```bash
# Test manually
php artisan schedule:run

# Check if cron is configured
crontab -l
```

## 📚 Full Documentation

See [MASSIVE_INTEGRATION.md](server/MASSIVE_INTEGRATION.md) for complete documentation including:
- Architecture details
- Database schema
- Best practices
- Advanced usage

## 💡 Example Usage

### Get Today's Market Overview
```bash
curl http://localhost:8000/api/market/overview | jq
```

### Get Specific Index
```bash
curl "http://localhost:8000/api/market/indices?date=$(date +%Y-%m-%d)" | jq '.data[] | select(.ticker == "I:SPX")'
```

### Get News for Apple
```bash
curl "http://localhost:8000/api/market/news?ticker=AAPL&limit=10" | jq
```

### Get Latest Inflation Data
```bash
curl "http://localhost:8000/api/market/economy?type=inflation&limit=5" | jq
```

## 🎉 You're All Set!

Your application now has access to:
- ✅ Real-time market data
- ✅ Historical indices and stocks
- ✅ Economic indicators
- ✅ Financial news
- ✅ Automatic hourly updates

Happy coding! 🚀
