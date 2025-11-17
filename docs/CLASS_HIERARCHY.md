# Complete Class Hierarchy Documentation

## Overview

This document provides a comprehensive view of all classes in the discipleship system, their inheritance relationships, and organizational structure.

---

## 1. Model Classes (Eloquent Models)

All models extend `Illuminate\Database\Eloquent\Model` and use `HasFactory` trait.

### Core Models

```
Illuminate\Database\Eloquent\Model
└── App\Models\User
    ├── Uses: HasFactory, Notifiable, HasApiTokens
    ├── Implements: MustVerifyEmail
    └── Relationships:
        ├── hasMany(Member)
        ├── hasMany(DiscipleshipClass, 'mentor_id') → mentoredClasses()
        ├── hasMany(Mentorship, 'mentor_id') → mentorships()
        └── hasMany(Booking, 'mentor_id') → bookingsAsMentor()

└── App\Models\Member
    ├── Uses: HasFactory
    └── Relationships:
        ├── belongsTo(User)
        ├── hasMany(Attendance)
        ├── hasMany(Mentorship, 'member_id')
        ├── hasMany(ClassEnrollment) → enrollments()
        ├── hasMany(Booking) → bookings()
        └── hasMany(Testimonial) → testimonials()

└── App\Models\DiscipleshipClass
    ├── Uses: HasFactory
    ├── Table: 'classes'
    └── Relationships:
        ├── belongsTo(User, 'mentor_id') → mentor()
        ├── hasMany(ClassSession) → sessions()
        ├── hasManyThrough(Attendance, ClassSession) → attendance()
        └── hasMany(ClassEnrollment) → enrollments()

└── App\Models\ClassSession
    ├── Uses: HasFactory
    ├── Table: 'class_sessions'
    └── Relationships:
        ├── belongsTo(DiscipleshipClass, 'class_id') → class()
        ├── belongsTo(User, 'created_by') → creator()
        └── hasMany(Attendance) → attendance()

└── App\Models\Attendance
    ├── Uses: HasFactory
    └── Relationships:
        ├── belongsTo(Member) → member()
        └── belongsTo(ClassSession, 'class_session_id') → classSession()

└── App\Models\Mentorship
    ├── Uses: HasFactory
    └── Relationships:
        ├── belongsTo(Member) → member()
        └── belongsTo(User, 'mentor_id') → mentor()

└── App\Models\ClassEnrollment
    ├── Uses: HasFactory
    └── Relationships:
        ├── belongsTo(DiscipleshipClass, 'class_id') → class()
        └── belongsTo(Member) → member()

└── App\Models\Booking
    ├── Uses: HasFactory
    └── Relationships:
        ├── belongsTo(Member) → member()
        └── belongsTo(User, 'mentor_id') → mentor()

└── App\Models\Testimonial
    ├── Uses: HasFactory
    └── Relationships:
        └── belongsTo(Member) → member()

└── App\Models\Message
    ├── Uses: HasFactory
    └── Relationships:
        └── hasMany(MessageLog) → logs()

└── App\Models\MessageLog
    ├── Uses: HasFactory
    └── Relationships:
        └── belongsTo(Message) → message()
```

---

## 2. Controller Classes

All controllers extend `App\Http\Controllers\Controller`, which extends `Illuminate\Routing\Controller`.

### Base Controller

```
Illuminate\Routing\Controller
└── App\Http\Controllers\Controller
    └── Base class for all controllers
```

### Web Controllers

```
App\Http\Controllers\Controller
├── App\Http\Controllers\DashboardController
├── App\Http\Controllers\AdminController
├── App\Http\Controllers\UserController
├── App\Http\Controllers\MemberController
├── App\Http\Controllers\ClassController
├── App\Http\Controllers\SessionController
├── App\Http\Controllers\AttendanceController
├── App\Http\Controllers\MentorshipController
├── App\Http\Controllers\ProfileController
│
└── App\Http\Controllers\Member\
    ├── EnrollmentController
    ├── BookingController
    └── TestimonialController
```

