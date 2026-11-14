<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification streaming
    |--------------------------------------------------------------------------
    |
    | SSE is enabled by default after the DB-backed stream rollout. Keep the
    | legacy notification-getall endpoint available as a polling fallback.
    |
    */
    'sse_enabled' => env('NOTIFICATION_SSE_ENABLED', true),
    'sse_stream_seconds' => (int) env('NOTIFICATION_SSE_STREAM_SECONDS', 30),
    'sse_poll_interval' => (float) env('NOTIFICATION_SSE_POLL_INTERVAL', 2),
    'sse_heartbeat_seconds' => (int) env('NOTIFICATION_SSE_HEARTBEAT_SECONDS', 15),
    'poll_interval' => (int) env('NOTIFICATION_POLL_SECONDS', 30),
];
