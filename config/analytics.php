<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | These values define the default time periods used throughout the
    | application for analytics, reports, and dashboard statistics.
    | All values are in days unless otherwise specified.
    |
    */

    // Dashboard statistics
    'recent_activity_days' => env('ANALYTICS_RECENT_ACTIVITY_DAYS', 30),
    'recent_messages_days' => env('ANALYTICS_RECENT_MESSAGES_DAYS', 7),
    
    // Reports default periods
    'attendance_trends_months' => env('ANALYTICS_ATTENDANCE_TRENDS_MONTHS', 6),
    'dashboard_attendance_trends_months' => env('ANALYTICS_DASHBOARD_ATTENDANCE_TRENDS_MONTHS', 3),
];

