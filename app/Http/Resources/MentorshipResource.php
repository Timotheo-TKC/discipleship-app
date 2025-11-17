<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorshipResource extends JsonResource
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
            'member_id' => $this->member_id,
            'mentor_id' => $this->mentor_id,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'meeting_frequency' => $this->meeting_frequency,
            'notes' => $this->notes,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships (when loaded)
            'member' => $this->whenLoaded('member', function () {
                return [
                    'id' => $this->member->id,
                    'full_name' => $this->member->full_name,
                    'phone' => $this->member->phone,
                    'email' => $this->member->email,
                ];
            }),

            'mentor' => $this->whenLoaded('mentor', function () {
                return [
                    'id' => $this->mentor->id,
                    'name' => $this->mentor->name,
                    'email' => $this->mentor->email,
                    'role' => $this->mentor->role,
                ];
            }),

            // Computed fields
            'duration_days' => $this->start_date->diffInDays(now()),
            'is_active' => $this->status === 'active',
            'is_completed' => $this->status === 'completed',
            'is_paused' => $this->status === 'paused',
        ];
    }
}
