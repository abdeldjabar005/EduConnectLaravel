<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model

{
    use HasFactory;
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'grade_level',
        'subject',
        'teacher_id',
        'school_id',

        'image'
    ];
    protected $casts = [
        'id' => 'integer',
        'school_id' => 'integer',
    ];
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_student', 'class_id', 'student_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id');
    }
    public function joinRequests()
    {
        return $this->hasMany(JoinRequest::class, 'class_id');
    }
    public function inviteCodes()
    {
        return $this->hasMany(ClassInviteCode::class, 'class_id');
    }
}
