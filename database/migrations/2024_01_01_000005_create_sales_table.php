<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('preco_venda', 10, 2);
            $table->enum('tipo_pagamento', ['a_vista', 'financiado', 'consorcio', 'permuta', 'misto']);
            $table->string('financiadora', 80)->nullable();
            $table->tinyInteger('parcelas')->unsigned()->nullable();
            $table->decimal('entrada', 10, 2)->nullable();
            $table->date('data_venda');
            $table->enum('status', ['pendente', 'concluida', 'cancelada'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
