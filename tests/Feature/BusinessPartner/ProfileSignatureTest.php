<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The logged-in account can draw a signature once; it is stored as a PNG
 * (meta_files Topic 'user-signature', Map_To = user id) and reused in
 * generated documents.
 */
class ProfileSignatureTest extends TestCase
{
    use DatabaseTransactions;

    /** 1x1 PNG. */
    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function testUser(): User
    {
        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }
        return $user;
    }

    /** @test */
    public function saving_a_signature_stores_it_and_returns_its_url(): void
    {
        $user = $this->testUser();

        $response = $this->actingAs($user)->post('/partner-profile-update-signature', [
            'signature' => self::PNG,
        ]);

        $response->assertOk();
        $this->assertSame('success', $response->json('message'));

        $meta = DB::table('meta_files')
            ->where('Topic', 'user-signature')
            ->where('Map_To', (string) $user->id)
            ->orderByDesc('ID')
            ->first();

        $this->assertNotNull($meta, 'The signature must be recorded in meta_files');
        $this->assertSame('png', strtolower($meta->Extension));
        $this->assertFileExists(public_path('filevendor/'.$meta->Path));
        $this->assertNotEmpty($response->json('url'));
    }

    /** @test */
    public function profile_data_exposes_the_saved_signature_url(): void
    {
        $user = $this->testUser();

        $this->actingAs($user)->post('/partner-profile-update-signature', ['signature' => self::PNG])->assertOk();

        $response = $this->actingAs($user)->get('/partner-profile-data');
        $response->assertOk();

        $this->assertNotEmpty($response->json('data.signature_url'));
    }

    /** @test */
    public function a_missing_signature_is_rejected(): void
    {
        $user = $this->testUser();

        $this->actingAs($user)
            ->post('/partner-profile-update-signature', ['signature' => ''])
            ->assertStatus(422);
    }
}
