<?php
// config/campaign.php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Sending Configuration
    |--------------------------------------------------------------------------
    */

    'email' => [
        // Number of customers to process in each chunk
        'chunk_size' => env('CAMPAIGN_CHUNK_SIZE', 100),

        // Update progress in database every X emails
        'progress_update_interval' => env('CAMPAIGN_PROGRESS_INTERVAL', 10),

        // Throttle email sending (seconds between emails)
        'throttle_seconds' => env('CAMPAIGN_THROTTLE_SECONDS', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Configuration
    |--------------------------------------------------------------------------
    */

    'job' => [
        // Maximum number of retry attempts for failed jobs
        'max_tries' => env('CAMPAIGN_JOB_TRIES', 3),

        // Maximum seconds a job can run before timing out
        'timeout' => env('CAMPAIGN_JOB_TIMEOUT', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'customers_per_page' => env('CUSTOMERS_PER_PAGE', 50),
        'groups_per_page' => env('GROUPS_PER_PAGE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Placeholders for Email Templates
    |--------------------------------------------------------------------------
    */

    'placeholders' => [
        '{{first_name}}' => 'Customer first name',
        '{{last_name}}' => 'Customer last name',
        '{{full_name}}' => 'Customer full name',
        '{{email}}' => 'Customer email',
        '{{sex}}' => 'Customer sex',
        '{{birth_date}}' => 'Customer birth date',
    ],

];
