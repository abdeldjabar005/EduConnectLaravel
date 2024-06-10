<?php

namespace App\Http\Resources;

use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userVote = Vote::where('user_id', $request->user()->id)
            ->where('poll_id', $this->id)
            ->first();
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'question' => $this->question,
            'options' => $this->options,
            'results' => $this->results,
            'user_vote' => $userVote ? $userVote->option : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
