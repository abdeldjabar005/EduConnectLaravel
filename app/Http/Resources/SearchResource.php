<?php

namespace App\Http\Resources;

use App\Models\SchoolJoinRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $user = auth()->user();

        $isMember = 0;
        if ($this->users()->where('users.id', $user->id)->exists()) {
            $isMember = 1;
        } else {
            $joinRequest = SchoolJoinRequest::where('user_id', $user->id)
                ->where('school_id', $this->id)
                ->first();
            if ($joinRequest) {
                $isMember = 2;
            }
        }

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
            'is_member' => $isMember,
        ];
    }

}
