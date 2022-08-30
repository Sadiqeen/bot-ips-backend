<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'page_id',
        'token',
    ];

    public function district()
    {
        return $this->hasMany(District::class);
    }

    public function districtDesc()
    {
        return $this->hasMany(District::class)->orderBy('id', 'DESC');
    }

    public function facebookLog()
    {
        return $this->hasMany(FacebookLog::class);
    }
}
