<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SsoService
{
    private string $publicUrl;
    private string $internalUrl;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->publicUrl = rtrim(config('services.sso.url'), '/');
        $this->internalUrl = rtrim(config('services.sso.internal_url'), '/');
        $this->clientId = config('services.sso.client_id');
        $this->clientSecret = config('services.sso.client_secret');
        $this->redirectUri = config('services.sso.redirect_uri');
    }

    public function getAuthorizeUrl(string $state): string
    {
        return $this->publicUrl . '/oauth/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'users.read',
            'state' => $state,
        ]);
    }

    public function exchangeCodeForToken(string $code): ?array
    {
        $response = Http::asForm()
            ->post($this->internalUrl . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'code' => $code,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getUser(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)
            ->get($this->internalUrl . '/api/user');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getLogoutUrl(string $redirectUri): string
    {
        return $this->publicUrl . '/logout?' . http_build_query([
            'redirect_uri' => $redirectUri,
        ]);
    }
}
