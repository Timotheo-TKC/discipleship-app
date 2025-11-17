# Mentor System Documentation

## Overview

**Important:** "Mentor" is **NOT a user role** in this system. It is a **function/relationship** that certain users can perform.

## Who Can Be Mentors?

Currently, only **Admins** and **Pastors** can be assigned as mentors because:
1. They have the necessary permissions to manage classes and memberships
2. They can access mentorship management features
3. They have the administrative capabilities required for mentorship responsibilities

## How Mentors Work

### 1. Class Mentors
- **Relationship:** `DiscipleshipClass` has a `mentor_id` foreign key to `User`
- **Who:** Any user with role `admin` or `pastor`
- **Purpose:** Lead and manage discipleship classes
- **Access:** Can create sessions, mark attendance, manage class enrollment

### 2. Mentorship Relationships
- **Relationship:** `Mentorship` has a `mentor_id` foreign key to `User`
- **Who:** Any user with role `admin` or `pastor`
- **Purpose:** One-on-one discipleship relationship with a member
- **Features:** Track meeting frequency, status, notes, and completion

### 3. Booking Mentors
- **Relationship:** `Booking` has a `mentor_id` foreign key to `User`
- **Who:** Any user with role `admin` or `pastor`
- **Purpose:** Members can book one-on-one sessions with mentors

## Database Structure

```sql
-- Classes table
classes.mentor_id -> users.id (where users.role IN ('admin', 'pastor'))

-- Mentorships table
mentorships.mentor_id -> users.id (where users.role IN ('admin', 'pastor'))

-- Bookings table
bookings.mentor_id -> users.id (where users.role IN ('admin', 'pastor'))
```

## Code Implementation

### Model Relationships

**User Model:**
```php
// User can mentor classes
public function mentoredClasses(): HasMany
{
    return $this->hasMany(DiscipleshipClass::class, 'mentor_id');
}

// User can have mentorship relationships
public function mentorships(): HasMany
{
    return $this->hasMany(Mentorship::class, 'mentor_id');
}

// User can have bookings
public function bookingsAsMentor(): HasMany
{
    return $this->hasMany(Booking::class, 'mentor_id');
}
```

### Controller Queries

All mentor selection queries correctly filter to admin and pastor roles:
```php
$mentors = User::whereIn('role', ['admin', 'pastor'])
              ->orderBy('name')
              ->get();
```

## After Coordinator Removal

Previously, coordinators could also be mentors, but they were restricted to:
- Only classes they were assigned to
- Limited administrative access

After removing coordinators:
- ✅ **Pastors now have all coordinator permissions PLUS full mentorship capabilities**
- ✅ **All coordinator-mentored classes have been reassigned to pastors**
- ✅ **All coordinator mentorships have been reassigned to pastors**
- ✅ **System is cleaner with only 3 roles: Admin, Pastor, Member**

## Verification

The system correctly:
1. ✅ Only shows admins and pastors in mentor selection dropdowns
2. ✅ Only allows admins and pastors to be assigned as mentors
3. ✅ Validates mentor assignments in form requests
4. ✅ Displays mentor role correctly in views
5. ✅ Handles mentor relationships in all controllers

## Summary

**Mentor = Function/Relationship** (not a role)
- **Who:** Admins and Pastors
- **What:** Can be assigned to classes, mentorships, and bookings
- **Status:** ✅ Working correctly after coordinator removal
- **No changes needed** - the system handles mentors properly as a relationship, not a role

