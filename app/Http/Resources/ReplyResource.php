<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
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
            'comment_id' => $this->comment_id,
            'user_id' => $this->user_id,
            'text' => $this->text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'likes_count' => $this->likes->count(),
            'isLiked' => $this->likes->contains('user_id', auth()->id()),
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'profile_picture' => $this->user->profile_picture,

        ];
    }
}
