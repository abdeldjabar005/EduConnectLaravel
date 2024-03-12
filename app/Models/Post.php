<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'text',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
    public function poll()
    {
        return $this->hasOne(Poll::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

}
