<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('billing_history', function (Blueprint $table) {
            $table->id();
            $table->string('asaas_payment_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('status'); // pending, confirmed, received, overdue, refunded
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('billing_type')->nullable(); // BOLETO, PIX, CREDIT_CARD
            $table->string('invoice_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_history');
    }
}
