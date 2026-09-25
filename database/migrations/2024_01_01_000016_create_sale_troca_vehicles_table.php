<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Uma venda pode receber vários veículos de troca (ex.: dois carros + dinheiro).
 *
 * Substitui as colunas sales.troca_vehicle_id / sales.valor_troca (um único veículo).
 * As colunas antigas são mantidas por enquanto (sem doctrine/dbal não dá para
 * removê-las com segurança no SQLite dos testes); o código não as lê nem escreve mais.
 */
class CreateSaleTrocaVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('sale_troca_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->decimal('valor_troca', 10, 2)->nullable()->comment('Valor avaliado do veículo na troca');
            $table->timestamps();

            $table->unique(['sale_id', 'vehicle_id']);
        });

        // Migra o veículo de troca único das vendas já existentes
        $legacy = DB::table('sales')
            ->whereNotNull('troca_vehicle_id')
            ->get(['id', 'troca_vehicle_id', 'valor_troca']);

        $now = now();

        foreach ($legacy as $sale) {
            DB::table('sale_troca_vehicles')->insert([
                'sale_id'     => $sale->id,
                'vehicle_id'  => $sale->troca_vehicle_id,
                'valor_troca' => $sale->valor_troca,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('sale_troca_vehicles');
    }
}
