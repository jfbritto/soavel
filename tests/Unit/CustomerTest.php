<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_endereco_completo_accessor()
    {
        $customer = Customer::factory()->create([
            'endereco' => 'Rua A',
            'numero' => '100',
            'bairro' => 'Centro',
            'cidade' => 'Vitória',
            'estado' => 'ES',
        ]);

        $endereco = $customer->enderecoCompleto;
        $this->assertStringContainsString('Rua A', $endereco);
        $this->assertStringContainsString('100', $endereco);
        $this->assertStringContainsString('Vitória', $endereco);
    }

    public function test_has_many_sales()
    {
        $customer = Customer::factory()->create();
        Sale::factory()->create(['customer_id' => $customer->id]);

        $this->assertCount(1, $customer->sales);
    }
}