### API Controllers

```
App\Http\Controllers\Controller
└── App\Http\Controllers\Api\
    ├── AuthController
    ├── MemberController
    ├── ClassController
    ├── SessionController
    ├── AttendanceController
    └── MentorshipController
```

### Authentication Controllers

```
App\Http\Controllers\Controller
└── App\Http\Controllers\Auth\
    ├── AuthenticatedSessionController
    ├── ConfirmablePasswordController
    ├── EmailVerificationNotificationController
    ├── EmailVerificationPromptController
    ├── NewPasswordController
    ├── PasswordController
    ├── PasswordResetLinkController
    ├── RegisteredUserController
    └── VerifyEmailController
```

---

## 3. Policy Classes

All policies follow Laravel's authorization pattern.

```
Illuminate\Foundation\Auth\Access\HandlesAuthorization (trait)
│
├── App\Policies\UserPolicy
├── App\Policies\MemberPolicy
├── App\Policies\ClassPolicy
├── App\Policies\SessionPolicy
├── App\Policies\AttendancePolicy
└── App\Policies\MentorshipPolicy
```

---

## 4. Request Validation Classes

All form requests extend `Illuminate\Foundation\Http\FormRequest`.

```
Illuminate\Foundation\Http\FormRequest
├── App\Http\Requests\UserRequest
├── App\Http\Requests\MemberRequest
├── App\Http\Requests\ClassRequest
├── App\Http\Requests\SessionRequest
├── App\Http\Requests\AttendanceRequest
├── App\Http\Requests\MentorshipRequest
├── App\Http\Requests\ProfileUpdateRequest
│
└── App\Http\Requests\Member\
    ├── EnrollmentRequest
    ├── BookingRequest
    └── TestimonialRequest

└── App\Http\Requests\Auth\
    └── LoginRequest
```

---

## 5. Resource Classes (API Resources)

All resources extend `Illuminate\Http\Resources\Json\JsonResource`.

```
Illuminate\Http\Resources\Json\JsonResource
├── App\Http\Resources\UserResource
├── App\Http\Resources\MemberResource
├── App\Http\Resources\ClassResource
├── App\Http\Resources\SessionResource
├── App\Http\Resources\AttendanceResource
└── App\Http\Resources\MentorshipResource
```

---

## 6. Service Classes

Service classes are standalone utility classes (no base class).

```
App\Services\
├── BibleVerseService
│   └── Methods:
│       ├── getDailyVerse()
│       ├── getRandomVerse()
│       └── getVerseForDate()
│
└── MemberImportService
    └── Methods:
        ├── importFromCsv()
        └── validateMemberData()
```

---

## 7. Provider Classes

```
Illuminate\Support\ServiceProvider
├── App\Providers\AppServiceProvider
│   └── Extends: Illuminate\Foundation\Support\Providers\AppServiceProvider
│
└── App\Providers\AuthServiceProvider
    └── Extends: Illuminate\Foundation\Support\Providers\AuthServiceProvider
    └── Registers:
        ├── Policies (UserPolicy, MemberPolicy, etc.)
        └── Gates (manage-users, manage-classes, etc.)
```

---

## 8. Middleware Classes

```
Illuminate\Http\Middleware\
└── App\Http\Middleware\RoleMiddleware
    └── Extends: No base class (implements middleware interface)
    └── Purpose: Role-based access control
```

---

## 9. View Component Classes

```
Illuminate\View\Component
├── App\View\Components\AppLayout
│   └── Extends: Illuminate\View\Component
│
└── App\View\Components\GuestLayout
    └── Extends: Illuminate\View\Component
```

---

## 10. Complete Relationship Diagram

### Entity Relationships

