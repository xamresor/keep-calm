# Roadmap

Ideas that are not built yet. One entry per idea: what it is, why, and what it needs.

## Event timeline with history playback (idea, 2026-11-18)

Go back in time on the dashboard and see what happened on a given day next to the charts.

- **Time travel.** A date slider or a calendar. The page shows the snapshot of that day: chaos range,
  countries, scenarios, news titles. The data is already stored: one snapshot per day since 2026-02-12.
- **Events on the time line.** Notable events as markers on the price charts (gold, Brent, VIX): a
  strike, a central bank decision, a blockade. Click a marker and see what it was.
- **Media per event.** Notable pictures and videos from the internet tied to the event and its date.
  Links and previews, not copies of the files.
- **Correlation view.** One screen where a price chart and the events sit on the same time axis, so a
  jump in the price can be compared with what happened that day.
- **Style.** Romans named a reference style that speech recognition wrote down as "PMM". The meaning
  is not confirmed. Ask before designing.

What it needs:

- An `events` table: date, title per language, countries, kind, source link, media links.
- A source of events. The stored daily news titles are a start, but the model wrote them without web
  search, so they must be checked against real news before they become markers.
- An API that returns a snapshot for any date (`/api/dashboard?date=YYYY-MM-DD`), not only the latest.
- Chart annotations on the page (Chart.js annotation plugin, self-hosted like the rest).
