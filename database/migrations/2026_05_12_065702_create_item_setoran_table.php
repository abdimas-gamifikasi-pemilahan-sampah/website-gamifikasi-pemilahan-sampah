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
        Schema::create('item_setoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setoran_id')->constrained('setoran')->cascadeOnDelete();
            $table->foreignId('tarif_item_id')->constrained('tarif_items');
            $table->foreignId('riwayat_tarif_id')->constrained('riwayat_tarif');
            $table->enum('tipe_sampah', ['organik', 'anorganik', 'b3']);
            $table->enum('status_pemilahan', ['dipilah', 'tidak_dipilah']);
            $table->decimal('berat_kg', 8, 2);
            $table->decimal('harga_per_kg_saat_itu', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('setoran_id');
            $table->index(['status_pemilahan', 'tipe_sampah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_setoran');
    }
};
