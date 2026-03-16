<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Sale;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetupOnboarding;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, SetupOnboarding;

    public function test_dashboard_requires_auth()
    {
        $this->completeOnboarding();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_loads_with_data()
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Dashboard uses MySQL MONTH() function not available in SQLite.');
        }

        $this->completeOnboarding();
        $user = User::factory()->create();
        Vehicle::factory()->count(5)->create();
        Sale::factory()->count(2)->create();
        Lead::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_dashboard_loads_empty()
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Dashboard uses MySQL MONTH() function not available in SQLite.');
        }

        $this->completeOnboarding();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}
