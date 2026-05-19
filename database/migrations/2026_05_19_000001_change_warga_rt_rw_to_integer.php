<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE warga MODIFY rt SMALLINT UNSIGNED NOT NULL, MODIFY rw SMALLINT UNSIGNED NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE warga MODIFY rt VARCHAR(5) NOT NULL, MODIFY rw VARCHAR(5) NOT NULL");
        }
    }
};
