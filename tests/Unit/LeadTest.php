<?php

namespace Tests\Unit;

use App\Models\Lead;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_label()
    {
        $lead = Lead::factory()->create(['status' => 'novo']);
        $this->assertEquals('Novo', $lead->statusLabel);

        $lead->status = 'em_contato';
        $this->assertEquals('Em Contato', $lead->statusLabel);

        $lead->status = 'convertido';
        $this->assertEquals('Convertido', $lead->statusLabel);

        $lead->status = 'perdido';
        $this->assertEquals('Perdido', $lead->statusLabel);
    }

    public function test_status_color()
    {
        $lead = Lead::factory()->create(['status' => 'novo']);
        $this->assertEquals('primary', $lead->statusColor);

        $lead->status = 'convertido';
        $this->assertEquals('success', $lead->statusColor);

        $lead->status = 'perdido';
        $this->assertEquals('danger', $lead->statusColor);
    }

    public function test_origem_label()
    {
        $lead = Lead::factory()->create(['origem' => 'site']);
        $this->assertEquals('Site', $lead->origemLabel);

        $lead->origem = 'whatsapp';
        $this->assertEquals('WhatsApp', $lead->origemLabel);

        $lead->origem = 'instagram';
        $this->assertEquals('Instagram', $lead->origemLabel);
    }

    public function test_belongs_to_vehicle_with_default()
    {
        $lead = Lead::factory()->create(['vehicle_id' => null]);
        $this->assertNotNull($lead->vehicle);
    }

    public function test_belongs_to_user_with_default()
    {
        $lead = Lead::factory()->create(['user_id' => null]);
        $this->assertEquals('—', $lead->user->name);
    }

    public function test_belongs_to_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        $lead = Lead::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertEquals($vehicle->id, $lead->vehicle->id);
    }
}
