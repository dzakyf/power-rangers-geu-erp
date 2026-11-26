<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function makeNotification(string $title, string $content, string $type, ?string $senderId = null, ?string $recipientId = null, ?string $status = 'SENT',  $metadata = null)
    {
        $expirationTime = Carbon::now()->add(3, 'day');
        $encodedMetadata = (is_array($metadata) || is_object($metadata))
            ? json_encode($metadata)
            : $metadata;

        $id = DB::table('notification')->insertGetId([
            'Title' => $title,
            'Content' => $content,
            'Expires_At' => $expirationTime,
            'Topic' => $type,
            'Sender_Id' => $senderId ?? null, // null = all
            'Recipient_Id' => $recipientId ?? null, // null = all
            'Metadata' => $encodedMetadata,
            'Status' => $status
        ]);

        return $id;
    }
}
