<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_member_with_valid_data()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $memberData = [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            'email' => 'john@example.com',
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'sms',
            'notes' => 'New member',
        ];

        $response = $this->post(route('members.store'), $memberData);

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            'email' => 'john@example.com',
            'date_of_conversion' => '2024-01-01 00:00:00',
            'preferred_contact' => 'sms',
        ]);
        
        // Verify User account was created
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => User::ROLE_MEMBER,
            'name' => 'John Doe',
        ]);
        
        // Verify member is linked to user
        $member = Member::where('email', 'john@example.com')->first();
        $user = User::where('email', 'john@example.com')->first();
        $this->assertEquals($member->user_id, $user->id);
    }

    /** @test */
    public function member_creation_requires_validation()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        // Test missing required fields
        $response = $this->post(route('members.store'), []);

        $response->assertSessionHasErrors(['full_name', 'phone', 'email', 'date_of_conversion', 'preferred_contact']);
    }

    /** @test */
    public function member_phone_must_be_unique()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        // Create first member
        Member::factory()->create(['phone' => '0712345678']);

        $memberData = [
            'full_name' => 'John Doe',
            'phone' => '0712345678', // Duplicate phone
            'email' => 'john@example.com',
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'sms',
        ];

        $response = $this->post(route('members.store'), $memberData);

        $response->assertSessionHasErrors(['phone']);
    }

    /** @test */
    public function member_email_must_be_valid_format()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $memberData = [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            'email' => 'invalid-email', // Invalid email format
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'sms',
        ];

        $response = $this->post(route('members.store'), $memberData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function member_email_is_required()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $memberData = [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            // Email is missing - should fail validation
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'sms',
        ];

        $response = $this->post(route('members.store'), $memberData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function admin_can_update_member()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $member = Member::factory()->create([
            'full_name' => 'John Doe',
            'phone' => '0712345678',
        ]);

        $updateData = [
            'full_name' => 'John Smith',
            'phone' => '0712345678',
            'email' => 'john.smith@example.com',
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'email',
        ];

        $response = $this->put(route('members.update', $member), $updateData);

        $response->assertRedirect(route('members.show', $member));
        $response->assertSessionHas('success', 'Member updated successfully.');

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'preferred_contact' => 'email',
        ]);
    }

    /** @test */
    public function admin_can_delete_member()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $member = Member::factory()->create();

        $response = $this->delete(route('members.destroy', $member));

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('success', 'Member deleted successfully.');

        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);
    }

    /** @test */
    public function member_can_be_viewed_by_authorized_users()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $member = Member::factory()->create();

        $response = $this->get(route('members.show', $member));

        $response->assertOk();
        $response->assertSee($member->full_name);
        $response->assertSee($member->phone);
    }

    /** @test */
    public function members_can_be_searched()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        // Create test members
        Member::factory()->create(['full_name' => 'John Doe', 'phone' => '0712345678']);
        Member::factory()->create(['full_name' => 'Jane Smith', 'phone' => '0723456789']);

        // Search by name
        $response = $this->get(route('members.index', ['search' => 'John']));

        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');

        // Search by phone
        $response = $this->get(route('members.index', ['search' => '0723456789']));

        $response->assertOk();
        $response->assertSee('Jane Smith');
        $response->assertDontSee('John Doe');
    }

    /** @test */
    public function members_can_be_filtered_by_conversion_date()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        // Create test members with different conversion dates
        Member::factory()->create(['date_of_conversion' => '2024-01-01']);
        Member::factory()->create(['date_of_conversion' => '2024-06-01']);

        // Filter by date range
        $response = $this->get(route('members.index', [
            'conversion_date_from' => '2024-01-01',
            'conversion_date_to' => '2024-03-01',
        ]));

        $response->assertOk();
        // Should only show members converted between Jan and Mar 2024
    }

    /** @test */
    public function members_can_be_filtered_by_preferred_contact()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        // Create test members with different contact preferences
        Member::factory()->create(['preferred_contact' => 'sms']);
        Member::factory()->create(['preferred_contact' => 'email']);

        // Filter by SMS preference
        $response = $this->get(route('members.index', ['preferred_contact' => 'sms']));

        $response->assertOk();
        // Should only show members who prefer SMS
    }
}
