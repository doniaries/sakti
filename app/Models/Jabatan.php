<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Jabatan extends Model
{
    protected $fillable = ['nama', 'slug'];

    protected static function booted(): void
    {
        static::saving(function (Jabatan $jabatan) {
            if (empty($jabatan->slug) && filled($jabatan->nama)) {
                $jabatan->slug = Str::slug($jabatan->nama);
            }
        });
    }
}
