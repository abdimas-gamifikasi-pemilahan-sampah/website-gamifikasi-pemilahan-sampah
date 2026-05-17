<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshot_peringkat_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->date('bulan'); // stored as first day of month
            $table->decimal('total_kg', 10, 2)->default(0);
            $table->decimal('kg_dipilah', 10, 2)->default(0);
            $table->decimal('persen_dipilah', 5, 2)->default(0);
            $table->unsignedInteger('bulan_berturut')->default(0);
            $table->decimal('poin', 10, 2)->default(0);
            $table->unsignedInteger('peringkat')->nullable();
            $table->unsignedInteger('peringkat_rw')->nullable();
            $table->timestamps();

            $table->unique(['warga_id', 'bulan']);
            $table->index(['bulan', 'poin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshot_peringkat_bulanan');
    }
};
