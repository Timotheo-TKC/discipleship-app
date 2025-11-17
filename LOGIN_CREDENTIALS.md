# Discipleship System - Login Credentials & System Status

## 🔐 Login Credentials

### Administrator Accounts

**Primary Admin:**
- **Email:** `admin@discipleship.local`
- **Name:** Admin User
- **Password:** `password`
- **Access:** Full system access (all features)

**Secondary Admin:**
- **Email:** `emerald68@example.net`
- **Name:** Carolyne Green
- **Password:** `password`
- **Access:** Full system access

---

### Pastor Accounts (Mentors)

**Pastor John Smith:**
- **Email:** `pastor@discipleship.local`
- **Name:** Pastor John Smith
- **Password:** `password`
- **Access:** 
  - Manage classes (create, edit, assign mentors)
  - Manage mentorships (create, assign, track)
  - View all members and their attendance
  - View dashboard with ministry statistics
  - Mark attendance for their classes

**Pastor Mary Johnson:**
- **Email:** `pastor2@discipleship.local`
- **Name:** Pastor Mary Johnson
- **Password:** `password`
- **Access:** Same as above

---

### Coordinator Accounts (Mentors)

**Sarah Wilson:**
- **Email:** `coordinator@discipleship.local`
- **Name:** Sarah Wilson (Coordinator)
- **Password:** `password`
- **Access:**
  - Manage classes (create, edit, assign mentors)
  - View members (read-only)
  - View dashboard with class statistics
  - Mark attendance for their classes
  - **Cannot:** Manage mentorships or members directly

**Michael Brown:**
- **Email:** `coordinator2@discipleship.local`
- **Name:** Michael Brown (Coordinator)
- **Password:** `password`
- **Access:** Same as above

---

### Member Accounts

**Sample Member 1:**
- **Email:** `erick73@example.net`
- **Name:** Imelda Smith
- **Password:** `password`
- **Access:**
  - View own profile
  - View dashboard (limited view)
  - View classes (read-only)

**Sample Member 2:**
- **Email:** `timothychumo6@gmail.com`
- **Name:** tim
- **Password:** `password`
- **Access:** Same as above

---

## 🌐 Access URLs

### Web Application
- **Base URL:** `http://localhost:8000`
- **Login:** `http://localhost:8000/login`
- **Dashboard:** `http://localhost:8000/dashboard`
- **Register:** `http://localhost:8000/register`

### API Endpoints
- **Base URL:** `http://localhost:8000/api/v1`
- **API Login:** `POST http://localhost:8000/api/v1/auth/login`

---

## 📊 System Status (Last Check)

### Database Statistics
- **Users:** 8 accounts
- **Members:** 35 records
- **Classes:** 4 active classes
- **Sessions:** 32 class sessions
- **Attendance Records:** 0 (ready for marking)
- **Mentorships:** 30 total (14 active)
- **Messages:** 0 (message system ready)

### Test Results
- **Tests Passing:** 140/141 ✅
- **Tests Failing:** 1 (known issue: token logout test - Laravel Sanctum limitation)
- **Total Assertions:** 529
- **Success Rate:** 99.3%

---

## 🎯 Dashboard Access by Role

### Admin Dashboard Features
- Full system overview
- User management
- System health monitoring
- All statistics and reports
- Access to `/admin` route

### Pastor/Coordinator Dashboard Features
- My Classes overview
- My Sessions count
- My Members (pastors only)
- Active classes list
- Upcoming sessions
- Quick actions (Create Class, Add Member)
- Ministry statistics

### Member Dashboard Features
- Limited view with basic statistics
- View available classes
- Personal information

---

## 🔍 Quick Access Links (After Login)

### For Admins/Pastors/Coordinators:
- **Members:** `/members`
- **Classes:** `/classes`
- **Mentorships:** `/mentorships` (Admins/Pastors only)
- **Sessions:** `/classes/{id}/sessions`
- **Attendance:** `/sessions/{id}/attendance`
- **Statistics:** 
  - `/mentorships/statistics` (Admins/Pastors)
  - `/classes/{id}/statistics`

### For Members:
- **Profile:** `/profile`
- **Dashboard:** `/dashboard`
- **Classes View:** `/classes` (read-only)

---

## ⚠️ Important Notes

1. **Default Password:** All accounts use `password` as the default password. Change this in production!

2. **Email Verification:** Some accounts may require email verification. Check your mail settings.

3. **Role Permissions:**
   - **Admin:** Full access to everything
   - **Pastor:** Can manage classes, mentorships, and view members
   - **Coordinator:** Can manage classes but cannot manage mentorships
   - **Member:** Read-only access to most features

4. **Mentorships:** Only Admins and Pastors can create/manage mentorships. Coordinators have view-only access.

5. **Classes:** All mentors (Pastors/Coordinators) can create and manage their own classes.

---

## 🧪 Testing Credentials

Use the following credentials for automated testing:
- Email: Use any of the emails above
- Password: `password`

For API testing, use:
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@discipleship.local", "password": "password"}'
```

---

## 📝 Next Steps

1. Log in with any of the credentials above
2. Explore the dashboard based on your role
3. Create classes, sessions, and mark attendance
4. Set up mentorships for members
5. Generate reports and statistics

---

**Last Updated:** $(date)
**System Version:** Laravel 12
**PHP Version:** 8.1+
