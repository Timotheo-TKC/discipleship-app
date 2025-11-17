# Role Analysis: Coordinator vs Mentor (Pastor) Functions

## Executive Summary

This document analyzes the functional differences between **Coordinators** and **Mentors (Pastors)** in the discipleship system, and evaluates whether mentors can perform coordinator roles.

**Key Finding:** Pastors can perform ALL coordinator functions, but coordinators CANNOT perform all pastor/mentor functions due to permission restrictions.

---

## Role Definitions

### Coordinator Role
A **coordinator** is a user who manages discipleship classes but has limited administrative permissions.

### Mentor/Pastor Role  
A **pastor** serves as both a mentor (teaching classes, one-on-one mentorship) AND has broader administrative responsibilities including member management.

**Note:** In the codebase, "mentor" refers to any user (admin, pastor, or coordinator) who can be assigned to mentor a class or mentorship. However, the **pastor** role specifically combines mentoring with administrative privileges.

---

## Detailed Function Comparison

### 1. Class Management

| Function | Coordinator | Pastor | Notes |
|----------|------------|--------|-------|
| **View Classes** | ✅ All classes | ✅ All classes | Both can browse |
| **Create Classes** | ✅ Can create | ✅ Can create | Both have `canManageClasses()` |
| **Edit Classes** | ⚠️ Only classes they mentor | ✅ All classes OR their own | Coordinator restricted |
| **Delete Classes** | ❌ No | ❌ No (Admin only) | Neither can delete |
| **Manage Sessions** | ⚠️ Only for their classes | ✅ All classes OR their own | Coordinator restricted |
| **View Attendance** | ⚠️ Only for their classes | ✅ All classes OR their own | Coordinator restricted |
| **Mark Attendance** | ⚠️ Only for their classes | ✅ All classes OR their own | Coordinator restricted |

**Code Reference:**
```php
// app/Models/User.php
public function canManageClasses(): bool
{
    return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_PASTOR, self::ROLE_COORDINATOR]);
}

// app/Policies/ClassPolicy.php
// Coordinators can only manage classes they mentor
if ($user->isCoordinator()) {
    return $user->id === $class->mentor_id;
}
```

**Analysis:** 
- Both roles can manage classes, BUT coordinators are restricted to classes where they are assigned as the mentor.
- Pastors can manage all classes or classes they mentor (broader scope).

---

### 2. Member Management

| Function | Coordinator | Pastor | Notes |
|----------|------------|--------|-------|
| **View Members** | ❌ No | ✅ Yes | Critical difference |
| **Create Members** | ❌ No | ✅ Yes | Critical difference |
| **Edit Members** | ❌ No | ✅ Yes | Critical difference |
| **Delete Members** | ❌ No | ❌ No (Admin only) | Neither can delete |
| **Import Members** | ❌ No | ✅ Yes | Coordinator cannot import |

**Code Reference:**
```php
// app/Models/User.php
public function canManageMembers(): bool
{
    return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_PASTOR]);
}

// app/Policies/MemberPolicy.php
public function viewAny(User $user): bool
{
    return $user->canManageMembers(); // Excludes coordinators
}
```

**Analysis:** This is the PRIMARY functional difference. Coordinators have NO access to member management, while pastors have full access.

---

### 3. Mentorship Management

| Function | Coordinator | Pastor | Notes |
|----------|------------|--------|-------|
| **View Mentorships** | ❌ No | ✅ Yes | Coordinator blocked |
| **Create Mentorships** | ❌ No | ✅ Yes | Coordinator blocked |
| **Assign Mentorships** | ❌ No | ✅ Yes | Coordinator blocked |
| **Update Mentorships** | ❌ No | ✅ Yes | Coordinator blocked |
| **View Own Mentorships** | ⚠️ Can be mentor, but can't manage | ✅ Can manage | Coordinator can be assigned but can't CRUD |

**Code Reference:**
```php
// app/Http/Controllers/MentorshipController.php
$this->middleware(function ($request, $next) {
    if (! $request->user()->hasAnyRole(['admin', 'pastor'])) {
        abort(403, 'Access denied. Only admins and pastors can manage mentorships.');
    }
    return $next($request);
});
```

**Analysis:** Coordinators are COMPLETELY excluded from mentorship management, even though they can BE mentors in a mentorship relationship. This creates a functional gap.

---

### 4. Attendance Management

| Function | Coordinator | Pastor | Notes |
|----------|------------|--------|-------|
| **View Attendance** | ⚠️ Only their classes | ✅ All classes OR their own | Coordinator restricted |
| **Mark Attendance** | ⚠️ Only their classes | ✅ All classes OR their own | Coordinator restricted |
| **Bulk Attendance** | ⚠️ Only their classes | ✅ All classes OR their own | Coordinator restricted |
| **Export Attendance** | ⚠️ Only their classes | ✅ All classes OR their own | Coordinator restricted |

**Code Reference:**
```php
// app/Policies/AttendancePolicy.php
public function viewAttendance(User $user, $class): bool
{
    if ($user->isAdmin() || $user->isPastor()) {
        return true; // Full access
    }
    
    if ($user->isCoordinator()) {
        return $user->id === $class->mentor_id; // Restricted access
    }
    
    return false;
}
```

**Analysis:** Coordinators have limited attendance management, restricted to classes they mentor.

---

### 5. User Management

| Function | Coordinator | Pastor | Notes |
|----------|------------|--------|-------|
| **View Users** | ❌ No | ❌ No (Admin only) | Neither can view |
| **Update Users** | ❌ No (except self) | ⚠️ Can update coordinators & members | Pastor has limited user management |
| **Change Roles** | ❌ No | ❌ No (Admin only) | Neither can change roles |

