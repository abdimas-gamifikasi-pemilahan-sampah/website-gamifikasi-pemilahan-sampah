<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $table = 'import_log';

    protected $fillable = [
        'filename',
        'jenis',
        'total_baris',
        'berhasil',
        'gagal',
        'status',
        'diupload_oleh_user_id',
        'catatan',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'diupload_oleh_user_id');
    }
}
