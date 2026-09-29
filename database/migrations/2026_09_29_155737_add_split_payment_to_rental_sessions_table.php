<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->decimal('cash_amount', 12, 2)->default(0)->after('payment_method');
            $table->decimal('qris_amount', 12, 2)->default(0)->after('cash_amount');
        });
    }

    public function down(): void
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->dropColumn(['cash_amount', 'qris_amount']);
        });
    }
};
