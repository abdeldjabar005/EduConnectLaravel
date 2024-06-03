<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
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
            'address' => $this->address,
            'image' => $this->image,
            'is_verified' => $this->verified,
            'verification_request_sent' => $this->verification_request_sent,
            'admin_id' => $this->admin_id,
            'admin_first_name' => $this->admin->first_name,
            'admin_last_name' => $this->admin->last_name,
            'members_count' => $this->users->count(),
        ];
    }
}
