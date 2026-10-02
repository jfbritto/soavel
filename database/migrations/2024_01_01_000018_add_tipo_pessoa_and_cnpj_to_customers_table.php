<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cliente pessoa juridica: tipo_pessoa (pf|pj) e cnpj.
 *
 * Todos os clientes existentes viram 'pf' (default), sem tocar no cpf.
 * O cnpj e unico como o cpf, e nullable (o cadastro de PF nunca exigiu CPF).
 */
class AddTipoPessoaAndCnpjToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('tipo_pessoa', 2)->default('pf')->after('nome')->comment('pf = pessoa física, pj = pessoa jurídica');
            $table->string('cnpj', 18)->nullable()->unique()->after('cpf');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['cnpj']);
            $table->dropColumn(['tipo_pessoa', 'cnpj']);
        });
    }
}
