<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacebookLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'comment_id',
        'page_id',
        'message'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    protected function message(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value ? gzuncompress(base64_decode($value)) : "",
            set: fn (string $value) => $value ? base64_encode(gzcompress($value, 9)) : "",
        );
    }
}
