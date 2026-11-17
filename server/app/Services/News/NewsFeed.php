<?php

namespace App\Services\News;

use App\Models\NewsItem;
use GuzzleHttp\Client;

/**
 * Real headlines from public RSS feeds. The language model has no web access and
 * invents "news of the last 24 hours" when asked for it, so the headlines are
 * loaded here and handed to it as facts. They are also stored, with links, as raw
 * material for a future event timeline.
 */
class NewsFeed
{
    public const FEEDS = [
        'BBC' => 'https://feeds.bbci.co.uk/news/world/rss.xml',
        'BBC Business' => 'https://feeds.bbci.co.uk/news/business/rss.xml',
        'Al Jazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
        'DW' => 'https://rss.dw.com/rdf/rss-en-all',
        'France 24' => 'https://www.france24.com/en/rss',
        'The Guardian' => 'https://www.theguardian.com/world/rss',
        'LSM' => 'https://eng.lsm.lv/rss/',
    ];

    private const PER_FEED = 14;
    private const MAX_AGE_HOURS = 36;

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * Load every feed, store new items, return the fresh ones. A feed that fails is skipped.
     *
     * @return array<int, array{source: string, title: string, time: string}> newest first
     */
    public function refresh(): array
    {
        $fresh = [];
        $cutoff = time() - self::MAX_AGE_HOURS * 3600;

        foreach (self::FEEDS as $source => $url) {
            try {
                $items = $this->fetch($url);
            } catch (\Throwable) {
                continue;
            }

            usort($items, fn ($a, $b) => $b['ts'] <=> $a['ts']);
            foreach (array_slice($items, 0, self::PER_FEED) as $item) {
                if ($item['ts'] < $cutoff || $item['ts'] > time() + 3600) {
                    continue;
                }
                NewsItem::updateOrCreate(
                    ['url' => $item['url']],
                    ['source' => $source, 'title' => $item['title'], 'published_at' => gmdate('Y-m-d H:i:s', $item['ts'])],
                );
                $fresh[] = ['source' => $source, 'title' => $item['title'], 'ts' => $item['ts']];
            }
        }

        usort($fresh, fn ($a, $b) => $b['ts'] <=> $a['ts']);

        return array_map(fn ($i) => [
            'source' => $i['source'],
            'title' => $i['title'],
            'time' => gmdate('Y-m-d H:i', $i['ts']) . ' UTC',
        ], $fresh);
    }

    /**
     * @return array<int, array{title: string, url: string, ts: int}>
     */
    private function fetch(string $url): array
    {
        $response = $this->client->get($url, [
            'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; keep-calm-dashboard)'],
            'timeout' => 15,
        ]);
        $xml = @simplexml_load_string((string) $response->getBody());
        if ($xml === false) {
            throw new \RuntimeException("Feed is not XML: {$url}");
        }

        // RSS 2.0 keeps items under <channel>, RDF (RSS 1.0) next to it.
        $nodes = isset($xml->channel->item) ? $xml->channel->item : $xml->item;
        $items = [];
        foreach ($nodes as $node) {
            $title = trim(html_entity_decode(strip_tags((string) $node->title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $link = trim((string) $node->link);
            $date = (string) $node->pubDate ?: (string) $node->children('http://purl.org/dc/elements/1.1/')->date;
            $ts = strtotime($date);
            if ($title === '' || $link === '' || $ts === false) {
                continue;
            }
            $items[] = ['title' => mb_substr($title, 0, 500), 'url' => mb_substr($link, 0, 1000), 'ts' => $ts];
        }

        return $items;
    }
}
