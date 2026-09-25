<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove as colunas legadas sales.troca_vehicle_id / sales.valor_troca.
 *
 * Desde a migration 000016 os veiculos de troca vivem em sale_troca_vehicles
 * e o codigo nao le nem escreve mais estas colunas. Os dados ja foram copiados.
 *
 * SQLite (usado so nos testes): o Laravel 8 nao consegue remover uma coluna com
 * chave estrangeira sem recriar a tabela, entao aqui a remocao e pulada. Em
 * MySQL (producao) as colunas sao removidas de fato.
 */
class DropLegacyTrocaColumnsFromSalesTable extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (!Schema::hasColumn('sales', 'troca_vehicle_id')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['troca_vehicle_id']);
            $table->dropColumn(['troca_vehicle_id', 'valor_troca']);
        });
    }

    /**
     * Recria as colunas e volta o PRIMEIRO veiculo de troca de cada venda para
     * elas, para que a versao anterior do codigo funcione. Vendas com mais de
     * um veiculo de troca ficam so com o primeiro nas colunas antigas; a tabela
     * sale_troca_vehicles continua intacta com todos.
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'troca_vehicle_id')) {
            return;
        }

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

        $primeiros = DB::table('sale_troca_vehicles')
            ->orderBy('sale_id')
            ->orderBy('id')
            ->get(['sale_id', 'vehicle_id', 'valor_troca'])
            ->unique('sale_id');

        foreach ($primeiros as $troca) {
            DB::table('sales')->where('id', $troca->sale_id)->update([
                'troca_vehicle_id' => $troca->vehicle_id,
                'valor_troca'      => $troca->valor_troca,
            ]);
        }
    }
}
