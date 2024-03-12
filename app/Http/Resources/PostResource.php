<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'class_or_school_id' => $this->class_id ? $this->class_id : $this->school_id,
            'text' => $this->text,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'likes' => LikeResource::collection($this->whenLoaded('likes')),
            'comments_count' => $this->comments->count(),
            'likes_count' => $this->likes->count(),
            'user' => new UserResource($this->whenLoaded('user')),
        ];

        switch ($this->type) {
            case 'video':
                $data['video'] = new VideoResource($this->whenLoaded('video'));
                break;
            case 'picture':
                $data['pictures'] = PictureResource::collection($this->whenLoaded('pictures'));
                break;
            case 'poll':
                $data['poll'] = new PollResource($this->whenLoaded('poll'));
                break;
            case 'attachment':
                $data['attachment'] = new AttachmentResource($this->whenLoaded('attachment'));
                break;
        }

        return $data;
    }
}
