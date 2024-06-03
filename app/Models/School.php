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
        'code',
        'verified',
        'verification_request_sent'
    ];
    protected $casts = [
        'id' => 'integer',
        'verified' => 'boolean',
        'verification_request_sent' => 'boolean',
    ];
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'school_user', 'school_id', 'user_id');
    }
    public function teachers()
    {
        return $this->hasMany(User::class, 'school_id')->where('role', 'teacher');
    }

    public function students()
{
    return $this->belongsToMany(Student::class, 'school_student');
}
    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'school_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function inviteCodes()
    {
        return $this->hasMany(SchoolInviteCode::class);
    }

}