**Code Reference:**
```php
// app/Policies/UserPolicy.php
// Pastors can update coordinators and members, but not other pastors or admins
if ($user->isPastor()) {
    return $model->hasAnyRole([User::ROLE_COORDINATOR, User::ROLE_MEMBER]);
}

// Coordinators cannot update other users
return false;
```

**Analysis:** Pastors have limited user management (can update coordinators and members), while coordinators have none.

---

## Can a Mentor (Pastor) Perform Coordinator Roles?

### ✅ YES - With Full Compatibility

**Pastors can perform ALL coordinator functions:**

1. ✅ **Class Management** - Pastors have `canManageClasses()` permission
2. ✅ **Class Creation** - Pastors can create classes (same as coordinators)
3. ✅ **Session Management** - Pastors can manage sessions (broader scope than coordinators)
4. ✅ **Attendance Management** - Pastors can mark attendance (broader scope than coordinators)
5. ✅ **Be Assigned as Mentor** - Pastors can be assigned as `mentor_id` in classes

**Evidence from Code:**
```php
// Both roles share the same permission method
public function canManageClasses(): bool
{
    return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_PASTOR, self::ROLE_COORDINATOR]);
}

// Pastors have broader access than coordinators
if ($user->isPastor()) {
    return true; // Can manage all classes
} else if ($user->isCoordinator()) {
    return $user->id === $class->mentor_id; // Restricted to own classes
}
```

### ⚠️ Coordinators CANNOT Perform All Mentor Functions

**Coordinators are missing:**
1. ❌ **Member Management** - Cannot view, create, or edit members
2. ❌ **Mentorship Management** - Cannot create, assign, or manage mentorship relationships
3. ⚠️ **Class Scope** - Restricted to classes they mentor (pastors can manage all classes)

---

## Functional Overlap Analysis

### Shared Functions (Both Can Do)
1. Create discipleship classes
2. Manage class sessions (coordinator restricted to own)
3. Mark attendance (coordinator restricted to own)
4. Be assigned as a mentor to classes
5. Be assigned as a mentor in one-on-one mentorships

### Coordinator-Only Limitations
1. Can ONLY manage classes where they are the assigned mentor
2. Cannot manage members at all
3. Cannot manage mentorship relationships (even though they can be mentors)

### Pastor-Only Functions
1. Can manage ALL classes (not restricted to assigned classes)
2. Full member management (view, create, edit, import)
3. Full mentorship management (create, assign, update)
4. Can update coordinator and member user accounts

---

## Recommendations

### 1. Current System Design Analysis

**Strengths:**
- Clear separation of concerns (coordinators = class-focused, pastors = broader administrative role)
- Prevents coordinators from accessing sensitive member data
- Scalable permission model

**Weaknesses:**
- Coordinators can be mentors but cannot manage mentorship relationships (inconsistency)
- Coordinators cannot view member information even when needed for class management
- No pathway for coordinators to upgrade to pastor role (manual admin intervention required)

### 2. Recommended Role Structure

**Option A: Keep Current Structure (Recommended)**
- ✅ Clear role hierarchy: Admin → Pastor → Coordinator → Member
- ✅ Coordinators serve as "class teachers" with limited scope
- ✅ Pastors handle administrative oversight
- ⚠️ Requires pastors to manage mentorship assignments (not coordinators)

**Option B: Merge Coordinator into Pastor**
- ⚠️ Would eliminate the coordinator role entirely
- ⚠️ All class management would require pastor-level permissions
- ⚠️ Less granular permission control

**Option C: Expand Coordinator Permissions**
- Consider allowing coordinators to:
  - View members enrolled in their classes (read-only)
  - Create mentorship relationships (for members in their classes only)
  - View basic member information (name, contact) for class attendance

### 3. Validation for Mentor = Coordinator Question

**Can a mentor (pastor) perform coordinator roles?**

**Answer: YES, with qualifications:**

✅ **Functional Compatibility:** Pastors have all coordinator permissions PLUS additional ones.  
✅ **Scope Superiority:** Pastors can manage ALL classes, while coordinators are restricted to assigned classes.  
✅ **Use Case:** In smaller churches, a pastor could handle all coordinator duties without issues.  
⚠️ **Limitation:** In larger churches, coordinators provide a middle layer for class-specific management without full administrative access.

**Can a coordinator perform all mentor roles?**

**Answer: NO, with reasons:**

❌ **Missing Permissions:** Coordinators cannot manage members or mentorship relationships.  
❌ **Access Restrictions:** Coordinators cannot view member information needed for mentorship assignments.  
❌ **Role Purpose:** Coordinators are designed as "limited administrators" for class management only.

---

## Conclusion

**Pastors (Mentors) CAN perform coordinator functions** because:
1. They share the same class management permissions
2. They have broader scope (all classes vs. assigned classes only)
3. They have additional permissions (member management, mentorship management)

**Coordinators CANNOT fully perform pastor/mentor functions** because:
1. They lack member management permissions
2. They cannot manage mentorship relationships
3. They are restricted to classes they mentor

**System Validation:**
- Current design is **logically sound** - clear hierarchy and separation of concerns
- Overlap is **intentional** - pastors can handle coordinator duties
- Restrictions are **security-focused** - prevent unauthorized access to member data

**Recommendation:** Keep current structure but consider allowing coordinators to:
- View members enrolled in their classes (read-only)
- View basic member contact information for attendance purposes
- Create mentorship requests (pending pastor approval)

This would improve coordinator functionality while maintaining security boundaries.

