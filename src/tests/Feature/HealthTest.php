<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthTest extends TestCase
{
    #[Test]
    public function health_returns_ok(): void
    {
        $response = $this->get('/health');

        $response->assertOk()->assertJson(['status' => 'ok']);
    }

    #[Test]
    public function ready_returns_ready_when_db_connected(): void
    {
        $response = $this->get('/ready');

        $response->assertOk()->assertJson(['status' => 'ready']);
    }
}
