<?php

namespace Tests\Feature\Middleware;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckSuspendedTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_accessible_when_not_suspended()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_site_shows_maintenance_when_suspended()
    {
        Setting::set('suspended', 'true');

        $response = $this->get('/');
        $response->assertStatus(503);
    }

    public function test_admin_can_access_when_suspended()
    {
        Setting::set('suspended', 'true');
        $user = User::factory()->create();

        // Use settings page (doesn't require onboarding and doesn't use MySQL-specific functions)
        $response = $this->actingAs($user)->get(route('admin.settings.index'));
        $response->assertStatus(200);
    }

    public function test_api_master_bypasses_suspension()
    {
        Setting::set('suspended', 'true');
        config(['services.master.token' => 'test-token']);

        $response = $this->getJson('/api/master/health', [
            'X-Master-Token' => 'test-token',
        ]);

        $response->assertStatus(200);
    }
}
