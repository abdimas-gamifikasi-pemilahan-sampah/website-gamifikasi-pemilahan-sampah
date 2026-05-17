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

        // Allow tarif_item_id, riwayat_tarif_id, tipe_sampah, harga_per_kg_saat_itu
        // to be NULL so "tidak_dipilah" items can be stored without a category or price.
        DB::statement("
            ALTER TABLE item_setoran
                MODIFY tarif_item_id         BIGINT UNSIGNED NULL,
                MODIFY riwayat_tarif_id      BIGINT UNSIGNED NULL,
                MODIFY tipe_sampah           ENUM('organik','anorganik') NULL,
                MODIFY harga_per_kg_saat_itu DECIMAL(10,2) NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE item_setoran
                MODIFY tarif_item_id         BIGINT UNSIGNED NOT NULL,
                MODIFY riwayat_tarif_id      BIGINT UNSIGNED NOT NULL,
                MODIFY tipe_sampah           ENUM('organik','anorganik') NOT NULL,
                MODIFY harga_per_kg_saat_itu DECIMAL(10,2) NOT NULL
        ");
    }
};
