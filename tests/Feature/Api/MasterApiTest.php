<?php

namespace Tests\Feature\Api;

use App\Models\Vehicle;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterApiTest extends TestCase
{
    use RefreshDatabase;

    private $token = 'test-master-token-123';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.master.token' => $this->token]);
    }

    public function test_health_requires_token()
    {
        $response = $this->getJson('/api/master/health');
        $response->assertStatus(401);
    }

    public function test_health_rejects_invalid_token()
    {
        $response = $this->getJson('/api/master/health', [
            'X-Master-Token' => 'wrong-token',
        ]);
        $response->assertStatus(401);
    }

    public function test_health_returns_ok()
    {
        $response = $this->getJson('/api/master/health', [
            'X-Master-Token' => $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'suspended', 'php_version', 'laravel_version', 'timestamp']);
        $response->assertJsonPath('status', 'ok');
    }

    public function test_health_shows_suspended_status()
    {
        Setting::set('suspended', 'true');

        $response = $this->getJson('/api/master/health', [
            'X-Master-Token' => $this->token,
        ]);

        $response->assertJsonPath('suspended', true);
    }

    public function test_stats_returns_counts()
    {
        Vehicle::factory()->count(5)->create();
        Vehicle::factory()->vendido()->count(2)->create();
        Lead::factory()->count(3)->create();

        $response = $this->getJson('/api/master/stats', [
            'X-Master-Token' => $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('vehicles_count', 7);
        $response->assertJsonPath('leads_count', 3);
        $response->assertJsonPath('sales_count', 2);
    }

    public function test_suspend_sets_flag()
    {
        $response = $this->postJson('/api/master/suspend', [], [
            'X-Master-Token' => $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'suspended');

        $this->assertEquals('true', Setting::get('suspended'));
    }

    public function test_reactivate_clears_flag()
    {
        Setting::set('suspended', 'true');

        $response = $this->postJson('/api/master/reactivate', [], [
            'X-Master-Token' => $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'active');

        $this->assertEquals('false', Setting::get('suspended'));
    }

    public function test_config_updates_settings()
    {
        $response = $this->postJson('/api/master/config', [
            'nome_sistema' => 'Novo Nome',
            'cor_primaria' => '#FF0000',
        ], [
            'X-Master-Token' => $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');

        $this->assertEquals('Novo Nome', Setting::get('nome_sistema'));
        $this->assertEquals('#FF0000', Setting::get('cor_primaria'));
    }
}
