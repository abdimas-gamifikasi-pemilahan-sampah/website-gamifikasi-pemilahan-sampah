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

        // Allow NULL so aggregate-mode setoran can exist without a registered warga.
        // MySQL keeps the FK constraint; NULL values are always permitted by FK semantics.
        DB::statement('ALTER TABLE setoran MODIFY warga_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE setoran MODIFY warga_id BIGINT UNSIGNED NOT NULL');
    }
};
