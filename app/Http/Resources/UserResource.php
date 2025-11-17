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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Computed fields
            'is_admin' => $this->isAdmin(),
            'is_pastor' => $this->isPastor(),
            'is_mentor' => $this->isMentor(),
            'is_member' => $this->isMember(),
            'can_manage_members' => $this->canManageMembers(),
            'can_manage_classes' => $this->canManageClasses(),
            'can_manage_users' => $this->canManageUsers(),

            // Relationships (when loaded)
            'mentorships' => $this->whenLoaded('mentorships', function () {
                return MentorshipResource::collection($this->mentorships);
            }),
        ];
    }
}
