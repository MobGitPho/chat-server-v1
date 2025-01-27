<?php

namespace App\Http\Resources;

use App\Enums\MessageStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'sender' => $this->whenLoaded('sender', function () {
                return new UserResource($this->sender);
            }),
            'receiver' => $this->whenLoaded('receiver', function () {
                return new UserResource($this->receiver);
            }),
            'group' => $this->whenLoaded('group', function () {
                return new GroupResource($this->group);
            }),
            'content' => $this->content,
            'attachment' => $this->attachment,
            'status' => $this->status,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
