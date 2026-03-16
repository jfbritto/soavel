<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tipo_pagamento_label()
    {
        $sale = Sale::factory()->create(['tipo_pagamento' => 'a_vista']);
        $this->assertEquals('À Vista', $sale->tipoPagamentoLabel);

        $sale->tipo_pagamento = 'financiado';
        $this->assertEquals('Financiado', $sale->tipoPagamentoLabel);
    }

    public function test_status_label()
    {
        $sale = Sale::factory()->create(['status' => 'concluida']);
        $this->assertEquals('Concluída', $sale->statusLabel);

        $sale->status = 'pendente';
        $this->assertEquals('Pendente', $sale->statusLabel);

        $sale->status = 'cancelada';
        $this->assertEquals('Cancelada', $sale->statusLabel);
    }

    public function test_status_color()
    {
        $sale = Sale::factory()->create(['status' => 'concluida']);
        $this->assertEquals('success', $sale->statusColor);

        $sale->status = 'pendente';
        $this->assertEquals('warning', $sale->statusColor);

        $sale->status = 'cancelada';
        $this->assertEquals('danger', $sale->statusColor);
    }

    public function test_belongs_to_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        $sale = Sale::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertEquals($vehicle->id, $sale->vehicle->id);
    }

    public function test_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $sale = Sale::factory()->create(['customer_id' => $customer->id]);

        $this->assertEquals($customer->id, $sale->customer->id);
    }

    public function test_belongs_to_user()
    {
        $user = User::factory()->create();
        $sale = Sale::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $sale->user->id);
    }

    public function test_preco_venda_formatado()
    {
        $sale = Sale::factory()->create(['preco_venda' => 75000.00]);
        $this->assertStringContainsString('75', $sale->precoVendaFormatado);
    }
}
