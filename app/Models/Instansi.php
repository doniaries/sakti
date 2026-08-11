<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $fillable = ['nama_dinas', 'nama_kepala_dinas', 'alamat', 'logo', 'kepala_id'];

    public function kepala()
    {
        return $this->belongsTo(User::class, 'kepala_id');
    }
}
