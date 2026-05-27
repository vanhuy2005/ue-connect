<?php

return [
    'identity' => [
        'student_email_domains' => array_values(array_filter(array_map(
            fn ($domain) => strtolower(ltrim(trim($domain), '@')),
            explode(',', env('AUTH_STUDENT_EMAIL_DOMAINS', 'student.hcmue.edu.vn'))
        ))),

        'staff_email_domains' => array_values(array_filter(array_map(
            fn ($domain) => strtolower(ltrim(trim($domain), '@')),
            explode(',', env('AUTH_STAFF_EMAIL_DOMAINS', 'hcmue.edu.vn,teacher.hcmue.edu.vn'))
        ))),

        'alumni_personal_email_allowed' => filter_var(env('AUTH_ALUMNI_PERSONAL_EMAIL_ALLOWED', true), FILTER_VALIDATE_BOOLEAN),
        'external_mentor_personal_email_allowed' => filter_var(env('AUTH_EXTERNAL_MENTOR_PERSONAL_EMAIL_ALLOWED', false), FILTER_VALIDATE_BOOLEAN),
    ],
];
