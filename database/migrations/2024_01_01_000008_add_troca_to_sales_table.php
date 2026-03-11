<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrocaToSalesTable extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('troca_vehicle_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('vehicles')
                ->nullOnDelete();

            $table->decimal('valor_troca', 10, 2)
                ->nullable()
                ->after('troca_vehicle_id')
                ->comment('Valor avaliado do veículo de troca');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['troca_vehicle_id']);
            $table->dropColumn(['troca_vehicle_id', 'valor_troca']);
        });
    }
}
