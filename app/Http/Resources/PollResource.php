<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'question' => $this->question,
            'options' => $this->options,
            'results' => $this->results,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
