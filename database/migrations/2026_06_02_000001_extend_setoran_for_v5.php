<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->text('area_rw')->nullable()->after('catatan_kondisi');
            $table->decimal('total_kg', 10, 2)->nullable()->after('area_rw');
            $table->decimal('total_kg_dipilah', 10, 2)->default(0)->after('total_kg');
            $table->decimal('total_kg_tidak_dipilah', 10, 2)->default(0)->after('total_kg_dipilah');
            $table->decimal('nilai', 12, 2)->default(0)->after('total_kg_tidak_dipilah'); // signed: + terima, − iuran
            $table->enum('mode', ['agregat', 'detail'])->default('detail')->after('nilai');
            $table->enum('sumber_input', ['manual', 'excel', 'ocr'])->default('manual')->after('mode');
            $table->boolean('is_selesai')->default(false)->after('sumber_input');
        });
    }

    public function down(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->dropColumn([
                'area_rw',
                'total_kg',
                'total_kg_dipilah',
                'total_kg_tidak_dipilah',
                'nilai',
                'mode',
                'sumber_input',
                'is_selesai',
            ]);
        });
    }
};
