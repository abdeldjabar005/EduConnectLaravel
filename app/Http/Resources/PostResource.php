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
            'classname' => $this->class_id ? $this->class->name : $this->school->name,
            'text' => $this->text,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
//            'comments' => CommentResource::collection($this->comments),
//            'likes' => LikeResource::collection($this->likes),
            'comments_count' => $this->comments->count(),
            'likes_count' => $this->likes->count(),
            'isLiked' => $this->likes->contains('user_id', auth()->id()),
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'profile_picture' => $this->user->profile_picture,
            'isSaved' => $this->savedByUsers->contains(auth()->user()),
        ];

        switch ($this->type) {
            case 'video':
                $data['video'] = VideoResource::collection($this->videos);
                break;
            case 'picture':
                $data['pictures'] = PictureResource::collection($this->pictures);
                break;
            case 'poll':
                $data['poll'] = new PollResource($this->poll);
                break;
            case 'attachment':
                $data['attachment'] =  AttachmentResource::collection($this->attachments);
                break;
        }

        return $data;
    }
}
