<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_setoran', function (Blueprint $table) {
            $table->string('rw', 10)->nullable()->after('nama_penyetor');
            $table->string('rt', 10)->nullable()->after('rw');
        });
    }

    public function down(): void
    {
        Schema::table('item_setoran', function (Blueprint $table) {
            $table->dropColumn(['rw', 'rt']);
        });
    }
};
