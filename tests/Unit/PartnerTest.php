<?php

namespace Tests\Unit;

use App\Models\Partner;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_many_vehicles()
    {
        $partner = Partner::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $partner->vehicles()->attach($vehicle->id, ['percentual' => 50]);

        $this->assertCount(1, $partner->vehicles);
        $this->assertEquals(50, $partner->vehicles->first()->pivot->percentual);
    }

    public function test_vehicle_has_many_partners()
    {
        $vehicle = Vehicle::factory()->create();
        $p1 = Partner::factory()->create();
        $p2 = Partner::factory()->create();

        $vehicle->partners()->attach($p1->id, ['percentual' => 30]);
        $vehicle->partners()->attach($p2->id, ['percentual' => 20]);

        $this->assertCount(2, $vehicle->partners);
    }
}
