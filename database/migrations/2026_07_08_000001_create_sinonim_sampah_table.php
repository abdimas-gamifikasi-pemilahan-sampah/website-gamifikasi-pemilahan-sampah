<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinonim_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('kata_kunci')->unique(); // lowercase, e.g. "karton", "plastik hitam"
            $table->foreignId('tarif_item_id')
                  ->nullable()
                  ->constrained('tarif_items')
                  ->nullOnDelete();
            $table->enum('tipe_sampah', ['dipilah', 'tidak_dipilah'])->nullable();
            $table->boolean('gunakan_flat')->default(true);
            $table->foreignId('dibuat_oleh_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinonim_sampah');
    }
};
