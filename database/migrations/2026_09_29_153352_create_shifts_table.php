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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->decimal('starting_cash', 12, 2); // Modal kas awal
            $table->decimal('expected_cash', 12, 2)->default(0); // Kas awal + Transaksi Cash
            $table->decimal('actual_cash', 12, 2)->nullable(); // Input fisik dari kasir
            $table->decimal('difference_cash', 12, 2)->nullable(); // Selisih
            $table->decimal('total_qris', 12, 2)->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Tambahkan shift_id pada rental_sessions
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
