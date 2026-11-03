<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuans';

    protected $fillable = ['nama'];

    public function masterBarangs(): HasMany
    {
        return $this->hasMany(MasterBarang::class, 'satuan_id');
    }
}

