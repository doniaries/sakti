<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterBank extends Model
{
    use HasFactory;

    protected $table = 'master_banks';

    protected $fillable = [
        'kode_bank',
        'nama_bank',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tokos(): HasMany
    {
        return $this->hasMany(MasterToko::class, 'master_bank_id');
    }

    public function getLabelAttribute(): string
    {
        return $this->kode_bank
            ? "{$this->nama_bank} ({$this->kode_bank})"
            : $this->nama_bank;
    }
}
