<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->text('notes')->nullable(); // Kolom catatan kasir sementara
        });
    }

    public function down()
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
