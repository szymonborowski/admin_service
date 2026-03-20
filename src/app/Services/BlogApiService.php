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

    // Posts

    public function getPosts(array $query = []): array
    {
        $response = $this->request('GET', '/api/internal/posts', $query);

        if ($response->successful()) {
            return $response->json();
        }

        return ['data' => [], 'meta' => []];
    }

    public function getPost(int $id): ?array
    {
        $response = $this->request('GET', "/api/internal/posts/{$id}");

        if ($response->successful()) {
            return $response->json('data') ?? $response->json();
        }

        return null;
    }

    public function createPost(array $data): array
    {
        $response = $this->request('POST', '/api/internal/posts', [], $data);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json('data') ?? $response->json()];
        }

        return ['success' => false, 'status' => $response->status(), 'body' => $response->json()];
    }

    public function updatePost(int $id, array $data): array
    {
        $response = $this->request('PUT', "/api/internal/posts/{$id}", [], $data);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json('data') ?? $response->json()];
        }

        return ['success' => false, 'status' => $response->status(), 'body' => $response->json()];
    }

    public function deletePost(int $id): bool
    {
        return $this->request('DELETE', "/api/internal/posts/{$id}")->successful();
    }

    // Featured Posts (Most Important Posts widget)

    public function getFeaturedPosts(): array
    {
        $response = $this->request('GET', '/api/internal/featured-posts');

        if ($response->successful()) {
            return $response->json('data') ?? [];
        }

        return [];
    }

    public function addFeaturedPost(int $postId): array
    {
        $response = $this->request('POST', '/api/internal/featured-posts', [], [
            'post_id' => $postId,
        ]);

        return [
            'success' => $response->successful(),
            'status'  => $response->status(),
            'data'    => $response->json(),
        ];
    }

    public function removeFeaturedPost(int $id): bool
    {
        return $this->request('DELETE', "/api/internal/featured-posts/{$id}")->successful();
    }

    public function reorderFeaturedPosts(array $items): bool
    {
        $response = $this->request('PATCH', '/api/internal/featured-posts/reorder', [], [
            'items' => $items,
        ]);

        return $response->successful();
    }

    // Categories

    public function getCategories(): array
    {
        $response = $this->request('GET', '/api/internal/categories', ['per_page' => 100]);

        if ($response->successful()) {
            return $response->json('data') ?? [];
        }

        return [];
    }

    public function createCategory(array $data): ?array
    {
        $response = $this->request('POST', '/api/internal/categories', [], $data);

        if ($response->successful()) {
            return $response->json('data') ?? $response->json();
        }

        return null;
    }

    public function updateCategory(int $id, array $data): array
    {
        $response = $this->request('PUT', "/api/internal/categories/{$id}", [], $data);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json('data') ?? $response->json()];
        }

        return ['success' => false, 'status' => $response->status(), 'body' => $response->json()];
    }

    public function deleteCategory(int $id): bool
    {
        return $this->request('DELETE', "/api/internal/categories/{$id}")->successful();
    }

    // Tags

    public function getTags(): array
    {
        $response = $this->request('GET', '/api/internal/tags', ['per_page' => 100]);

        if ($response->successful()) {
            return $response->json('data') ?? [];
        }

        return [];
    }

    public function createTag(array $data): ?array
    {
        $response = $this->request('POST', '/api/internal/tags', [], $data);

        if ($response->successful()) {
            return $response->json('data') ?? $response->json();
        }

        return null;
    }

    public function deleteTag(int $id): bool
    {
        return $this->request('DELETE', "/api/internal/tags/{$id}")->successful();
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
