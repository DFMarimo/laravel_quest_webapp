<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => $this->name,
            "slug" => $this->slug,
            "uuid" => $this->uuid,
            "is_active" => $this->is_active,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at,
            "quests" => $this->whenLoaded('quests', function ($quest) {
                return $quest;
            }, []),
        ];
    }
}
