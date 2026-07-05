<?php

namespace App\Services\Gemini;

use App\Contracts\DashboardDataProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use RuntimeException;

class GeminiDataProvider implements DashboardDataProviderInterface
{
    private const ENDPOINT_TEMPLATE = '%smodels/%s:generateContent';
    private const MAX_RETRIES = 5;
    private const INITIAL_RETRY_DELAY = 2;
    private const MAX_RETRY_DELAY = 8;

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

        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $response = $this->client->post($url, [
                    'query' => ['key' => $this->apiKey],
                    'json' => $payload,
                ]);

                $body = (string) $response->getBody();
                $decoded = json_decode($body, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('Invalid JSON response from Gemini: ' . json_last_error_msg());
                }

                $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!$text) {
                    throw new RuntimeException('Empty or unexpected response structure from Gemini');
                }

                $dashboard = json_decode($this->stripJsonFences($text), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // The (preview) model intermittently emits malformed JSON — e.g. an
                    // unescaped character inside a string on the multilingual content —
                    // even with finishReason=STOP and responseMimeType=application/json.
                    // A fresh generation almost always parses, so treat this as retryable.
                    throw new RuntimeException('Gemini returned non-JSON content: ' . json_last_error_msg());
                }

                return $dashboard;
            } catch (ServerException $e) {
                $lastException = $e;
                // 4xx (bad key/model/quota) will never recover — fail fast.
                if ($e->getResponse()->getStatusCode() < 500) {
                    throw new RuntimeException('Gemini API request failed: ' . $e->getMessage(), 0, $e);
                }
            } catch (GuzzleException $e) {
                // Network/timeout — retryable.
                $lastException = $e;
            } catch (RuntimeException $e) {
                // Malformed / empty / non-JSON response — retryable.
                $lastException = $e;
            }

            if ($attempt < self::MAX_RETRIES) {
                $delay = min(self::INITIAL_RETRY_DELAY * (2 ** ($attempt - 1)), self::MAX_RETRY_DELAY);
                error_log(
                    "Gemini fetch attempt {$attempt}/" . self::MAX_RETRIES
                    . " failed ({$lastException->getMessage()}). Retrying in {$delay}s..."
                );
                sleep($delay);
            }
        }

        throw new RuntimeException(
            'Gemini API request failed after ' . self::MAX_RETRIES . ' attempts: '
            . ($lastException ? $lastException->getMessage() : 'Unknown error'),
            0,
            $lastException
        );
    }

    /**
     * Strip an optional leading ```json / trailing ``` markdown fence the model
     * sometimes wraps its output in, so json_decode sees raw JSON.
     */
    private function stripJsonFences(string $text): string
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```$/', '', (string) $text);
            $text = trim((string) $text);
        }

        return $text;
    }
}
