<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'url',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
