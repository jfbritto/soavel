<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetupOnboarding;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase, SetupOnboarding;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->completeOnboarding();
    }

    public function test_index_lists_sales()
    {
        Sale::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('admin.sales.index'));
        $response->assertStatus(200);
    }

    public function test_create_shows_form()
    {
        $response = $this->actingAs($this->user)->get(route('admin.sales.create'));
        $response->assertStatus(200);
    }

    public function test_store_creates_sale()
    {
        $vehicle = Vehicle::factory()->disponivel()->create();
        $customer = Customer::factory()->create();

        $data = [
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'preco_venda' => 85000,
            'tipo_pagamento' => 'a_vista',
            'data_venda' => now()->format('Y-m-d'),
            'status' => 'concluida',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', [
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), []);
        $response->assertSessionHasErrors(['vehicle_id', 'customer_id', 'preco_venda', 'tipo_pagamento', 'data_venda', 'status']);
    }

    public function test_store_validates_vehicle_exists()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), [
            'vehicle_id' => 999,
            'customer_id' => $customer->id,
            'preco_venda' => 50000,
            'tipo_pagamento' => 'a_vista',
            'data_venda' => now()->format('Y-m-d'),
            'status' => 'concluida',
        ]);

        $response->assertSessionHasErrors('vehicle_id');
    }

    public function test_show_displays_sale()
    {
        $sale = Sale::factory()->create();

        $response = $this->actingAs($this->user)->get(route('admin.sales.show', $sale));
        $response->assertStatus(200);
    }

    public function test_update_status()
    {
        $sale = Sale::factory()->pendente()->create();

        $response = $this->actingAs($this->user)->patch(route('admin.sales.status', $sale), [
            'status' => 'concluida',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'status' => 'concluida']);
    }
}
