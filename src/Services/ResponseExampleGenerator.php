<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use Illuminate\Support\Facades\Http;
use LaravelGenerators\PostmanGenerator\DataObjects\BodySchema;
use LaravelGenerators\PostmanGenerator\DataObjects\EnrichedRouteData;
use Throwable;

class ResponseExampleGenerator
{
    public function attach(array &$collectionItem, EnrichedRouteData $route, BodySchema $body): void
    {
        if (!config('postman-generator.generate_responses', false)) {
            return;
        }

        // FR-6: Response generation is local/testing only for safety
        if (!app()->environment('local', 'testing') && !app()->runningUnitTests()) {
            return;
        }

        $token = config('postman-generator.token');
        $isAuthProtected = $route->authType === 'bearer';

        if ($isAuthProtected && !$token) {
            return;
        }

        try {
            $response = $this->makeRequest($route, $body, $token);
            
            if ($response) {
                $collectionItem['response'][] = [
                    'name' => 'Captured Response',
                    'originalRequest' => $collectionItem['request'],
                    'status' => $response['status_text'],
                    'code' => $response['status_code'],
                    '_postman_previewlanguage' => 'json',
                    'header' => $response['headers'],
                    'body' => $response['body'],
                ];
            }
        } catch (Throwable $e) {
        }
    }

    protected function makeRequest(EnrichedRouteData $route, BodySchema $body, ?string $token): ?array
    {
        $baseUrl = config('postman-generator.base_url', 'http://localhost');
        $url = rtrim($baseUrl, '/') . '/' . ltrim($route->route->uri, '/');
        $url = preg_replace_callback('/\{(\w+)\}/', fn($m) => '1', $url);

        $timeout = (int) config('postman-generator.response_timeout', 5);
        $maxBytes = (int) config('postman-generator.response_max_bytes', 10240);

        try {
            $client = Http::timeout($timeout);
            if ($token) {
                $client = $client->withToken($token);
            }

            $method = strtolower($route->route->method);
            
            $res = match($method) {
                'get' => $client->get($url),
                'post' => $client->post($url, $body->json ?? []),
                'put' => $client->put($url, $body->json ?? []),
                'patch' => $client->patch($url, $body->json ?? []),
                'delete' => $client->delete($url, $body->json ?? []),
                default => null,
            };

            if (!$res instanceof \Illuminate\Http\Client\Response) {
                return null;
            }

            $bodyContent = (string) $res->body();
            if (strlen($bodyContent) > $maxBytes) {
                $bodyContent = substr($bodyContent, 0, $maxBytes) . "\n... [truncated]";
            }

            return [
                'status_code' => $res->status(),
                'status_text' => $this->getStatusText($res->status()),
                'headers' => $this->formatHeaders($res->headers()),
                'body' => $bodyContent,
            ];
        } catch (Throwable $e) {
            return null;
        }
    }

    protected function getStatusText(int $code): string
    {
        $texts = [200 => 'OK', 201 => 'Created', 204 => 'No Content', 400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found', 500 => 'Internal Server Error'];
        return $texts[$code] ?? 'Unknown';
    }

    protected function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $values) {
            $formatted[] = [
                'key' => $key,
                'value' => implode(', ', $values),
            ];
        }
        return $formatted;
    }
}
