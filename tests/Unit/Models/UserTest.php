<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_has_role_helper_methods()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
        $mentor = User::factory()->create(['role' => User::ROLE_MENTOR]);
        $member = User::factory()->create(['role' => User::ROLE_MEMBER]);

        // Test isAdmin
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($pastor->isAdmin());
        $this->assertFalse($member->isAdmin());

        // Test isPastor
        $this->assertFalse($admin->isPastor());
        $this->assertTrue($pastor->isPastor());
        $this->assertFalse($member->isPastor());

        // Test isMentor
        $this->assertFalse($admin->isMentor());
        $this->assertFalse($pastor->isMentor());
        $this->assertTrue($mentor->isMentor());
        $this->assertFalse($member->isMentor());

        // Test isMember
        $this->assertFalse($admin->isMember());
        $this->assertFalse($pastor->isMember());
        $this->assertTrue($member->isMember());
    }

    /** @test */
    public function user_has_role_checking_methods()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
        $mentor = User::factory()->create(['role' => User::ROLE_MENTOR]);

        // Test hasRole
        $this->assertTrue($admin->hasRole(User::ROLE_ADMIN));
        $this->assertFalse($admin->hasRole(User::ROLE_PASTOR));

        // Test hasAnyRole
        $this->assertTrue($admin->hasAnyRole([User::ROLE_ADMIN, User::ROLE_PASTOR]));
        $this->assertTrue($pastor->hasAnyRole([User::ROLE_ADMIN, User::ROLE_PASTOR]));
        $this->assertFalse($pastor->hasAnyRole([User::ROLE_ADMIN]));
    }

    /** @test */
    public function user_has_permission_methods()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
        $mentor = User::factory()->create(['role' => User::ROLE_MENTOR]);
        $member = User::factory()->create(['role' => User::ROLE_MEMBER]);

        // Test canManageUsers
        $this->assertTrue($admin->canManageUsers());
        $this->assertFalse($pastor->canManageUsers());
        $this->assertFalse($member->canManageUsers());

        // Test canManageClasses
        $this->assertTrue($admin->canManageClasses());
        $this->assertTrue($pastor->canManageClasses());
        $this->assertTrue($mentor->canManageClasses());
        $this->assertFalse($member->canManageClasses());

        // Test canManageMembers
        $this->assertTrue($admin->canManageMembers());
        $this->assertTrue($pastor->canManageMembers());
        $this->assertTrue($mentor->canManageMembers());
        $this->assertFalse($member->canManageMembers());
    }

    /** @test */
    public function user_can_get_all_roles()
    {
        $roles = User::getRoles();

        $this->assertIsArray($roles);
        $this->assertContains(User::ROLE_ADMIN, $roles);
        $this->assertContains(User::ROLE_PASTOR, $roles);
        $this->assertContains(User::ROLE_MENTOR, $roles);
        $this->assertNotContains('coordinator', $roles);
        $this->assertContains(User::ROLE_MEMBER, $roles);
        $this->assertCount(4, $roles);
    }

    /** @test */
    public function user_password_is_hashed()
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(password_verify('plaintext', $user->password));
    }

    /** @test */
    public function user_has_relationships()
    {
        $user = User::factory()->create();

        // Test that relationships exist (they should be defined in the model)
        $this->assertTrue(method_exists($user, 'members'));
        $this->assertTrue(method_exists($user, 'mentoredClasses'));
        $this->assertTrue(method_exists($user, 'mentorships'));
    }

    /** @test */
    public function user_can_have_members()
    {
        $user = User::factory()->create();
        $member = \App\Models\Member::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->members->contains($member));
        $this->assertEquals($user->id, $member->user_id);
    }

    /** @test */
    public function user_can_mentor_classes()
    {
        $user = User::factory()->create(['role' => User::ROLE_MENTOR]);
        $class = \App\Models\DiscipleshipClass::factory()->create(['mentor_id' => $user->id]);

        $this->assertTrue($user->mentoredClasses->contains($class));
        $this->assertEquals($user->id, $class->mentor_id);
    }

    /** @test */
    public function user_can_have_mentorships()
    {
        $user = User::factory()->create(['role' => User::ROLE_MENTOR]);
        $mentorship = \App\Models\Mentorship::factory()->create(['mentor_id' => $user->id]);

        $this->assertTrue($user->mentorships->contains($mentorship));
        $this->assertEquals($user->id, $mentorship->mentor_id);
    }
}
