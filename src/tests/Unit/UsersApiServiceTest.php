<?php

namespace Tests\Unit;

use App\Services\UsersApiService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsersApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.users.url' => 'http://users-nginx',
            'services.users.api_key' => 'test-api-key',
        ]);
    }

    #[Test]
    public function get_users_returns_data_on_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/users*' => Http::response([
                'data' => [['id' => 1, 'name' => 'User 1']],
                'meta' => ['current_page' => 1],
            ], 200),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->getUsers(1);

        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['data'][0]['id']);
    }

    #[Test]
    public function get_users_returns_empty_on_failure(): void
    {
        Http::fake([
            'users-nginx/api/internal/users*' => Http::response(null, 500),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->getUsers(1);

        $this->assertEquals(['data' => [], 'meta' => []], $result);
    }

    #[Test]
    public function get_user_returns_user_on_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/users/1' => Http::response([
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ], 200),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->getUser(1);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('test@example.com', $result['email']);
    }

    #[Test]
    public function get_user_returns_null_on_404(): void
    {
        Http::fake([
            'users-nginx/api/internal/users/999' => Http::response(null, 404),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->getUser(999);

        $this->assertNull($result);
    }

    #[Test]
    public function check_credentials_returns_user_when_authorized(): void
    {
        Http::fake([
            'users-nginx/api/internal/auth/check' => Http::response([
                'authorized' => true,
                'user' => [
                    'id' => 1,
                    'email' => 'test@example.com',
                    'name' => 'Test',
                ],
            ], 200),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->checkCredentials('test@example.com', 'password');

        $this->assertNotNull($result);
        $this->assertEquals('test@example.com', $result['email']);
    }

    #[Test]
    public function check_credentials_returns_null_when_unauthorized(): void
    {
        Http::fake([
            'users-nginx/api/internal/auth/check' => Http::response(['authorized' => false], 401),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->checkCredentials('test@example.com', 'wrong');

        $this->assertNull($result);
    }

    #[Test]
    public function get_roles_returns_roles_on_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/roles' => Http::response([
                'data' => [['id' => 1, 'name' => 'admin']],
            ], 200),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->getRoles();

        $this->assertArrayHasKey('data', $result);
    }

    #[Test]
    public function assign_role_returns_true_on_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/users/1/roles' => Http::response(null, 204),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->assignRole(1, 'admin');

        $this->assertTrue($result);
    }

    #[Test]
    public function delete_user_returns_true_on_success(): void
    {
        Http::fake([
            'users-nginx/api/internal/users/1' => Http::response(null, 204),
        ]);

        $service = app(UsersApiService::class);
        $result = $service->deleteUser(1);

        $this->assertTrue($result);
    }
}
