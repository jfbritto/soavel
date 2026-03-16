<?php

namespace Tests\Unit;

use App\Models\Expense;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_categoria_label()
    {
        $expense = Expense::factory()->create(['categoria' => 'manutencao']);
        $this->assertEquals('Manutenção', $expense->categoriaLabel);

        $expense->categoria = 'documentacao';
        $this->assertEquals('Documentação', $expense->categoriaLabel);

        $expense->categoria = 'comissao';
        $this->assertEquals('Comissão', $expense->categoriaLabel);
    }

    public function test_valor_formatado()
    {
        $expense = Expense::factory()->create(['valor' => 1500.50]);
        $this->assertStringContainsString('1.500,50', $expense->valorFormatado);
    }

    public function test_belongs_to_vehicle_with_default()
    {
        $expense = Expense::factory()->create(['vehicle_id' => null]);
        // withDefault returns a Vehicle with titulo accessor '—'
        $this->assertNotNull($expense->vehicle);
    }

    public function test_belongs_to_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        $expense = Expense::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertEquals($vehicle->id, $expense->vehicle->id);
    }
}
