<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclePartnersTable extends Migration
{
    public function up()
    {
        Schema::create('vehicle_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->decimal('percentual', 5, 2);
            $table->timestamps();

            $table->unique(['vehicle_id', 'partner_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_partners');
    }
}
