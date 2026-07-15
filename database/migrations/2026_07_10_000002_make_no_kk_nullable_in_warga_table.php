<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('warga', function (Blueprint $table) {
                $table->string('no_kk', 16)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('warga', function (Blueprint $table) {
                $table->string('no_kk', 16)->nullable(false)->change();
            });
        }
    }
};
