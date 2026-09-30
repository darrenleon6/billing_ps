<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Promo
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Contoh: "BONUS1JAM", "DISCOUNT5K"
            $table->string('name');           // Contoh: "Promo Main 2 Jam Gratis 1 Jam"
            $table->enum('type', ['discount_nominal', 'discount_percent', 'bonus_time']); 
            
            // Nilai promo
            $table->decimal('discount_value', 12, 2)->default(0); // Rp atau %
            $table->integer('bonus_minutes')->default(0);        // Menit gratis (misal: 60)

            // Syarat & Ketentuan
            $table->integer('min_duration_minutes')->default(0);          // Syarat min. durasi (menit)
            $table->decimal('min_transaction_amount', 12, 2)->default(0); // Syarat min. total tagihan (Rp)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tambahkan kolom relasi promo di tabel rental_sessions
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0)->after('total_price');
            $table->integer('bonus_minutes_applied')->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('rental_sessions', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['promotion_id', 'discount_amount', 'bonus_minutes_applied']);
        });
        Schema::dropIfExists('promotions');
    }
};