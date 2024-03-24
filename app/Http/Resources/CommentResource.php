<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{

    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'text' => $this->text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'likes_count' => $this->likes->count(),
            'replies_count' => $this->replies->count(),
            'replies' => ReplyResource::collection($this->replies),
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'profile_picture' => $this->user->profile_picture,


        ];
    }
}
