<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatans';

    protected $fillable = [
        'id',
        'nama_kecamatan',
    ];

    public function nagaris(): HasMany
    {
        return $this->hasMany(Nagari::class, 'kecamatan_id');
    }
}
