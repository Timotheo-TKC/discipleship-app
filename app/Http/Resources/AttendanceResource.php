<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'class_session_id' => $this->class_session_id,
            'member_id' => $this->member_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'marked_by' => $this->marked_by,
            'marked_at' => $this->marked_at->toISOString(),
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

            'classSession' => $this->whenLoaded('classSession', function () {
                return [
                    'id' => $this->classSession->id,
                    'session_date' => $this->classSession->session_date->format('Y-m-d'),
                    'topic' => $this->classSession->topic,
                    'class' => [
                        'id' => $this->classSession->class->id,
                        'title' => $this->classSession->class->title,
                    ],
                ];
            }),

            'markedBy' => $this->whenLoaded('markedBy', function () {
                return [
                    'id' => $this->markedBy->id,
                    'name' => $this->markedBy->name,
                    'email' => $this->markedBy->email,
                ];
            }),

            // Computed fields
            'is_present' => $this->status === 'present',
            'is_absent' => $this->status === 'absent',
            'is_excused' => $this->status === 'excused',
        ];
    }
}
