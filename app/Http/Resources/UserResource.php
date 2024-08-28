<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_student' => (bool) $this->is_student,
            'created_at' => formatDateTime($this->created_at),
            'updated_at' => formatDateTime($this->updated_at),
        ];

        if ($this->is_student) {
            $data['student'] = $this->whenLoaded('student', fn() => new StudentResource($this->student));
        }

        return $data;
    }
}
