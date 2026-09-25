<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Garante que a migration copia o veículo de troca único (colunas legadas
 * sales.troca_vehicle_id / valor_troca) para a nova tabela sale_troca_vehicles.
 */
class SaleTrocaVehiclesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_single_troca_is_copied_to_pivot_table()
    {
        $sale      = Sale::factory()->create(['tipo_pagamento' => 'permuta']);
        $trocaCar  = Vehicle::factory()->create();
        $semTroca  = Sale::factory()->create();

        DB::table('sales')->where('id', $sale->id)->update([
            'troca_vehicle_id' => $trocaCar->id,
            'valor_troca'      => 47500.00,
        ]);

        require_once database_path('migrations/2024_01_01_000016_create_sale_troca_vehicles_table.php');
        $migration = new \CreateSaleTrocaVehiclesTable();
        $migration->down();
        $migration->up();

        $this->assertDatabaseHas('sale_troca_vehicles', [
            'sale_id'     => $sale->id,
            'vehicle_id'  => $trocaCar->id,
            'valor_troca' => 47500.00,
        ]);
        $this->assertDatabaseMissing('sale_troca_vehicles', ['sale_id' => $semTroca->id]);

        $this->assertEquals($trocaCar->id, $sale->fresh()->trocaVehicles->first()->id);
        $this->assertEquals($sale->id, $trocaCar->fresh()->vendaOrigem->id);
    }
}
