<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
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
            'teacher' => $this->whenLoaded('teacher', fn() => new UserResource($this->teacher)),
            'student' => $this->whenLoaded('student', fn() => new UserResource($this->student)),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'repeat' => (bool) $this->status,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
