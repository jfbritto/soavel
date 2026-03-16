<?php

namespace Tests\Unit;

use App\Models\Vehicle;
use App\Models\VehicleFeature;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_titulo_accessor()
    {
        $vehicle = Vehicle::factory()->create([
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'versao' => 'XEi 2.0',
        ]);

        $this->assertEquals('Toyota Corolla XEi 2.0', $vehicle->titulo);
    }

    public function test_preco_formatado_accessor()
    {
        $vehicle = Vehicle::factory()->create(['preco' => 85000.00]);
        $this->assertStringContainsString('85', $vehicle->precoFormatado);
    }

    public function test_km_formatado_accessor()
    {
        $vehicle = Vehicle::factory()->create(['km' => 45000]);
        $this->assertStringContainsString('45', $vehicle->kmFormatado);
    }

    public function test_status_label_accessor()
    {
        $vehicle = Vehicle::factory()->create(['status' => 'disponivel']);
        $this->assertEquals('Disponível', $vehicle->statusLabel);

        $vehicle->status = 'vendido';
        $this->assertEquals('Vendido', $vehicle->statusLabel);

        $vehicle->status = 'reservado';
        $this->assertEquals('Reservado', $vehicle->statusLabel);
    }

    public function test_status_color_accessor()
    {
        $vehicle = Vehicle::factory()->create(['status' => 'disponivel']);
        $this->assertEquals('success', $vehicle->statusColor);

        $vehicle->status = 'vendido';
        $this->assertEquals('danger', $vehicle->statusColor);

        $vehicle->status = 'reservado';
        $this->assertEquals('warning', $vehicle->statusColor);
    }

    public function test_scope_disponivel()
    {
        Vehicle::factory()->count(2)->disponivel()->create();
        Vehicle::factory()->vendido()->create();

        $this->assertCount(2, Vehicle::disponivel()->get());
    }

    public function test_scope_destaque()
    {
        Vehicle::factory()->destaque()->create();
        Vehicle::factory()->create(['destaque' => false]);

        $this->assertCount(1, Vehicle::destaque()->get());
    }

    public function test_generate_slug_is_unique()
    {
        $slug1 = Vehicle::generateSlug('Toyota', 'Corolla', 2024);
        Vehicle::factory()->create(['slug' => $slug1]);
        $slug2 = Vehicle::generateSlug('Toyota', 'Corolla', 2024);

        $this->assertNotEquals($slug1, $slug2);
    }

    public function test_has_many_features()
    {
        $vehicle = Vehicle::factory()->create();
        VehicleFeature::create(['vehicle_id' => $vehicle->id, 'feature' => 'Ar condicionado']);
        VehicleFeature::create(['vehicle_id' => $vehicle->id, 'feature' => 'Direção elétrica']);

        $this->assertCount(2, $vehicle->features);
    }

    public function test_has_many_leads()
    {
        $vehicle = Vehicle::factory()->create();
        Lead::factory()->count(3)->create(['vehicle_id' => $vehicle->id]);

        $this->assertCount(3, $vehicle->leads);
    }

    public function test_has_many_expenses()
    {
        $vehicle = Vehicle::factory()->create();
        Expense::factory()->count(2)->create(['vehicle_id' => $vehicle->id]);

        $this->assertCount(2, $vehicle->expenses);
    }

    public function test_has_many_sales()
    {
        $vehicle = Vehicle::factory()->create();
        Sale::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertCount(1, $vehicle->sales);
    }
}
