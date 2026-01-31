<?php

namespace App\Auth;

use App\Models\ApiUser;
use App\Services\UsersApiService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class ApiUserProvider implements UserProvider
{
    public function __construct(
        private UsersApiService $usersApiService
    ) {}

    public function retrieveById($identifier): ?Authenticatable
    {
        $userData = $this->usersApiService->getUser((int) $identifier);

        if (!$userData) {
            return null;
        }

        return new ApiUser($userData);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Not supported for API-based users
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            return null;
        }

        $userData = $this->usersApiService->checkCredentials(
            $credentials['email'],
            $credentials['password']
        );

        if (!$userData) {
            return null;
        }

        return new ApiUser($userData);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // Credentials are already validated in retrieveByCredentials
        return true;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Not supported for API-based users
    }
}
