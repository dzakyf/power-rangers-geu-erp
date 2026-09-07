<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Support > Announcement (menu SI00251, MITRA module).
 *
 * The feature shipped (controller added 2026-03-02) without a migration for
 * its `announcement` table, so every create/publish attempt failed with
 * SQLSTATE[42S02] "Base table not found". These tests lock in the full CRUD
 * flow against the schema derived from the code's actual field usage:
 * ID, Title, Content, Is_Final, Final_Date, Created_Date, Created_By.
 *
 * Routes under test (routes/web.php:4881-4887, middleware web+log.access):
 *   POST partner-announcement-new-submit     -> partnerAnnouncementNewSubmit
 *   POST partner-announcement-detail-submit  -> partnerAnnouncementDetailSubmit
 *   GET  xml-partner-announcement            -> partnerAnnouncementXml
 *
 * The controllers echo "success" instead of returning a response, so output
 * is captured with ob_start()/ob_get_clean() (see UnknownPartnerNameGridTest).
 *
 * All fixture tables are InnoDB and the inserts happen inside the per-test
 * transaction opened by DatabaseTransactions, so nothing persists to the
 * dev database.
 */
class AnnouncementCrudTest extends TestCase
{
    use DatabaseTransactions;

    private const NEW_SUBMIT_URL = '/partner-announcement-new-submit';
    private const DETAIL_SUBMIT_URL = '/partner-announcement-detail-submit';
    private const XML_URL = '/xml-partner-announcement';

    /**
     * Fixture user, created lazily on first use. The `users` table is
     * MyISAM and does not participate in the per-test rollback, so we
     * never insert users - we only reuse an existing identity.
     *
     * @var User|null
     */
    private $user;

    private function testUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $this->user = $user;
    }

    private function submitNew(array $payload): void
    {
        ob_start();
        $response = $this->actingAs($this->testUser())->post(self::NEW_SUBMIT_URL, $payload);
        $body = (string) ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString('success', $body);
    }

    /** @test */
    public function test_publish_creates_final_announcement(): void
    {
        $this->submitNew([
            'Title' => 'AUD-TEST-PUBLISH',
            'Content' => 'Konten pengumuman final',
        ]);

        $row = DB::table('announcement')->where('Title', 'AUD-TEST-PUBLISH')->first();

        $this->assertNotNull($row, 'Published announcement must be stored');
        $this->assertSame(1, (int) $row->Is_Final);
        $this->assertNotNull($row->Final_Date, 'Published announcement must record Final_Date');
        $this->assertNotNull($row->Created_Date);
        $this->assertSame($this->testUser()->username, $row->Created_By);
        $this->assertSame('Konten pengumuman final', $row->Content);
    }

    /** @test */
    public function test_draft_stays_unpublished(): void
    {
        $this->submitNew([
            'Title' => 'AUD-TEST-DRAFT',
            'Content' => 'Konten draft',
            'Status' => 'Draft',
        ]);

        $row = DB::table('announcement')->where('Title', 'AUD-TEST-DRAFT')->first();

        $this->assertNotNull($row, 'Draft announcement must be stored');
        $this->assertSame(0, (int) $row->Is_Final);
        $this->assertNull($row->Final_Date, 'Draft announcement must not have Final_Date');
    }

    /** @test */
    public function test_detail_submit_updates_and_republishes(): void
    {
        DB::table('announcement')->insert([
            'Title' => 'AUD-TEST-UPD',
            'Content' => 'konten lama',
            'Is_Final' => 0,
            'Created_Date' => now(),
            'Created_By' => $this->testUser()->username,
        ]);
        $id = DB::table('announcement')->where('Title', 'AUD-TEST-UPD')->value('ID');

        ob_start();
        $response = $this->actingAs($this->testUser())->post(self::DETAIL_SUBMIT_URL, [
            'ID' => $id,
            'Title' => 'AUD-TEST-UPD-2',
            'Content' => 'konten baru',
        ]);
        $body = (string) ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString('success', $body);

        $row = DB::table('announcement')->where('ID', $id)->first();

        $this->assertSame('AUD-TEST-UPD-2', $row->Title);
        $this->assertSame('konten baru', $row->Content);
        $this->assertSame(1, (int) $row->Is_Final, 'Detail publish without Status=Draft must finalize');
        $this->assertNotNull($row->Final_Date);
    }

    /** @test */
    public function test_xml_list_shows_created_announcement(): void
    {
        DB::table('announcement')->insert([
            'Title' => 'AUD-TEST-XML',
            'Content' => 'konten untuk grid',
            'Is_Final' => 0,
            'Created_Date' => now(),
            'Created_By' => $this->testUser()->username,
        ]);

        $response = $this->actingAs($this->testUser())->get(self::XML_URL);

        $response->assertOk();

        $body = (string) $response->getContent();

        $this->assertStringContainsString('AUD-TEST-XML', $body);
        $this->assertStringContainsString('Draft', $body);
    }
}
