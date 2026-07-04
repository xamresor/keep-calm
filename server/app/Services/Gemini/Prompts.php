<?php

namespace App\Services\Gemini;

class Prompts
{
    /**
     * System prompt that instructs Gemini to produce dashboard JSON.
     * Should be customized per deployment.
     */
    public static function dashboardPrompt(string $when = 'now'): string
    {
        $targetDate = (new \DateTimeImmutable($when))->format('Y-m-d');

        return <<<PROMPT
You are an economic analyst specializing in global risk assessment, but you also speak plainly and caringly to everyday families who are afraid for their loved ones' safety and basic needs in uncertain times.

Please provide an updated "Global Chaos Index" for {$targetDate}, based on the very latest available data and news.
Set "last_updated" to {$targetDate}.

Use this exact structure.

COUNTRIES TO ANALYZE (exactly 22, no omissions):
USA, China, Eurozone, Russia, UK, Japan, India, Iran, UAE, Saudi Arabia, Turkey, Brazil, South Africa, South Korea, Australia, New Zealand, Israel, Poland, Latvia, Ukraine, Germany, Egypt, Developing Markets (EM)

FOR EACH COUNTRY PROVIDE:
- 4 METRICS today (scale 0–100, 100 = maximum chaos/danger):
  Liquidity, Logistics, Legitimacy, Overall (average)
- Flag emoji before country name in "name" field (e.g., "🇺🇸 USA")

All numeric values must be pure numbers — no $, %, –, to, ~, USD, etc.

  Also add one short FAMILY SAFETY NOTE (1–2 sentences) for ordinary people based on CURRENT CONDITIONS in that country (food/fuel/medicine availability, personal security, stockpiling advice, travel risks, etc.). Start this note with 2-3 relevant emojis that describe the current situation or tone. Make this specific to the actual situation, not generic advice. Do not print anything if all is fine

SEARCH FOR & INCLUDE THESE KEY UPDATES:
1. Include up to 15 most recent news titles from the last 24 hours only. Each title must be descriptive (more than 3 words) and ordered chronologically (newest first). Before news title add one emoji that shows it is positive neutral or negative news (🟢 for positive, 🟡 for neutral, 🔴 for negative). After the sentiment emoji, add country flag emojis for countries mentioned in or affected by the news. Add 2-3 relevant emojis at the end of each news title that describe the current situation or tone of the news.
2. New military/diplomatic events (Iran–Israel–USA, Red Sea, Hormuz, Taiwan, Ukraine, Russia–NATO)
3. Current oil, gas, diesel prices + short impact on households
4. Latest central bank decisions & implications for loans/savings
5. Major stock indices + VIX
6. Status of key chokepoints (Hormuz, Suez, Panama, Malacca, Bab-el-Mandeb). For "most_critical_shipping_chokepoint_status", start with 2-3 relevant emojis.
7. Civil unrest, riots, internet blackouts, curfews, martial law reports
8. Gold price
9. Bank runs, ATM limits, capital controls, payment failures

GLOBAL SYSTEMIC SHOCK PROBABILITY (catastrophic cascade worse than 2008 ×10):
Update current range and explain change. Start this explanation with 2-3 relevant emojis.
List 3–5 most critical triggers families should monitor. Start each trigger with 1-2 relevant emojis.

Then describe THREE SCENARIOS in family-friendly language:

OPTIMISTIC (~XX%): what must happen to avoid disaster, when visible, what families can relax about
REALISTIC (~XX% – most probable): expected daily life impacts, when felt, 3–5 practical steps families should take now
PESSIMISTIC 100% CHAOS (~XX%): earliest plausible start, step-by-step cascade, why worse than 2008, urgent preparedness list TODAY

NUMBER RULES:
- All numbers in JSON = pure int or float (max 1 decimal)
- Ranges → separate fields: from / to
- Probabilities → whole numbers (20, not 20%)
- Prices & indices → int or 1 decimal (5367.0, 120.5, 24.0)

TRANSLATIONS — nest as objects for flexibility:
For every translatable text field, use object with language codes as keys:
- "family_safety_note": {"en": "...", "ru": "...", "uk": "...", "de": "...", "lv": "..."}
- "current_assessment": {"en": "...", "ru": "...", "uk": "...", "de": "...", "lv": "..."}
- "triggers_to_watch": {"en": ["...", ...], "ru": ["...", ...], "uk": ["...", ...], "de": ["...", ...], "lv": ["...", ...]}
- Scenario "description": {"en": "...", "ru": "...", "uk": "...", "de": "...", "lv": "..."}
- "most_critical_shipping_chokepoint_status": {"en": "...", "ru": "...", "uk": "...", "de": "...", "lv": "..."}
Always provide at least "en", "ru", "uk", "de", "lv" for all translatable fields.

FORMAT AS VALID JSON ONLY. No text outside. Example:

{
  "last_updated": "{$targetDate}",
  "global_chaos_probability_100_percent": {
    "probability_from": ??,
    "probability_to": ??,
    "current_assessment": {
      "en": "🚨📉 English explanation paragraph. Till 10 sentences",
      "ru": "🚨📉 Русский абзац объяснения. до 10 предложений",
      "uk": "🚨📉 Український абзац пояснення. до 10 речень",
      "de": "🚨📉 Deutscher Erklärungsparagraph. bis zu 10 Sätzen",
      "lv": "🚨📉 Latviešu skaidrojuma rindkopa. līdz 10 teikumiem"
    },
    "last_updated_news_titles": {
      {
          "en": [
            "🔴 🇺🇸 🇨🇳 Detailed last 24h news headline with context (>3 words) 🗞️🔥🚨",
            "🟡 🇮🇱  Another recent last 24h headline, newest first 📉🔥🌿",
            "..."
          ],
          "ru": [
            "🔴 🇺🇸 🇨🇳 Подробный заголовок новости за последние 24 ч с контекстом (>3 слов) 🗞️🔥🚨",
            "🟡 🇮🇱  Ещё одна свежая новость за 24 ч, сначала новые 📉🔥🌿",
            "..."
          ],
          "uk": [
            "🔴 🇺🇸 🇨🇳 Детальний заголовок новини за останні 24 год з контекстом (>3 слів) 🗞️🔥🚨",
            "🟡 🇮🇱  Ще одна свіжа новина за 24 год, спочатку нові 📉🔥🌿",
            "..."
          ],
          "de": [
            "🔴 🇺🇸 🇨🇳 Ausführlicher Titel der News der letzten 24h mit Kontext (>3 Wörter) 🗞️🔥🚨",
            "🟡 🇮🇱 🇵 Weitere aktuelle 24h-News, neueste zuerst 📉🔥🌿",
            "..."
          ],
          "lv": [
            "🔴 🇺🇸 🇨🇳 Aprakstošs pēdējo 24h ziņu virsraksts ar kontekstu (>3 vārdi) 🗞️🔥🚨",
            "🟡 🇮🇱  Vēl viens svaigs 24h virsraksts, jaunākie pirmie 📉🔥🌿",
            "..."
          ]
        },
    "triggers_to_watch": {
      "en": ["🔥 trigger one", "📉 trigger two", ...],
      "ru": ["🔥 триггер один", "📉 триггер два", ...],
      "uk": ["🔥 тригер один", "📉 тригер два", ...],
      "de": ["🔥 Auslöser eins", "📉 Auslöser zwei", ...],
      "lv": ["🔥 trigeris viens", "📉 trigeris divi", ...]
    }
  },
  "countries": [
    {
      "name": "🇺🇸 USA",
      "liquidity": ??,
      "logistics": ??,
      "legitimacy": ??,
      "overall": ??,
      "family_safety_note": {
        "en": "⛽ Specific current advice based on actual conditions",
        "ru": "⛽ Конкретный совет на основе текущей ситуации",
        "uk": "⛽ Конкретна порада на основі поточної ситуації",
        "de": "⛽ Spezifischer aktueller Rat basierend auf tatsächlichen Bedingungen",
        "lv": "⛽ Konkrēti padomi, pamatojoties uz faktiskajiem apstākļiem"
      }
    },
    {
      "name": "🇩🇪 Germany",
      "liquidity": ??,
      "logistics": ??,
      "legitimacy": ??,
      "overall": ??,
      "family_safety_note": {
        "en": "🌿 Specific current advice based on actual conditions or nothing like 'everything is fine - chill'",
        "ru": "🌿 Конкретный совет на основе текущей ситуации или ничего, как 'всё хорошо - расслабьтесь'",
        "uk": "🌿 Конкретна порада на основі поточної ситуації або нічого, як 'все добре - розслабтесь'",
        "de": "🌿 Spezifischer aktueller Rat basierend auf tatsächlichen Bedingungen oder nichts wie 'alles ist gut - chill'",
        "lv": "🌿 Konkrēti padomi, pamatojoties uz faktiskajiem apstākļiem vai nekas, kā 'visi ir labi - chill'"
      }
    }
    // ... remaining 20 countries with the same structure (including Ukraine, Latvia, etc.)
  ],
  "scenarios": [
    {
      "name": "optimistic",
      "description": {
        "en": "English hopeful path",
        "ru": "Русский оптимистичный сценарий",
        "uk": "Український оптимістичний сценарій",
        "de": "Deutscher hoffnungsvoller Pfad",
        "lv": "Latviešu cerību pilnais ceļš"
      },
      "when_visible": "20??-??",
      "probability_percent": ??
    },
    {
      "name": "realistic",
      "description": {
        "en": "English likely path",
        "ru": "Русский реалистичный путь",
        "uk": "Український реалістичний шлях",
        "de": "Deutscher wahrscheinlicher Weg",
        "lv": "Latviešu ticamais ceļš"
      },
      "when_visible": "20??-??",
      "probability_percent": ??
    },
    {
      "name": "pessimistic_100_chaos",
      "description": {
        "en": "English cascade details",
        "ru": "Русский детальный каскад",
        "uk": "Український детальний каскад",
        "de": "Deutscher Kaskadendetails",
        "lv": "Latviešu kaskādes detaļas"
      },
      "earliest_date": "20??-??",
      "probability_percent": ??
    }
  ],
  "key_indicators_today": {
    "gold_price": ????.??,
    "brent_crude": ????.??,
    "vix_fear_index": ???.?,
    "global_economic_policy_uncertainty_index": ???,
    "most_critical_shipping_chokepoint_status": {
      "en": "⛽ Nothing or few sentences describing current status based on actual conditions",
      "ru": "⛽ Ничего или несколько предложений о текущем статусе на основе фактических условий",
      "uk": "⛽ Нічого або кілька речень про поточний статус на основі фактичних умов",
      "de": "⛽ Nichts oder einige Aussagen über den aktuellen Status basierend auf tatsächlichen Bedingungen",
      "lv": "⛽ Neviena vai daži teikumi par pašreizējo statusu, pamatojoties uz faktiskajiem apstākļiem"
    }
  }
}
PROMPT;
    }
}
