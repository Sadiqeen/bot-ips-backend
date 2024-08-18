<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adjust extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'imsak',
        'fajr',
        'sunrise',
        'dhuhr',
        'asr',
        'maghrib',
        'sunset',
        'isha',
        'midnight',
        'method'
    ];
}
