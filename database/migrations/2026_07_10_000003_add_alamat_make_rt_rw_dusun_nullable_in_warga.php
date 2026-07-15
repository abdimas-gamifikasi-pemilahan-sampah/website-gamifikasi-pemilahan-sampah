<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            $table->text('alamat')->nullable()->after('no_kk');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE warga
                MODIFY rt SMALLINT UNSIGNED NULL,
                MODIFY rw SMALLINT UNSIGNED NULL,
                MODIFY dusun VARCHAR(255) NULL
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE warga
                MODIFY rt SMALLINT UNSIGNED NOT NULL,
                MODIFY rw SMALLINT UNSIGNED NOT NULL,
                MODIFY dusun VARCHAR(255) NOT NULL
            ");
        }

        Schema::table('warga', function (Blueprint $table) {
            $table->dropColumn('alamat');
        });
    }
};
