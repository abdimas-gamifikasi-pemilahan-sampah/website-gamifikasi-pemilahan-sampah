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
        Schema::create('setoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga');
            $table->foreignId('petugas_id')->constrained('users');
            $table->dateTime('tanggal_setoran');
            $table->text('catatan_kondisi')->nullable();
            $table->decimal('total_nilai', 12, 2)->default(0);
            $table->enum('status_pembayaran', ['belum_dibayar', 'sudah_dibayar'])->default('belum_dibayar');
            $table->timestamps();

            $table->index(['warga_id', 'tanggal_setoran']);
            $table->index('tanggal_setoran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran');
    }
};
