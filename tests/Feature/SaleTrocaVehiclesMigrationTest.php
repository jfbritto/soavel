<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Garante que a migration 000016 copia o veículo de troca único (colunas legadas
 * sales.troca_vehicle_id / valor_troca) para a nova tabela sale_troca_vehicles,
 * e que o down() da 000017 recria as colunas com o primeiro carro de cada venda.
 *
 * Sem RefreshDatabase nem DatabaseMigrations: estes testes executam DDL. Em MySQL
 * o DDL faz commit implícito e quebra a transação do RefreshDatabase; em SQLite o
 * migrate:rollback do DatabaseMigrations esbarra em down() antigos com dropColumn,
 * que exigem doctrine/dbal. Um migrate:fresh antes e depois resolve nos dois.
 */
class SaleTrocaVehiclesMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh')->run();
    }

    protected function tearDown(): void
    {
        // Deixa o banco limpo e migrado para as classes seguintes (SQLite em memória é compartilhado)
        $this->artisan('migrate:fresh')->run();
        parent::tearDown();
    }

    private function migration16(): \CreateSaleTrocaVehiclesTable
    {
        require_once database_path('migrations/2024_01_01_000016_create_sale_troca_vehicles_table.php');
        return new \CreateSaleTrocaVehiclesTable();
    }

    private function migration17(): \DropLegacyTrocaColumnsFromSalesTable
    {
        require_once database_path('migrations/2024_01_01_000017_drop_legacy_troca_columns_from_sales_table.php');
        return new \DropLegacyTrocaColumnsFromSalesTable();
    }

    public function test_legacy_single_troca_is_copied_to_pivot_table()
    {
        // Em MySQL a 000017 já removeu as colunas legadas; recria para simular o banco antigo.
        // Em SQLite (testes) a 000017 não faz nada e as colunas ainda existem.
        $this->migration17()->down();
        $this->assertTrue(Schema::hasColumn('sales', 'troca_vehicle_id'));

        $sale      = Sale::factory()->create(['tipo_pagamento' => 'permuta']);
        $trocaCar  = Vehicle::factory()->create();
        $semTroca  = Sale::factory()->create();

        DB::table('sales')->where('id', $sale->id)->update([
            'troca_vehicle_id' => $trocaCar->id,
            'valor_troca'      => 47500.00,
        ]);

        $migration = $this->migration16();
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

        $this->migration17()->up();
    }

    public function test_dropping_legacy_columns_can_be_rolled_back_with_first_troca_restored()
    {
        // Só faz sentido onde a 000017 remove as colunas de verdade
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('SQLite não remove as colunas legadas (Laravel 8 sem recriar a tabela).');
        }

        $sale = Sale::factory()->create(['tipo_pagamento' => 'misto']);
        $a = Vehicle::factory()->create();
        $b = Vehicle::factory()->create();
        $sale->trocaVehicles()->attach($a->id, ['valor_troca' => 30000]);
        $sale->trocaVehicles()->attach($b->id, ['valor_troca' => 55000]);

        $this->assertFalse(Schema::hasColumn('sales', 'troca_vehicle_id'));

        $this->migration17()->down();

        $this->assertTrue(Schema::hasColumn('sales', 'troca_vehicle_id'));
        $this->assertTrue(Schema::hasColumn('sales', 'valor_troca'));
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'troca_vehicle_id' => $a->id, 'valor_troca' => 30000]);
        $this->assertEquals(2, $sale->fresh()->trocaVehicles->count(), 'a tabela nova continua intacta');

        $this->migration17()->up();
        $this->assertFalse(Schema::hasColumn('sales', 'troca_vehicle_id'));
    }
}
