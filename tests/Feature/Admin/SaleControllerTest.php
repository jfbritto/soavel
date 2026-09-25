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

    // ── Veículos de troca ─────────────────────────────────────────────────────

    private function trocaPayload(array $overrides = []): array
    {
        return array_merge([
            'valor_troca'    => 30000,
            'marca'          => 'Fiat',
            'modelo'         => 'Argo Drive',
            'versao'         => '1.3',
            'ano_fabricacao' => 2020,
            'ano_modelo'     => 2021,
            'km'             => 45000,
            'cor'            => 'Prata',
            'categoria'      => 'hatch',
            'combustivel'    => 'flex',
            'transmissao'    => 'manual',
        ], $overrides);
    }

    private function salePayload(array $overrides = []): array
    {
        $vehicle  = Vehicle::factory()->disponivel()->create();
        $customer = Customer::factory()->create();

        return array_merge([
            'vehicle_id'     => $vehicle->id,
            'customer_id'    => $customer->id,
            'preco_venda'    => 20000,
            'tipo_pagamento' => 'misto',
            'data_venda'     => now()->format('Y-m-d'),
            'status'         => 'concluida',
        ], $overrides);
    }

    public function test_store_creates_sale_with_two_troca_vehicles_and_money()
    {
        $payload = $this->salePayload([
            'tipo_pagamento' => 'misto',
            'preco_venda'    => 20000,
            'trocas' => [
                $this->trocaPayload(['marca' => 'Fiat', 'modelo' => 'Argo', 'valor_troca' => 30000]),
                $this->trocaPayload(['marca' => 'Honda', 'modelo' => 'Civic', 'valor_troca' => 55000, 'transmissao' => 'cvt']),
            ],
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $sale = Sale::where('vehicle_id', $payload['vehicle_id'])->firstOrFail();

        $this->assertCount(2, $sale->trocaVehicles);
        $this->assertEquals(85000.0, $sale->valor_troca_total);

        // Os dois veículos entraram no estoque como disponíveis, com preço = valor avaliado
        // (busca pelos veículos ligados à venda: a factory do veículo vendido sorteia marcas/modelos reais)
        $fiat  = $sale->trocaVehicles->firstWhere('modelo', 'Argo');
        $civic = $sale->trocaVehicles->firstWhere('modelo', 'Civic');
        $this->assertNotNull($fiat);
        $this->assertNotNull($civic);
        $this->assertNotEquals($payload['vehicle_id'], $fiat->id);
        $this->assertNotEquals($payload['vehicle_id'], $civic->id);

        $this->assertDatabaseHas('vehicles', ['id' => $fiat->id,  'marca' => 'Fiat',  'status' => 'disponivel', 'preco' => 30000, 'preco_compra' => 30000, 'transmissao' => 'manual']);
        $this->assertDatabaseHas('vehicles', ['id' => $civic->id, 'marca' => 'Honda', 'status' => 'disponivel', 'preco' => 55000, 'preco_compra' => 55000, 'transmissao' => 'cvt']);

        $this->assertDatabaseHas('sale_troca_vehicles', ['sale_id' => $sale->id, 'vehicle_id' => $fiat->id,  'valor_troca' => 30000]);
        $this->assertDatabaseHas('sale_troca_vehicles', ['sale_id' => $sale->id, 'vehicle_id' => $civic->id, 'valor_troca' => 55000]);
        $this->assertEquals(3, Vehicle::count()); // vendido + 2 trocas

        // Veículo vendido ficou como vendido
        $this->assertDatabaseHas('vehicles', ['id' => $payload['vehicle_id'], 'status' => 'vendido']);
    }

    public function test_store_creates_sale_with_single_troca_vehicle()
    {
        $payload = $this->salePayload([
            'tipo_pagamento' => 'permuta',
            'trocas' => [$this->trocaPayload()],
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), $payload);
        $response->assertSessionHasNoErrors();

        $sale = Sale::where('vehicle_id', $payload['vehicle_id'])->firstOrFail();
        $this->assertCount(1, $sale->trocaVehicles);
        $this->assertEquals(30000.0, $sale->valor_troca_total);
    }

    public function test_store_ignores_blank_troca_blocks()
    {
        $payload = $this->salePayload([
            'tipo_pagamento' => 'permuta',
            'trocas' => [
                $this->trocaPayload(),
                // bloco padrão do formulário deixado em branco
                ['valor_troca' => '', 'marca' => '', 'modelo' => '', 'versao' => '', 'ano_fabricacao' => '', 'ano_modelo' => '', 'km' => '', 'cor' => '', 'categoria' => '', 'combustivel' => '', 'transmissao' => ''],
            ],
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), $payload);
        $response->assertSessionHasNoErrors();

        $sale = Sale::where('vehicle_id', $payload['vehicle_id'])->firstOrFail();
        $this->assertCount(1, $sale->trocaVehicles);
        $this->assertEquals(2, Vehicle::count()); // vendido + 1 troca
    }

    public function test_store_permuta_without_troca_is_still_allowed()
    {
        $payload = $this->salePayload(['tipo_pagamento' => 'permuta']);

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), $payload);
        $response->assertSessionHasNoErrors();

        $sale = Sale::where('vehicle_id', $payload['vehicle_id'])->firstOrFail();
        $this->assertCount(0, $sale->trocaVehicles);
    }

    public function test_store_validates_each_troca_vehicle()
    {
        $payload = $this->salePayload([
            'tipo_pagamento' => 'misto',
            'trocas' => [
                $this->trocaPayload(),
                ['marca' => 'Honda', 'ano_modelo' => 1900, 'combustivel' => 'querosene'],
            ],
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), $payload);

        $response->assertSessionHasErrors([
            'trocas.1.modelo', 'trocas.1.ano_fabricacao', 'trocas.1.ano_modelo', 'trocas.1.km',
            'trocas.1.cor', 'trocas.1.categoria', 'trocas.1.combustivel', 'trocas.1.transmissao',
        ]);
        $response->assertSessionDoesntHaveErrors(['trocas.0.marca', 'trocas.0.modelo']);

        // Nada foi persistido
        $this->assertEquals(0, Sale::count());
        $this->assertEquals(1, Vehicle::count());
    }

    public function test_store_ignores_trocas_when_payment_is_not_permuta_or_misto()
    {
        $payload = $this->salePayload([
            'tipo_pagamento' => 'a_vista',
            'trocas' => [$this->trocaPayload()],
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.sales.store'), $payload);
        $response->assertSessionHasNoErrors();

        $sale = Sale::where('vehicle_id', $payload['vehicle_id'])->firstOrFail();
        $this->assertCount(0, $sale->trocaVehicles);
        $this->assertEquals(1, Vehicle::count());
    }

    public function test_show_displays_all_troca_vehicles()
    {
        $sale = Sale::factory()->create(['tipo_pagamento' => 'misto']);
        $a = Vehicle::factory()->create(['marca' => 'Fiat', 'modelo' => 'Mobi']);
        $b = Vehicle::factory()->create(['marca' => 'Renault', 'modelo' => 'Kwid']);
        $sale->trocaVehicles()->attach([
            $a->id => ['valor_troca' => 25000],
            $b->id => ['valor_troca' => 35000],
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.sales.show', $sale));
        $response->assertStatus(200);
        $response->assertSee('Veículos de Troca (2)');
        $response->assertSee('Fiat Mobi');
        $response->assertSee('Renault Kwid');
        $response->assertSee('Total avaliado em troca');
        $response->assertSee('60.000');
    }

    public function test_vehicle_show_displays_sale_it_entered_as_troca()
    {
        $sale    = Sale::factory()->create(['tipo_pagamento' => 'permuta']);
        $vehicle = Vehicle::factory()->create();
        $sale->trocaVehicles()->attach($vehicle->id, ['valor_troca' => 42000]);

        $response = $this->actingAs($this->user)->get(route('admin.vehicles.show', $vehicle));
        $response->assertStatus(200);
        $response->assertSee('Veículo entrou como troca na');
        $response->assertSee('Venda #' . $sale->id);
        $response->assertSee('42.000');
    }

    public function test_destroy_sale_removes_troca_links_but_keeps_vehicles()
    {
        $sale    = Sale::factory()->create(['tipo_pagamento' => 'permuta']);
        $vehicle = Vehicle::factory()->create();
        $sale->trocaVehicles()->attach($vehicle->id, ['valor_troca' => 10000]);

        $this->actingAs($this->user)->delete(route('admin.sales.destroy', $sale))->assertRedirect();

        $this->assertDatabaseMissing('sale_troca_vehicles', ['sale_id' => $sale->id]);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_create_form_restores_troca_blocks_after_validation_error()
    {
        $payload = $this->salePayload([
            'tipo_pagamento' => 'misto',
            'trocas' => [
                $this->trocaPayload(['marca' => 'Fiat', 'modelo' => 'Argo']),
                ['marca' => 'Honda', 'modelo' => '', 'valor_troca' => '', 'km' => ''],
            ],
        ]);

        $this->actingAs($this->user)
            ->from(route('admin.sales.create'))
            ->post(route('admin.sales.store'), $payload)
            ->assertRedirect(route('admin.sales.create'))
            ->assertSessionHasErrors('trocas.1.modelo');

        $response = $this->actingAs($this->user)->get(route('admin.sales.create'));
        $response->assertStatus(200);

        // Os dois blocos voltam preenchidos, mais o template vazio para o botão "Adicionar"
        $response->assertSee('name="trocas[0][marca]"', false);
        $response->assertSee('value="Fiat"', false);
        $response->assertSee('name="trocas[1][marca]"', false);
        $response->assertSee('value="Honda"', false);
        $response->assertSee('trocas[__INDEX__][marca]', false);
        $response->assertSee('id="btnAddTroca"', false);
        $response->assertSee('Adicionar outro veículo de troca');
    }

    public function test_create_form_shows_one_empty_troca_block_by_default()
    {
        $response = $this->actingAs($this->user)->get(route('admin.sales.create'));
        $response->assertStatus(200);
        $response->assertSee('name="trocas[0][marca]"', false);
        $response->assertDontSee('name="trocas[1][marca]"', false);
    }
}
