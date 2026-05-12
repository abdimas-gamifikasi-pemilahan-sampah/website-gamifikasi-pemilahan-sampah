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
        Schema::create('riwayat_tarif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarif_item_id')->constrained('tarif_items');
            $table->decimal('harga_per_kg', 10, 2);
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir')->nullable();
            $table->text('alasan_perubahan')->nullable();
            $table->foreignId('diubah_oleh_user_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tarif_item_id', 'tanggal_mulai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_tarif');
    }
};
