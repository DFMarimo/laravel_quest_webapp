<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "slug" => $this->slug,
            "best_answer_id" => $this->best_answer_id,
            "channel_id" => $this->whenLoaded('channel'),
            "title" => $this->title,
            "body" => $this->body,
            "status" => $this->status,
            "is_active" => $this->is_active,
            "deleted_at" => $this->deleted_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "author" => $this->whenLoaded('author', function ($user){
                return $user;
            }, $this->author_id),
            "tags" => $this->whenLoaded('tags'),
            "answers" => $this->whenLoaded('answers')
        ];
    }
}
