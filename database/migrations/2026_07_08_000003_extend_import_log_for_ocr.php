<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_log', function (Blueprint $table) {
            $table->integer('jumlah_warga_matched')->default(0)->after('total_baris');
            $table->integer('jumlah_warga_baru')->default(0)->after('jumlah_warga_matched');
            $table->integer('jumlah_item_flat_rate')->default(0)->after('jumlah_warga_baru');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE import_log
                MODIFY jenis ENUM('warga','setoran_perolehan','setoran_rivan','setoran_detail','setoran_ocr')
                NOT NULL DEFAULT 'warga'
            ");
        }
    }

    public function down(): void
    {
        Schema::table('import_log', function (Blueprint $table) {
            $table->dropColumn(['jumlah_warga_matched', 'jumlah_warga_baru', 'jumlah_item_flat_rate']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE import_log
                MODIFY jenis ENUM('warga','setoran_perolehan','setoran_rivan','setoran_detail')
                NOT NULL DEFAULT 'warga'
            ");
        }
    }
};
