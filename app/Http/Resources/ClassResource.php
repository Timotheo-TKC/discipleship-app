<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'mentor_id' => $this->mentor_id,
            'capacity' => $this->capacity,
            'duration_weeks' => $this->duration_weeks,
            'schedule_type' => $this->schedule_type,
            'schedule_day' => $this->schedule_day,
            'schedule_time' => $this->schedule_time,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'location' => $this->location,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships (when loaded)
            'mentor' => $this->whenLoaded('mentor', function () {
                return [
                    'id' => $this->mentor->id,
                    'name' => $this->mentor->name,
                    'email' => $this->mentor->email,
                    'role' => $this->mentor->role,
                ];
            }),

            'sessions' => $this->whenLoaded('sessions', function () {
                return SessionResource::collection($this->sessions);
            }),

            // Statistics (when requested)
            'statistics' => $this->when($request->has('include_stats'), function () {
                return [
                    'total_sessions' => $this->sessions()->count(),
                    'upcoming_sessions' => $this->sessions()->where('session_date', '>=', now())->count(),
                    'total_attendance' => $this->getTotalAttendance(),
                    'average_attendance' => $this->getAverageAttendance(),
                ];
            }),
        ];
    }

    /**
     * Get total attendance for the class
     */
    private function getTotalAttendance(): int
    {
        return $this->sessions()
            ->withCount('attendance')
            ->get()
            ->sum('attendance_count');
    }

    /**
     * Get average attendance for the class
     */
    private function getAverageAttendance(): float
    {
        $sessions = $this->sessions()->withCount('attendance')->get();

        if ($sessions->isEmpty()) {
            return 0.0;
        }

        $totalAttendance = $sessions->sum('attendance_count');
        $totalSessions = $sessions->count();

        return round($totalAttendance / $totalSessions, 2);
    }
}
