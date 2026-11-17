<?php

namespace Tests\Feature\Notification;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationStreamTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function otherUser(): User
    {
        return User::where('Position', 'SALES')->firstOrFail();
    }

    private function seedNotification(array $overrides = []): int
    {
        return DB::table('notification')->insertGetId(array_merge([
            'Title'        => 'Test notification ' . uniqid(),
            'Content'      => 'Test content',
            'Expires_At'   => Carbon::now()->addDays(3),
            'Created_At'   => now(),
            'Topic'        => 'test',
            'Sender_Id'    => null,
            'Recipient_Id' => (string) $this->admin()->id,
            'Metadata'     => null,
            'Status'       => 'SENT',
        ], $overrides));
    }

    /** @test */
    public function get_all_returns_own_and_broadcast_notifications_but_not_expired_or_other_users(): void
    {
        $own = $this->seedNotification(['Title' => 'Own notification']);
        $broadcast = $this->seedNotification([
            'Title'        => 'Broadcast notification',
            'Recipient_Id' => null,
        ]);
        $other = $this->seedNotification([
            'Title'        => 'Other user notification',
            'Recipient_Id' => (string) $this->otherUser()->id,
        ]);
        $expired = $this->seedNotification([
            'Title'      => 'Expired notification',
            'Expires_At' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson('/notification-getall')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('ID')->map(fn ($id) => (int) $id)->all();
        $this->assertContains($own, $ids);
        $this->assertContains($broadcast, $ids);
        $this->assertNotContains($other, $ids);
        $this->assertNotContains($expired, $ids);
    }

    /** @test */
    public function mark_as_read_is_limited_to_the_recipient(): void
    {
        $own = $this->seedNotification();
        $foreign = $this->seedNotification([
            'Recipient_Id' => (string) $this->otherUser()->id,
        ]);

        $this->actingAs($this->admin())
            ->postJson('/notification-read', ['id' => $own])
            ->assertOk();

        $this->assertSame('READ', DB::table('notification')->where('ID', $own)->value('Status'));

        $this->actingAs($this->admin())
            ->postJson('/notification-read', ['id' => $foreign])
            ->assertStatus(403);

        $this->assertSame('SENT', DB::table('notification')->where('ID', $foreign)->value('Status'));
    }

    /** @test */
    public function sse_stream_emits_notification_events(): void
    {
        config([
            'notifications.sse_enabled'          => true,
            'notifications.sse_stream_seconds'   => 0.25,
            'notifications.sse_poll_interval'    => 0.05,
            'notifications.sse_heartbeat_seconds'=> 10,
        ]);

        $title = 'SSE event ' . uniqid();
        $this->seedNotification(['Title' => $title]);

        $response = $this->actingAs($this->admin())
            ->get('/notification-stream?last_id=0')
            ->assertOk();

        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('event: notification', $content);
        $this->assertStringContainsString($title, $content);
    }

    /** @test */
    public function sse_endpoint_is_disabled_by_config(): void
    {
        config(['notifications.sse_enabled' => false]);

        $this->actingAs($this->admin())
            ->get('/notification-stream?last_id=0')
            ->assertNotFound();
    }

    /** @test */
    public function notification_stream_indexes_exist(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM notification'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        $this->assertContains('notification_recipient_id_idx', $indexes);
        $this->assertContains('notification_status_id_idx', $indexes);
        $this->assertContains('notification_expires_at_idx', $indexes);
    }

    /** @test */
    public function adminor_and_legacy_layouts_load_the_shared_notification_client(): void
    {
        $this->actingAs($this->admin())
            ->get('/partner-approval-index')
            ->assertOk()
            ->assertSee('js/notification-stream.js', false)
            ->assertSee('notification-stream-url', false);

        $this->actingAs($this->admin())
            ->get('/home-sales')
            ->assertOk()
            ->assertSee('js/notification-stream.js', false)
            ->assertSee('notification-stream-url', false);
    }
}
