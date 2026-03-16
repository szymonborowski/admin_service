<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class AnalyticsApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.analytics.url', 'http://analytics-nginx'), '/');
        $this->apiKey  = config('services.analytics.internal_api_key') ?? '';
    }

    /**
     * Top posts by views for a given period.
     * period: 7d | 30d | 90d
     */
    public function trending(string $period = '7d', int $limit = 10): array
    {
        $response = $this->get('/api/v1/trending', [
            'period' => $period,
            'limit'  => $limit,
        ]);

        return $response->successful() ? ($response->json() ?? []) : [];
    }

    /**
     * Stats for a single post.
     * period: day | week | month | year | all
     */
    public function postStats(string $postUuid, string $period = 'month'): array
    {
        $response = $this->get("/api/v1/posts/{$postUuid}/stats", ['period' => $period]);

        return $response->successful() ? ($response->json() ?? []) : [];
    }

    private function get(string $path, array $query = []): Response
    {
        return Http::withHeaders([
            'X-Internal-Api-Key' => $this->apiKey,
            'Accept'             => 'application/json',
        ])->timeout(5)->get($this->baseUrl . $path, $query);
    }
}
