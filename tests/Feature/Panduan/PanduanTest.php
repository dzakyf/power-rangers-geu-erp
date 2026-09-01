<?php

namespace Tests\Feature\Panduan;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration tests for the Panduan (Support) module fixes:
 *   - detail() shows only guide-topic files with correct /guide/ URLs
 *   - submit() returns JSON success (create page redirects to panduan-index)
 *   - delete() removes the guide and its guide-topic meta_files
 */
class PanduanTest extends TestCase
{
    use DatabaseTransactions;

    private $uploadedFile = null;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    protected function tearDown(): void
    {
        if ($this->uploadedFile && is_file($this->uploadedFile)) {
            @unlink($this->uploadedFile);
        }
        parent::tearDown();
    }

    private function seedGuide(string $title = 'Akses CCTV'): int
    {
        return DB::table('guide')->insertGetId([
            'Title'          => $title,
            'Short_Desc'     => 'Panduan test',
            'Version'        => '1.0',
            'Effective_Date' => now()->toDateString(),
            'Created_At'     => now(),
            'Changelog'      => 'v1',
        ]);
    }

    private function seedMetaFile(int $guideId, string $topic, string $path): int
    {
        return DB::table('meta_files')->insertGetId([
            'Filename'    => basename($path),
            'Path'        => $path,
            'Extension'   => 'pdf',
            'Description' => 'file',
            'Topic'       => $topic,
            'Map_To'      => $guideId,
            'Created_At'  => now(),
        ]);
    }

    // ------------------------------------------------------------------
    // 1. Detail: guide-topic only + correct URL
    // ------------------------------------------------------------------

    /** @test */
    public function detail_shows_only_guide_files_with_correct_url()
    {
        $guideId = $this->seedGuide();
        $this->seedMetaFile($guideId, 'guide', '00-guide-file.pdf');
        $this->seedMetaFile($guideId, 'settlement', '00-decoy-settlement.pdf'); // decoy, must be excluded

        $html = $this->actingAs($this->admin())
            ->get('/panduan-detail?ID=' . $guideId)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('00-guide-file.pdf', $html);
        $this->assertStringNotContainsString('00-decoy-settlement.pdf', $html);
        $this->assertStringContainsString('guide/00-guide-file.pdf', $html);
    }

    // ------------------------------------------------------------------
    // 2. Submit: JSON success contract
    // ------------------------------------------------------------------

    /** @test */
    public function submit_returns_json_success_and_stores_guide_file()
    {
        $file = UploadedFile::fake()->create('panduan-test.pdf', 10, 'application/pdf');

        $res = $this->actingAs($this->admin())
            ->post('/panduan-submit', [
                'Title'          => '1', // Akses CCTV
                'Short_Desc'     => 'Panduan ERP test',
                'Version'        => '2.1',
                'Effective_Date' => now()->toDateString(),
                'Changelog'      => 'v2',
                'Doc'            => $file,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success', 'redirect' => 'panduan-index'])
            ->json();

        $guide = DB::table('guide')->where('ID', $res['id'])->first();
        $this->assertNotNull($guide);
        $this->assertEquals('Akses CCTV', $guide->Title);

        $meta = DB::table('meta_files')->where('Map_To', $res['id'])->where('Topic', 'guide')->first();
        $this->assertNotNull($meta);
        $this->assertFileExists(public_path('guide/' . $meta->Path));
        $this->uploadedFile = public_path('guide/' . $meta->Path);
    }

    // ------------------------------------------------------------------
    // 3. Delete: removes guide + its guide-topic files
    // ------------------------------------------------------------------

    /** @test */
    public function delete_removes_guide_and_its_files()
    {
        $guideId = $this->seedGuide();
        $this->seedMetaFile($guideId, 'guide', '00-del-file.pdf');
        $decoyId = $this->seedMetaFile($guideId, 'settlement', '00-del-decoy.pdf');

        $this->actingAs($this->admin())
            ->post('/panduan-delete', ['ID' => $guideId])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull(DB::table('guide')->where('ID', $guideId)->first());
        $this->assertEquals(0, DB::table('meta_files')->where('Map_To', $guideId)->where('Topic', 'guide')->count());
        $this->assertNotNull(DB::table('meta_files')->where('ID', $decoyId)->first(), 'non-guide files untouched');
    }
}
