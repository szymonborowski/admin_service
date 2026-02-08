<?php

namespace Tests\Unit;

use App\Auth\ApiUserProvider;
use App\Models\ApiUser;
use App\Services\UsersApiService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiUserProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function retrieve_by_id_returns_api_user(): void
    {
        $mockService = Mockery::mock(UsersApiService::class);
        $mockService->shouldReceive('getUser')
            ->with(1)
            ->once()
            ->andReturn([
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'created_at' => '2026-01-28T12:00:00.000000Z',
            ]);

        $provider = new ApiUserProvider($mockService);
        $user = $provider->retrieveById(1);

        $this->assertInstanceOf(ApiUser::class, $user);
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function retrieve_by_id_returns_null_for_nonexistent_user(): void
    {
        $mockService = Mockery::mock(UsersApiService::class);
        $mockService->shouldReceive('getUser')->with(999)->once()->andReturn(null);

        $provider = new ApiUserProvider($mockService);
        $this->assertNull($provider->retrieveById(999));
    }

    #[Test]
    public function retrieve_by_credentials_returns_user_when_valid(): void
    {
        $mockService = Mockery::mock(UsersApiService::class);
        $mockService->shouldReceive('checkCredentials')
            ->with('test@example.com', 'password')
            ->once()
            ->andReturn([
                'id' => 1,
                'name' => 'Test',
                'email' => 'test@example.com',
                'created_at' => now()->toISOString(),
            ]);

        $provider = new ApiUserProvider($mockService);
        $user = $provider->retrieveByCredentials([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertInstanceOf(ApiUser::class, $user);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function retrieve_by_credentials_returns_null_when_email_missing(): void
    {
        $mockService = Mockery::mock(UsersApiService::class);
        $mockService->shouldNotReceive('checkCredentials');

        $provider = new ApiUserProvider($mockService);
        $this->assertNull($provider->retrieveByCredentials(['password' => 'p']));
    }

    #[Test]
    public function validate_credentials_returns_true(): void
    {
        $user = new ApiUser(['id' => 1, 'email' => 't@t.com', 'name' => 'T']);
        $mockService = Mockery::mock(UsersApiService::class);
        $provider = new ApiUserProvider($mockService);

        $this->assertTrue($provider->validateCredentials($user, ['email' => 't@t.com', 'password' => 'p']));
    }
}
