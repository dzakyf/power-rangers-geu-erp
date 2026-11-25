<?php

namespace Tests\Unit\CNS;

use App\Services\CNS\CnsCodeGenerator;
use Tests\TestCase;

class CnsCodeGeneratorTest extends TestCase
{
    /** @test */
    public function it_starts_from_001_when_empty(): void
    {
        $code = CnsCodeGenerator::nextFromExisting(
            [],
            '/^(\d+)\/VS-GEU\/XI\/2026$/',
            static function (int $seq) {
                return sprintf('%03d', $seq) . '/VS-GEU/XI/2026';
            }
        );

        $this->assertSame('001/VS-GEU/XI/2026', $code);
    }

    /** @test */
    public function it_uses_max_plus_one_when_gaps_exist(): void
    {
        $code = CnsCodeGenerator::nextFromExisting(
            ['001/STF-GEU/XI/2026', '003/STF-GEU/XI/2026'],
            '/^(\d+)\/STF-GEU\/XI\/2026$/',
            static function (int $seq) {
                return sprintf('%03d', $seq) . '/STF-GEU/XI/2026';
            }
        );

        $this->assertSame('004/STF-GEU/XI/2026', $code);
    }

    /** @test */
    public function it_handles_prefixed_coo_and_yearly_annex_and_sd_formats(): void
    {
        $coo = CnsCodeGenerator::nextFromExisting(
            ['COO-GEU/002/XI/2026'],
            '/^COO-GEU\/(\d+)\/XI\/2026$/',
            static function (int $seq) {
                return 'COO-GEU/' . sprintf('%03d', $seq) . '/XI/2026';
            }
        );
        $this->assertSame('COO-GEU/003/XI/2026', $coo);

        $annex = CnsCodeGenerator::nextFromExisting(
            ['001/ANX/X/2026', '002/ANX/XI/2026'],
            '/^(\d+)\/ANX\/[IVX]+\/2026$/',
            static function (int $seq) {
                return sprintf('%03d', $seq) . '/ANX/XI/2026';
            }
        );
        $this->assertSame('003/ANX/XI/2026', $annex);

        $sd = CnsCodeGenerator::nextFromExisting(
            ['2026000220261119'],
            '/^2026(\d{4})\d{8}$/',
            static function (int $seq) {
                return '2026' . sprintf('%04d', $seq) . '20261120';
            }
        );
        $this->assertSame('2026000320261120', $sd);

        $drn = CnsCodeGenerator::nextFromExisting(
            ['DRN-20261124-0004'],
            '/^DRN-20261124-(\d+)$/',
            static function (int $seq) {
                return 'DRN-20261124-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }
        );
        $this->assertSame('DRN-20261124-0005', $drn);

        $sj = CnsCodeGenerator::nextFromExisting(
            ['SJ-202611-0012'],
            '/^SJ-202611-(\d+)$/',
            static function (int $seq) {
                return 'SJ-202611-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }
        );
        $this->assertSame('SJ-202611-0013', $sj);
    }
}
