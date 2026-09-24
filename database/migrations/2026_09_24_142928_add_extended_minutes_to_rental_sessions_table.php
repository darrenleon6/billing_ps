<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->integer('extended_minutes')->default(0)->after('start_time');
        });
    }

    public function down(): void
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->dropColumn('extended_minutes');
        });
    }
};