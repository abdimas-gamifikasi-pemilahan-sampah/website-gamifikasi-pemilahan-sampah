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
        Schema::create('warga', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_kk', 16)->unique();
            $table->string('rt', 5);
            $table->string('rw', 5);
            $table->string('dusun');
            $table->string('no_hp', 20)->nullable();
            $table->date('tanggal_terdaftar');
            $table->enum('status_keanggotaan', ['aktif', 'non_aktif'])->default('aktif');
            $table->timestamps();

            $table->index(['rw', 'dusun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warga');
    }
};
