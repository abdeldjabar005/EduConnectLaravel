<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'question',
        'options',
        'results',
    ];

    protected $casts = [
        'options' => 'array',
        'results' => 'array',
    ];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
