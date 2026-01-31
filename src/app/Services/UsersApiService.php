<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class UsersApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.users.url', 'http://users-nginx');
        $this->apiKey = config('services.users.api_key', '');
    }

    public function getUsers(int $page = 1): array
    {
        $response = $this->request('GET', '/api/internal/users', ['page' => $page]);

        if ($response->successful()) {
            return $response->json();
        }

        return ['data' => [], 'meta' => []];
    }

    public function getUser(int $id): ?array
    {
        $response = $this->request('GET', "/api/internal/users/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function createUser(array $data): ?array
    {
        $response = $this->request('POST', '/api/internal/users', [], $data);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getRoles(): array
    {
        $response = $this->request('GET', '/api/internal/roles');

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    public function getUserRoles(int $userId): array
    {
        $response = $this->request('GET', "/api/internal/users/{$userId}/roles");

        if ($response->successful()) {
            return $response->json()['roles'] ?? [];
        }

        return [];
    }

    public function assignRole(int $userId, string $role): bool
    {
        $response = $this->request('POST', "/api/internal/users/{$userId}/roles", [], ['role' => $role]);

        return $response->successful();
    }

    public function removeRole(int $userId, string $role): bool
    {
        $response = $this->request('DELETE', "/api/internal/users/{$userId}/roles/{$role}");

        return $response->successful();
    }

    public function updateUser(int $userId, array $data): ?array
    {
        $response = $this->request('PUT', "/api/internal/users/{$userId}", [], $data);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function deleteUser(int $userId): bool
    {
        $response = $this->request('DELETE', "/api/internal/users/{$userId}");

        return $response->successful();
    }

    public function checkCredentials(string $email, string $password): ?array
    {
        $response = $this->request('POST', '/api/internal/auth/check', [], [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if ($data['authorized'] ?? false) {
                return $data['user'];
            }
        }

        return null;
    }

    private function request(string $method, string $path, array $query = [], array $data = []): Response
    {
        $url = rtrim($this->baseUrl, '/') . $path;

        $request = Http::withHeaders([
            'X-Internal-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(10);

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return match (strtoupper($method)) {
            'GET' => $request->get($url),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'DELETE' => $request->delete($url),
            default => $request->get($url),
        };
    }
}
