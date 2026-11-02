<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GA notification email
    |--------------------------------------------------------------------------
    | In-app notifications are always written to the `notification` table.
    | Email is attempted through Laravel Mail only when GA_EMAIL_NOTIFICATIONS
    | is explicitly set to true in .env and the application is not running
    | under the testing environment. Defaulted to false to prevent HTTP timeout
    | when SMTP is unreachable or unauthenticated in local development.
    */
    'email_notifications' => env('GA_EMAIL_NOTIFICATIONS', false),
];
