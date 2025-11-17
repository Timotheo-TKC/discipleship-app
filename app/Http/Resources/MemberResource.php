<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'date_of_conversion' => $this->date_of_conversion->format('Y-m-d'),
            'preferred_contact' => $this->preferred_contact,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships (when loaded)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'role' => $this->user->role,
                ];
            }),

            'attendance' => $this->whenLoaded('attendance', function () {
                return AttendanceResource::collection($this->attendance);
            }),

            'mentorships' => $this->whenLoaded('mentorships', function () {
                return MentorshipResource::collection($this->mentorships);
            }),

            // Statistics (when requested)
            'attendance_stats' => $this->when($request->has('include_stats'), function () {
                return [
                    'total_sessions' => $this->attendance()->count(),
                    'present_count' => $this->attendance()->where('status', 'present')->count(),
                    'absent_count' => $this->attendance()->where('status', 'absent')->count(),
                    'excused_count' => $this->attendance()->where('status', 'excused')->count(),
                    'attendance_rate' => $this->getAttendanceRate(),
                ];
            }),
        ];
    }

    /**
     * Calculate attendance rate
     */
    private function getAttendanceRate(): float
    {
        $totalSessions = $this->attendance()->count();
        if ($totalSessions === 0) {
            return 0.0;
        }

        $presentCount = $this->attendance()->where('status', 'present')->count();

        return round(($presentCount / $totalSessions) * 100, 2);
    }
}
