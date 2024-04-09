<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'school_id' => $this->school_id,
            'teacher_id' => $this->teacher_id,
            'grade' => $this->grade_level,
            'subject' => $this->subject,
            'code' => $this->code,
            'teacher_first_name' => $this->teacher->first_name,
            'teacher_last_name' => $this->teacher->last_name,
            'members_count' => $this->users->count(),

        ];
    }
}
