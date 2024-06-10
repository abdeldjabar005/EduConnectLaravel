<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Contact extends JsonResource
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
            'profile_picture' => $this->profile_picture,
             'active_status' => $this->active_status,
            'last_message' => $this->last_message,
            'last_message_updated_at' => $this->last_message_updated_at,

        ];

        return $data;
    }
}
