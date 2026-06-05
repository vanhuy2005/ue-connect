<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mentor System Configuration
    |--------------------------------------------------------------------------
    |
    | Controls safety limits, availability, and feature flags for the
    | Mentor Connection System.
    |
    */

    /**
     * Maximum number of mentor requests a student can send per day.
     */
    'student_daily_request_limit' => (int) env('MENTOR_STUDENT_DAILY_REQUEST_LIMIT', 5),

    /**
     * Maximum number of pending requests a student can have at once.
     */
    'student_pending_limit' => (int) env('MENTOR_STUDENT_PENDING_LIMIT', 10),

    /**
     * Maximum number of pending requests a single mentor can have at once.
     */
    'per_mentor_pending_limit' => (int) env('MENTOR_PER_MENTOR_PENDING_LIMIT', 20),

    /**
     * Default max pending requests for new mentor profiles.
     */
    'default_max_pending_requests' => (int) env('MENTOR_DEFAULT_MAX_PENDING_REQUESTS', 5),

    /**
     * Block duplicate pending requests to the same mentor.
     */
    'duplicate_pending_block' => (bool) env('MENTOR_DUPLICATE_PENDING_BLOCK', true),

    /**
     * Enable mentor feedback feature.
     */
    'enable_feedback' => (bool) env('MENTOR_ENABLE_FEEDBACK', true),

    /**
     * Allow students to be granted exceptional mentor status by admin.
     */
    'enable_student_exceptional_mentors' => (bool) env('MENTOR_ENABLE_STUDENT_EXCEPTIONAL_MENTORS', true),

    /**
     * Eligible role types that can apply for mentor access.
     */
    'eligible_role_contexts' => ['alumni', 'teacher'],
];
