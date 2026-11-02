<?php

namespace App\Services\GA;

use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GaNotificationService
{
    protected $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function notifyUser($userId, string $title, string $content, string $type = 'ga_ticket', array $metadata = []): void
    {
        $userId = (int) $userId;
        if ($userId < 1) {
            return;
        }

        $this->safeNotify($userId, $title, $content, $type, $metadata);
    }

    public function notifyUsers(array $userIds, string $title, string $content, string $type = 'ga_ticket', array $metadata = []): void
    {
        $seen = [];
        foreach ($userIds as $userId) {
            $userId = (int) $userId;
            if ($userId < 1 || isset($seen[$userId])) {
                continue;
            }
            $seen[$userId] = true;
            $this->safeNotify($userId, $title, $content, $type, $metadata);
        }
    }

    public function notifyTeam($teamId, string $title, string $content, string $type = 'ga_ticket', array $metadata = []): void
    {
        $teamId = (int) $teamId;
        if ($teamId < 1) {
            return;
        }

        $userIds = DB::table('ga_team_member')
            ->where('Team_ID', $teamId)
            ->where('Status', 'ACTIVE')
            ->whereNotNull('User_Id')
            ->pluck('User_Id')
            ->all();

        $this->notifyUsers($userIds, $title, $content, $type, $metadata);
    }

    protected function safeNotify(int $userId, string $title, string $content, string $type, array $metadata): void
    {
        try {
            $this->notifications->makeNotification(
                mb_substr($title, 0, 100),
                $content,
                $type,
                null,
                (string) $userId,
                'SENT',
                json_encode($metadata)
            );
        } catch (\Throwable $exception) {
            Log::warning('Gagal menulis notifikasi GA', [
                'message' => $exception->getMessage(),
                'recipient' => $userId,
                'title' => $title,
            ]);
        }

        $this->sendEmail($userId, $title, $content);
    }

    protected function sendEmail(int $userId, string $title, string $content): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (!config('ga.email_notifications', false)) {
            return;
        }

        try {
            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user || empty($user->email)) {
                return;
            }

            Mail::raw($content, function ($message) use ($user, $title) {
                $message->to($user->email)->subject(mb_substr($title, 0, 150));
            });
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengirim email notifikasi GA', [
                'message' => $exception->getMessage(),
                'recipient' => $userId,
                'title' => $title,
            ]);
        }
    }
}
