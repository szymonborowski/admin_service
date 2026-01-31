<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;

class ApiUser implements Authenticatable, FilamentUser
{
    public int $id;
    public string $name;
    public string $email;
    public array $roles;
    public ?string $created_at;
    private string $password = '';
    private string $rememberToken = '';

    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'] ?? 0;
        $this->name = $attributes['name'] ?? '';
        $this->email = $attributes['email'] ?? '';
        $this->roles = $attributes['roles'] ?? [];
        $this->created_at = $attributes['created_at'] ?? null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return !empty(array_intersect($this->roles, $roles));
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function getRememberToken(): ?string
    {
        return $this->rememberToken;
    }

    public function setRememberToken($value): void
    {
        $this->rememberToken = $value ?? '';
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return null;
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getAttributeValue(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function getAttribute(string $key): mixed
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return null;
    }

    public function getKey(): mixed
    {
        return $this->id;
    }

    public function getKeyName(): string
    {
        return 'id';
    }

    public function getMorphClass(): string
    {
        return static::class;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles,
            'created_at' => $this->created_at,
        ];
    }
}
