<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class FrontendApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.frontend.url', 'http://frontend-nginx');
        $this->apiKey = config('services.frontend.api_key', '');
    }

    public function getFormSubmissions(int $page = 1, ?string $formType = null, ?string $search = null): array
    {
        $query = ['page' => $page, 'per_page' => 15];

        if ($formType) {
            $query['form_type'] = $formType;
        }

        if ($search) {
            $query['search'] = $search;
        }

        $response = $this->request('GET', '/api/internal/form-submissions', $query);

        if ($response->successful()) {
            return $response->json();
        }

        return ['data' => [], 'meta' => []];
    }

    public function getFormSubmission(int $id): ?array
    {
        $response = $this->request('GET', "/api/internal/form-submissions/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function deleteFormSubmission(int $id): bool
    {
        $response = $this->request('DELETE', "/api/internal/form-submissions/{$id}");

        return $response->successful();
    }

    private function request(string $method, string $path, array $query = []): Response
    {
        $url = rtrim($this->baseUrl, '/') . $path;

        $request = Http::withHeaders([
            'X-Internal-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ])->timeout(10);

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return match (strtoupper($method)) {
            'GET' => $request->get($url),
            'DELETE' => $request->delete($url),
            default => $request->get($url),
        };
    }
}
