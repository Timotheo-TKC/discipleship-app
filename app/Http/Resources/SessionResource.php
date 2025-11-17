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
            'class_id' => $this->class_id,
            'session_date' => $this->session_date->format('Y-m-d'),
            'topic' => $this->topic,
            'notes' => $this->notes,
            'location' => $this->location,
            'duration_minutes' => $this->duration_minutes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships (when loaded)
            'class' => $this->whenLoaded('class', function () {
                return [
                    'id' => $this->class->id,
                    'title' => $this->class->title,
                    'mentor' => $this->class->mentor->name ?? null,
                ];
            }),

            'attendance' => $this->whenLoaded('attendance', function () {
                return AttendanceResource::collection($this->attendance);
            }),

            'createdBy' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),

            // Statistics (when requested)
            'attendance_stats' => $this->when($request->has('include_stats'), function () {
                return [
                    'total_members' => $this->attendance()->count(),
                    'present_count' => $this->attendance()->where('status', 'present')->count(),
                    'absent_count' => $this->attendance()->where('status', 'absent')->count(),
                    'excused_count' => $this->attendance()->where('status', 'excused')->count(),
                    'attendance_rate' => $this->getAttendanceRate(),
                ];
            }),

            // Computed fields
            'is_past' => $this->session_date->isPast(),
            'is_today' => $this->session_date->isToday(),
            'is_upcoming' => $this->session_date->isFuture(),
            'status' => $this->getSessionStatus(),
        ];
    }

    /**
     * Get attendance rate for the session
     */
    private function getAttendanceRate(): float
    {
        $totalMembers = $this->attendance()->count();
        if ($totalMembers === 0) {
            return 0.0;
        }

        $presentCount = $this->attendance()->where('status', 'present')->count();

        return round(($presentCount / $totalMembers) * 100, 2);
    }

    /**
     * Get session status
     */
    private function getSessionStatus(): string
    {
        if ($this->session_date->isPast()) {
            return 'completed';
        } elseif ($this->session_date->isToday()) {
            return 'today';
        } else {
            return 'upcoming';
        }
    }
}
