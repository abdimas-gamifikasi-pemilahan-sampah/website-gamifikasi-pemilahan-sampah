<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnapshotPeringkatBulanan extends Model
{
    protected $table = 'snapshot_peringkat_bulanan';

    protected $fillable = [
        'warga_id',
        'bulan',
        'total_kg',
        'kg_dipilah',
        'persen_dipilah',
        'bulan_berturut',
        'poin',
        'peringkat',
        'peringkat_rw',
    ];

    protected $casts = [
        'bulan' => 'date',
        'total_kg' => 'decimal:2',
        'kg_dipilah' => 'decimal:2',
        'persen_dipilah' => 'decimal:2',
        'poin' => 'decimal:2',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }
}
