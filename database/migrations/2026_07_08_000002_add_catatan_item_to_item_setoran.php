<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_setoran', function (Blueprint $table) {
            // Stores OCR import notes, e.g. "jenis: plastik hitam [flat rate]"
            $table->string('catatan_item')->nullable()->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('item_setoran', function (Blueprint $table) {
            $table->dropColumn('catatan_item');
        });
    }
};
