<?php

namespace Tests\Unit;

use App\Models\ApiUser;
use Filament\Panel;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiUserTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function has_role_returns_true_when_user_has_role(): void
    {
        $user = new ApiUser(['id' => 1, 'name' => 'Admin', 'email' => 'a@b.com', 'roles' => ['admin', 'editor']]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertFalse($user->hasRole('viewer'));
    }

    #[Test]
    public function has_any_role_returns_true_when_user_has_one(): void
    {
        $user = new ApiUser(['id' => 1, 'roles' => ['admin']]);

        $this->assertTrue($user->hasAnyRole(['admin', 'editor']));
        $this->assertFalse($user->hasAnyRole(['editor', 'viewer']));
    }

    #[Test]
    public function can_access_panel_returns_true_only_for_admin_role(): void
    {
        $panel = Mockery::mock(Panel::class);

        $admin = new ApiUser(['id' => 1, 'roles' => ['admin']]);
        $this->assertTrue($admin->canAccessPanel($panel));

        $user = new ApiUser(['id' => 2, 'roles' => ['user']]);
        $this->assertFalse($user->canAccessPanel($panel));
    }

    #[Test]
    public function get_filament_name_returns_name(): void
    {
        $user = new ApiUser(['name' => 'Test User']);
        $this->assertSame('Test User', $user->getFilamentName());
    }

    #[Test]
    public function to_array_returns_all_attributes(): void
    {
        $user = new ApiUser([
            'id' => 1,
            'name' => 'Test',
            'email' => 'test@example.com',
            'roles' => ['admin'],
            'created_at' => '2026-01-01T00:00:00.000000Z',
        ]);

        $arr = $user->toArray();
        $this->assertEquals(1, $arr['id']);
        $this->assertEquals('Test', $arr['name']);
        $this->assertEquals('test@example.com', $arr['email']);
        $this->assertEquals(['admin'], $arr['roles']);
    }
}
