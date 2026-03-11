<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 200);
            $table->decimal('valor', 10, 2);
            $table->enum('categoria', ['manutencao', 'documentacao', 'limpeza', 'combustivel', 'comissao', 'outros'])->default('outros');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->date('data');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
