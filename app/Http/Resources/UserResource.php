<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
  public function toArray(Request $request): array
{
    $data = [
        'id' => $this->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'email' => $this->email,
        'is_verified' => $this->is_verified,
        'role' => $this->role,
        'profile_picture' => $this->profile_picture,
        'bio' => $this->bio,
        'contact_information' => $this->contact_information,
        'schools' => SchoolResource::collection($this->schools) ,
        "classes" => SchoolClassResource::collection($this->classes),
    ];

    if ($this->role == 'admin' || $this->role == 'teacher') {
        $data["owned_classes"] = SchoolClassResource::collection($this->class);
    }
    if ($this->role == 'admin') {
        $data["owned_school"] = new SchoolResource($this->school);
    }

    return $data;
}
}
