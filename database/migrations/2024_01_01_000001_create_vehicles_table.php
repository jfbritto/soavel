<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('marca', 60);
            $table->string('modelo', 80);
            $table->string('versao', 100)->nullable();
            $table->smallInteger('ano_fabricacao')->unsigned();
            $table->smallInteger('ano_modelo')->unsigned();
            $table->integer('km')->unsigned();
            $table->decimal('preco', 10, 2);
            $table->decimal('preco_compra', 10, 2)->nullable();
            $table->string('cor', 40);
            $table->enum('combustivel', ['gasolina', 'etanol', 'flex', 'diesel', 'gnv', 'hibrido', 'eletrico'])->default('flex');
            $table->enum('transmissao', ['manual', 'automatico', 'automatizado', 'cvt'])->default('manual');
            $table->tinyInteger('portas')->unsigned()->default(4);
            $table->string('motorizacao', 20)->nullable();
            $table->enum('categoria', ['hatch', 'sedan', 'suv', 'pickup', 'van', 'esportivo', 'outro'])->default('hatch');
            $table->enum('status', ['disponivel', 'reservado', 'vendido'])->default('disponivel');
            $table->text('descricao')->nullable();
            $table->boolean('destaque')->default(false);
            $table->string('slug', 120)->unique();
            $table->string('placa', 10)->nullable();
            $table->string('renavam', 20)->nullable();
            $table->string('chassi', 25)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
