<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Extend the jenis enum to include setoran import types.
        // ImportSetoranController logs with jenis = 'setoran_{format}'.
        DB::statement("
            ALTER TABLE import_log
                MODIFY jenis ENUM('warga', 'setoran_perolehan', 'setoran_rivan', 'setoran_detail')
                NOT NULL DEFAULT 'warga'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Revert to warga-only — only safe if no setoran import logs exist.
        DB::statement("
            ALTER TABLE import_log
                MODIFY jenis ENUM('warga')
                NOT NULL DEFAULT 'warga'
        ");
    }
};
