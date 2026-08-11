<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aktivitas extends Model
{
    protected $fillable = ['user_id', 'kode', 'nama_aktivitas', 'menit'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
