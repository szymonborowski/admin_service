<?php

namespace Tests\Feature;

use App\Models\ApiUser;
use App\Services\UsersApiService;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManageUsersPageTest extends TestCase
{
    private function adminUser(): ApiUser
    {
        return new ApiUser([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'roles' => ['admin'],
            'created_at' => now()->toISOString(),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.users.url' => 'http://users-nginx',
            'services.users.api_key' => 'test-key',
        ]);
    }

    #[Test]
    public function manage_users_page_loads_with_users_and_roles(): void
    {
        Http::fake([
            'users-nginx/api/internal/users*' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'User One', 'email' => 'u1@test.com', 'roles' => [], 'created_at' => '2026-01-01T12:00:00.000000Z'],
                    ['id' => 2, 'name' => 'User Two', 'email' => 'u2@test.com', 'roles' => ['editor'], 'created_at' => '2026-01-01T12:00:00.000000Z'],
                ],
                'meta' => ['current_page' => 1],
            ], 200),
            'users-nginx/api/internal/roles' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'admin', 'level' => 100],
                    ['id' => 2, 'name' => 'editor', 'level' => 50],
                ],
            ], 200),
        ]);

        $this->actingAs($this->adminUser());

        $component = Livewire::test(\App\Filament\Pages\ManageUsers::class);

        $component->assertOk();
        $component->assertSet('users', [
            ['id' => 1, 'name' => 'User One', 'email' => 'u1@test.com', 'roles' => [], 'created_at' => '2026-01-01T12:00:00.000000Z'],
            ['id' => 2, 'name' => 'User Two', 'email' => 'u2@test.com', 'roles' => ['editor'], 'created_at' => '2026-01-01T12:00:00.000000Z'],
        ]);
    }

    #[Test]
    public function assign_role_success_refreshes_data(): void
    {
        Http::fake([
            'users-nginx/api/internal/users*' => Http::response([
                'data' => [['id' => 1, 'name' => 'User One', 'email' => 'u1@test.com', 'roles' => [], 'created_at' => '2026-01-01T12:00:00.000000Z']],
                'meta' => [],
            ], 200),
            'users-nginx/api/internal/roles' => Http::response(['data' => [['id' => 1, 'name' => 'admin', 'level' => 100]]], 200),
            'users-nginx/api/internal/users/1/roles' => Http::response(null, 204),
        ]);

        $this->actingAs($this->adminUser());

        $component = Livewire::test(\App\Filament\Pages\ManageUsers::class)
            ->set('selectedUserId', 1)
            ->set('selectedRole', 'admin')
            ->call('assignRole');

        $component->assertHasNoErrors();
    }

    #[Test]
    public function remove_user_role_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/users*' => Http::response([
                'data' => [['id' => 1, 'name' => 'User One', 'email' => 'u1@test.com', 'roles' => ['admin'], 'created_at' => '2026-01-01T12:00:00.000000Z']],
                'meta' => [],
            ], 200),
            'users-nginx/api/internal/roles' => Http::response(['data' => []], 200),
            'users-nginx/api/internal/users/1/roles/admin' => Http::response(null, 204),
        ]);

        $this->actingAs($this->adminUser());

        Livewire::test(\App\Filament\Pages\ManageUsers::class)
            ->call('removeUserRole', 1, 'admin')
            ->assertHasNoErrors();
    }

    #[Test]
    public function save_user_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/users*' => Http::response([
                'data' => [['id' => 1, 'name' => 'Updated', 'email' => 'updated@test.com', 'roles' => [], 'created_at' => '2026-01-01T12:00:00.000000Z']],
                'meta' => [],
            ], 200),
            'users-nginx/api/internal/roles' => Http::response(['data' => []], 200),
            'users-nginx/api/internal/users/1' => Http::response([
                'id' => 1,
                'name' => 'Updated',
                'email' => 'updated@test.com',
            ], 200),
        ]);

        $this->actingAs($this->adminUser());

        Livewire::test(\App\Filament\Pages\ManageUsers::class)
            ->set('editUserId', 1)
            ->set('editUserName', 'Updated')
            ->set('editUserEmail', 'updated@test.com')
            ->call('saveUser')
            ->assertHasNoErrors();
    }

    #[Test]
    public function delete_user_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/users*' => Http::response([
                'data' => [],
                'meta' => [],
            ], 200),
            'users-nginx/api/internal/roles' => Http::response(['data' => []], 200),
            'users-nginx/api/internal/users/1' => Http::response(null, 204),
        ]);

        $this->actingAs($this->adminUser());

        Livewire::test(\App\Filament\Pages\ManageUsers::class)
            ->call('deleteUser', 1)
            ->assertHasNoErrors();
    }
}
