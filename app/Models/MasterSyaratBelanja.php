<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSyaratBelanja extends Model
{
    use HasFactory;

    protected $table = 'master_syarat_belanja';

    protected $fillable = [
        'jenis_belanja',
        'metode_pengadaan',
        'nama_syarat',
        'keterangan',
        'is_required',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];
}
