<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class BlogApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.blog.url', 'http://blog-nginx');
        $this->apiKey = config('services.blog.api_key', '');
    }

    public function getSlides(): array
    {
        $response = $this->request('GET', '/api/internal/slides');

        if ($response->successful()) {
            return $response->json('data') ?? [];
        }

        return [];
    }

    public function getSlide(int $id): ?array
    {
        $response = $this->request('GET', "/api/internal/slides/{$id}");

        if ($response->successful()) {
            return $response->json('data') ?? $response->json();
        }

        return null;
    }

    public function createSlide(array $data): ?array
    {
        $response = $this->request('POST', '/api/internal/slides', [], $data);

        if ($response->successful()) {
            return $response->json('data') ?? $response->json();
        }

        return null;
    }

    public function updateSlide(int $id, array $data): ?array
    {
        $response = $this->request('PUT', "/api/internal/slides/{$id}", [], $data);

        if ($response->successful()) {
            return $response->json('data') ?? $response->json();
        }

        return null;
    }

    public function deleteSlide(int $id): bool
    {
        $response = $this->request('DELETE', "/api/internal/slides/{$id}");

        return $response->successful();
    }

    public function reorderSlides(array $slides): bool
    {
        $response = $this->request('PATCH', '/api/internal/slides/reorder', [], [
            'slides' => $slides,
        ]);

        return $response->successful();
    }

    private function request(string $method, string $path, array $query = [], array $data = []): Response
    {
        $url = rtrim($this->baseUrl, '/') . $path;

        $request = Http::withHeaders([
            'X-Internal-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withoutVerifying()->timeout(10);

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return match (strtoupper($method)) {
            'GET' => $request->get($url),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'PATCH' => $request->patch($url, $data),
            'DELETE' => $request->delete($url),
            default => $request->get($url),
        };
    }
}
