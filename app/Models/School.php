<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'image',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function teachers()
    {
        return $this->hasMany(User::class, 'school_id')->where('role', 'teacher');
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'school_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }


}
