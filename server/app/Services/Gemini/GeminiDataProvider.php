<?php

namespace App\Services\Gemini;

use App\Contracts\DashboardDataProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class GeminiDataProvider implements DashboardDataProviderInterface
{
    private const ENDPOINT_TEMPLATE = '%smodels/%s:generateContent';

    public function __construct(
        private readonly Client $client,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUri,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fetch(string $when = 'now'): array
    {
        $url = sprintf(
            self::ENDPOINT_TEMPLATE,
            $this->baseUri,
            $this->model
        );

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => Prompts::dashboardPrompt($when)]],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 16384,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $response = $this->client->post($url, [
                'query' => ['key' => $this->apiKey],
                'json' => $payload,
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Gemini API request failed: ' . $e->getMessage(), 0, $e);
        }

        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response from Gemini: ' . json_last_error_msg());
        }

        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            throw new RuntimeException('Empty or unexpected response structure from Gemini');
        }

        $dashboard = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Gemini returned non-JSON content: ' . json_last_error_msg());
        }

        return $dashboard;
    }
}
