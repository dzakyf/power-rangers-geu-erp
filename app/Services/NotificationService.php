<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function makeNotification(string $title, string $content, string $type, string $senderId, string $recipientId, string $status = 'SENT', ?string $readAt = null, $metadata = null)
    {
        $expirationTime = Carbon::now()->add(3, 'day');

        $id = DB::table('notification')->insertGetId([
            'Title' => $title,
            'Content' => $content,
            'Expires_At' => $expirationTime,
            'Read_At' => $readAt,
            'Type' => $type,
            'Sender_Id' => $senderId,
            'Recipient_Id' => $recipientId,
            'Metadata' => $metadata,
            'Status' => $status
        ]);

        return $id;
    }
}