```
User (Authenticatable)
│
├── 1:N → Member (members)
├── 1:N → DiscipleshipClass (mentoredClasses, mentor_id)
├── 1:N → Mentorship (mentorships, mentor_id)
└── 1:N → Booking (bookingsAsMentor, mentor_id)

Member
│
├── N:1 → User (user_id, optional)
├── 1:N → Attendance
├── 1:N → Mentorship (member_id)
├── 1:N → ClassEnrollment
├── 1:N → Booking
└── 1:N → Testimonial

DiscipleshipClass
│
├── N:1 → User (mentor_id)
├── 1:N → ClassSession
├── 1:N → Attendance (through ClassSession)
└── 1:N → ClassEnrollment

ClassSession
│
├── N:1 → DiscipleshipClass (class_id)
├── N:1 → User (created_by)
└── 1:N → Attendance

Attendance
│
├── N:1 → Member (member_id)
└── N:1 → ClassSession (class_session_id)

Mentorship
│
├── N:1 → Member (member_id)
└── N:1 → User (mentor_id)

ClassEnrollment
│
├── N:1 → DiscipleshipClass (class_id)
└── N:1 → Member (member_id)

Booking
│
├── N:1 → Member (member_id)
└── N:1 → User (mentor_id)

Testimonial
│
└── N:1 → Member (member_id)

Message
│
└── 1:N → MessageLog

MessageLog
│
└── N:1 → Message (message_id)
```

---

## 11. Trait Usage

### Models Use:
- `HasFactory` - All models use this for factory support
- `Notifiable` - User model only
- `HasApiTokens` - User model only (Laravel Sanctum)

### Policies Use:
- `HandlesAuthorization` - AttendancePolicy only

---

## 12. Interface Implementations

```
User implements:
└── Illuminate\Contracts\Auth\MustVerifyEmail

All Models extend:
└── Illuminate\Database\Eloquent\Model

All Controllers extend:
└── Illuminate\Routing\Controller (via App\Http\Controllers\Controller)

All Form Requests extend:
└── Illuminate\Foundation\Http\FormRequest

All Resources extend:
└── Illuminate\Http\Resources\Json\JsonResource
```

---

## 13. Class Statistics

### Total Classes by Category:

- **Models:** 11 classes
- **Controllers:** 27 classes (6 API + 10 Web + 11 Auth)
- **Policies:** 6 classes
- **Requests:** 11 classes
- **Resources:** 6 classes
- **Services:** 2 classes
- **Providers:** 2 classes
- **Middleware:** 1 class
- **Components:** 2 classes

**Total: 68 classes**

---

## 14. Namespace Organization

```
App\
├── Http\
│   ├── Controllers\
│   │   ├── Api\
│   │   ├── Auth\
│   │   └── Member\
│   ├── Middleware\
│   ├── Requests\
│   │   ├── Auth\
│   │   └── Member\
│   └── Resources\
│
├── Models\
│
├── Policies\
│
├── Providers\
│
├── Services\
│
└── View\
    └── Components\
```

---

## 15. Key Design Patterns

1. **Repository Pattern** (implicit) - Models handle data access
2. **Policy Pattern** - Authorization logic in Policy classes
3. **Resource Pattern** - API transformation via Resource classes
4. **Service Pattern** - Business logic in Service classes
5. **Form Request Pattern** - Validation in dedicated Request classes
6. **Factory Pattern** - Model factories for testing
7. **Observer Pattern** - Laravel events/observers (if used)

---

## 16. Dependencies

### External Packages:
- `laravel/framework` - Core framework
- `laravel/sanctum` - API authentication
- `laravel/breeze` - Authentication scaffolding

### Internal Dependencies:
- All controllers depend on Models
- All policies depend on Models
- All resources depend on Models
- Services are independent utilities

---

## Notes

- All models use Eloquent ORM relationships
- Controllers follow RESTful conventions
- Policies enforce authorization at model level
- Resources transform models for API responses
- Form Requests handle validation
- Services contain reusable business logic

