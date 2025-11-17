<?php

namespace Tests\Feature\Api;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
        $this->member = User::factory()->create(['role' => User::ROLE_MEMBER]);
    }

    /** @test */
    public function admin_can_get_members_via_api()
    {
        Member::factory()->count(5)->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/members');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'full_name',
                        'phone',
                        'email',
                        'date_of_conversion',
                        'preferred_contact',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'current_page',
                'total',
            ],
        ]);
    }

    /** @test */
    public function pastor_can_get_members_via_api()
    {
        Member::factory()->count(3)->create();

        $token = $this->pastor->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/members');

        $response->assertOk();
    }

    // Coordinator role removed - test removed

    /** @test */
    public function admin_can_create_member_via_api()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $memberData = [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            'email' => 'john@example.com',
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'sms',
            'notes' => 'New member',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/members', $memberData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Member created successfully',
        ]);

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
        ]);
    }

    /** @test */
    public function member_creation_requires_validation_via_api()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/members', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'full_name', 'phone', 'date_of_conversion', 'preferred_contact',
        ]);
    }

    /** @test */
    public function admin_can_get_single_member_via_api()
    {
        $member = Member::factory()->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/members/{$member->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'member' => [
                    'id' => $member->id,
                    'full_name' => $member->full_name,
                    'phone' => $member->phone,
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_update_member_via_api()
    {
        $member = Member::factory()->create([
            'full_name' => 'Original Name',
            'phone' => '0712345678',
        ]);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $updateData = [
            'full_name' => 'Updated Name',
            'phone' => '0712345678',
            'email' => 'updated@example.com',
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'email',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson("/api/v1/members/{$member->id}", $updateData);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Member updated successfully',
        ]);

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function admin_can_delete_member_via_api()
    {
        $member = Member::factory()->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/members/{$member->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Member deleted successfully',
        ]);

        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);
    }

    /** @test */
    public function members_can_be_searched_via_api()
    {
        Member::factory()->create(['full_name' => 'John Doe', 'phone' => '0712345678']);
        Member::factory()->create(['full_name' => 'Jane Smith', 'phone' => '0723456789']);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/members?search=John');

        $response->assertOk();
        $response->assertJsonCount(1, 'data.data');
    }

    /** @test */
    public function members_can_be_filtered_by_conversion_date_via_api()
    {
        Member::factory()->create(['date_of_conversion' => '2024-01-01']);
        Member::factory()->create(['date_of_conversion' => '2024-06-01']);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/members?conversion_date_from=2024-01-01&conversion_date_to=2024-03-01');

        $response->assertOk();
        // Should only return members converted between Jan and Mar 2024
    }

    /** @test */
    public function members_can_be_filtered_by_preferred_contact_via_api()
    {
        Member::factory()->create(['preferred_contact' => 'sms']);
        Member::factory()->create(['preferred_contact' => 'email']);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/members?preferred_contact=sms');

        $response->assertOk();
        // Should only return members who prefer SMS
    }

    /** @test */
    public function admin_can_get_member_attendance_via_api()
    {
        $member = Member::factory()->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/members/{$member->id}/attendance");

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [],
            ],
        ]);
    }

    /** @test */
    public function admin_can_get_member_mentorships_via_api()
    {
        $member = Member::factory()->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/members/{$member->id}/mentorships");

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [],
        ]);
    }

    /** @test */
    public function admin_can_get_member_statistics_via_api()
    {
        $member = Member::factory()->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/members/{$member->id}/statistics");

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_sessions',
                'present_count',
                'absent_count',
                'excused_count',
                'attendance_rate',
                'active_mentorships',
                'completed_mentorships',
            ],
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_member_endpoints()
    {
        $member = Member::factory()->create();

        $this->getJson('/api/v1/members')->assertStatus(401);
        $this->postJson('/api/v1/members', [])->assertStatus(401);
        $this->getJson("/api/v1/members/{$member->id}")->assertStatus(401);
        $this->putJson("/api/v1/members/{$member->id}", [])->assertStatus(401);
        $this->deleteJson("/api/v1/members/{$member->id}")->assertStatus(401);
    }
}
