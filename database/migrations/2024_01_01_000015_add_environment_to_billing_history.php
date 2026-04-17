<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billing_history', function (Blueprint $table) {
            $table->string('environment')->default('production')->after('invoice_url');
        });

        \DB::table('billing_history')
            ->where('invoice_url', 'like', '%sandbox.asaas.com%')
            ->update(['environment' => 'sandbox']);
    }

    public function down(): void
    {
        Schema::table('billing_history', function (Blueprint $table) {
            $table->dropColumn('environment');
        });
    }
};
