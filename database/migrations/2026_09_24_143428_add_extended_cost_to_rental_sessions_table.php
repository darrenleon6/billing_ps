<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            // Menambahkan kolom extended_cost dengan default 0
            $table->decimal('extended_cost', 12, 2)->default(0)->after('extended_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->dropColumn('extended_cost');
        });
    }
};