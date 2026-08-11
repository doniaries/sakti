<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $fillable = ['nama', 'slug', 'kepala_id'];

    public function kepala()
    {
        return $this->belongsTo(User::class, 'kepala_id');
    }
}
