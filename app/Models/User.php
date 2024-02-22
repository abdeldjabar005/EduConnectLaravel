<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens,HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'is_verified',
        'role',
        'password',
        'profile_picture',
        'bio',
        'contact_information',
        'school_id',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function students()
    {

            return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id');
        }

    public function schoolClasses()
    {
        if ($this->role == 'teacher') {
            return $this->hasMany(SchoolClass::class, 'teacher_id');
        }
        return null;
    }

    public function school()
    {
        if ($this->role == 'admin') {
            return $this->hasOne(School::class, 'admin_id');
        }
        return $this->belongsTo(School::class);
    }
}
