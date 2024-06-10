<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolVerificationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'email',
        'phone_number',
        'document_path',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
