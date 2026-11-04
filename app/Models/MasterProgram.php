<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterProgram extends Model
{
    use HasFactory;

    protected $table = 'master_programs';

    protected $fillable = [
        'tahun_anggaran',
        'kode_program',
        'nama_program',
        'keterangan',
    ];

    protected $casts = [
        'tahun_anggaran' => 'integer',
    ];

    public function kegiatans(): HasMany
    {
        return $this->hasMany(MasterKegiatan::class, 'program_id');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode_program} - {$this->nama_program}";
    }
}

