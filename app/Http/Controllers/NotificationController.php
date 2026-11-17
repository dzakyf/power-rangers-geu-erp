<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    protected $userService;
    protected $notificationService;

    public function __construct(UserService $u, NotificationService $n)
    {
        $this->userService = $u;
        $this->notificationService = $n;
    }

    public function getAll()
    {
        $user = $this->userService->getCurrentSessionUserInfo();

        if (!$user) {
            return response()->json(['data' => []], 401);
        }

        $data = DB::table('notification')
            ->where(function ($query) use ($user) {
                $query->where('Recipient_Id', (string) $user->id)
                    ->orWhereNull('Recipient_Id');
            })
            ->where(function ($query) {
                $query->whereNull('Expires_At')
                    ->orWhere('Expires_At', '>', Carbon::now());
            })
            ->orderBy('ID', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    /**
     * Short-lived Server-Sent Events stream. The browser reconnects with
     * Last-Event-ID, so the Apache worker is released after each window.
     */
    public function stream(Request $request)
    {
        if (!config('notifications.sse_enabled', true)) {
            abort(404);
        }

        $user = $this->userService->getCurrentSessionUserInfo();
        if (!$user) {
            return response('Unauthorized', 401, ['Content-Type' => 'text/plain']);
        }

        $userId = (string) $user->id;
        $lastId = (int) ($request->header('Last-Event-ID') ?: $request->query('last_id', 0));
        $streamSeconds = max(1, (int) config('notifications.sse_stream_seconds', 30));
        $pollInterval = max(0.05, (float) config('notifications.sse_poll_interval', 2));
        $heartbeatSeconds = max(5, (int) config('notifications.sse_heartbeat_seconds', 15));

        // Do not hold the session lock while the stream is open.
        if ($request->hasSession()) {
            $request->session()->save();
        }

        $testing = app()->runningUnitTests();

        return response()->stream(function () use ($userId, $lastId, $streamSeconds, $pollInterval, $heartbeatSeconds, $testing) {
            @ini_set('zlib.output_compression', '0');
            @ini_set('output_buffering', '0');
            @ini_set('implicit_flush', '1');
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @set_time_limit($streamSeconds + 10);
            ob_implicit_flush(true);

            echo "retry: 5000\n\n";
            if (!$testing) {
                @ob_flush();
            }
            @flush();

            $deadline = microtime(true) + $streamSeconds;
            $nextHeartbeat = microtime(true) + $heartbeatSeconds;
            $currentId = $lastId;

            while (!connection_aborted() && microtime(true) < $deadline) {
                $rows = DB::table('notification')
                    ->where(function ($query) use ($userId) {
                        $query->where('Recipient_Id', $userId)
                            ->orWhereNull('Recipient_Id');
                    })
                    ->where('ID', '>', $currentId)
                    ->where(function ($query) {
                        $query->whereNull('Expires_At')
                            ->orWhere('Expires_At', '>', Carbon::now());
                    })
                    ->orderBy('ID')
                    ->get();

                foreach ($rows as $row) {
                    $currentId = (int) $row->ID;
                    echo "id: {$row->ID}\n";
                    echo "event: notification\n";
                    echo 'data: ' . json_encode($row) . "\n\n";
                    if (!$testing) {
                        @ob_flush();
                    }
                    @flush();
                }

                if (microtime(true) >= $nextHeartbeat) {
                    echo ": ping\n\n";
                    if (!$testing) {
                        @ob_flush();
                    }
                    @flush();
                    $nextHeartbeat = microtime(true) + $heartbeatSeconds;
                }

                usleep((int) ($pollInterval * 1000000));
            }

            echo ": stream-end\n\n";
            if (!$testing) {
                @ob_flush();
            }
            @flush();
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function getById()
    {
        return response()->json(['data' => []], 200);
    }

    public function markAsRead(Request $r)
    {
        $user = $this->userService->getCurrentSessionUserInfo();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id = $r->input('id', $r->query('id'));
        if (!$id) {
            return response()->json(['message' => 'Notification ID is required'], 400);
        }

        $notification = DB::table('notification')->where('ID', $id)->first();
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        if ($notification->Recipient_Id !== null
            && (string) $notification->Recipient_Id !== (string) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $lastRow = DB::table('notification')->where('ID', '=', $id)->update([
            'Status' => 'READ',
            'Read_At' => Carbon::now(),
        ]);

        return response()->json([
            'message' => "success. $lastRow marked notification as read."
        ], 200);
    }

    /**TODO:
     * implement blast WA, email, atau yang lain
     */
    public function reminderContractExpiration()
    {
        $remindAt = Carbon::now()->addMonths(3)->toDateString();
        $all = DB::table('opportunity_partner')->select('*')->whereDate('contract_end_at', '=', $remindAt)->get();

        foreach ($all as $d) {
            $this->notificationService->makeNotification("Reminder Membership", "Pemberitahuan bahwa masa aktif membership anda: $d->full_name tersisa 3 bulan lagi.", "membership", null, $d->user_row_id);
        }

        return response()->json(['message' => 'success']);
    }

    public function sendRoyaltyNotification(Request $r)
    {
        $month = Carbon::now()->format('m');
        $now = Carbon::now();

        $this->notificationService->makeNotification("Pembayaran Bagi Hasil Berhasil", "Pembayaran bagi hasil untuk periode $month telah berhasil ditransfer.Nominal: Rp XXXXX, Tanggal Transfer: $now, Silakan cek bukti transfer pada menu riwayat pembayaran.", "membership");

        return response()->json(['message' => 'success']);
    }
}
