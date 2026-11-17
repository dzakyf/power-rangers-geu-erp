<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BdpIndexCardLayoutTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * BDP/MITRA menu landing pages whose primary content is a record grid/table.
     */
    private const INDEX_VIEWS = [
            'resources/views/approval/index.blade.php',
            'resources/views/approval/profit-sharing-index.blade.php',
            'resources/views/broadcast/index.blade.php',
            'resources/views/panduan/index.blade.php',
            'resources/views/partner/announcement.blade.php',
            'resources/views/partner/asset-management.blade.php',
            'resources/views/partner/assign-history.blade.php',
            'resources/views/partner/assign-sales.blade.php',
            'resources/views/partner/branding/approval-index.blade.php',
            'resources/views/partner/branding/marketing-index.blade.php',
            'resources/views/partner/branding/monitoring-index.blade.php',
            'resources/views/partner/branding/promotion-index.blade.php',
            'resources/views/partner/business-facility-detail-booth.blade.php',
            'resources/views/partner/business-facility-detail-mobil.blade.php',
            'resources/views/partner/business-lists.blade.php',
            'resources/views/partner/canceled-leads.blade.php',
            'resources/views/partner/capex/index.blade.php',
            'resources/views/partner/category/index.blade.php',
            'resources/views/partner/compliance/legal-index.blade.php',
            'resources/views/partner/compliance/tax-index.blade.php',
            'resources/views/partner/container/index.blade.php',
            'resources/views/partner/delivery-order/index.blade.php',
            'resources/views/partner/fresh-leads.blade.php',
            'resources/views/partner/history-leads.blade.php',
            'resources/views/partner/master-audit-category.blade.php',
            'resources/views/partner/master-audit-parameter.blade.php',
            'resources/views/partner/master-sop.blade.php',
            'resources/views/partner/master-stock-price.blade.php',
            'resources/views/partner/master-training.blade.php',
            'resources/views/partner/mitra-profit-sharing-lk-monthly.blade.php',
            'resources/views/partner/order-equipment.blade.php',
            'resources/views/partner/outstanding-leads.blade.php',
            'resources/views/partner/outstock/index.blade.php',
            'resources/views/partner/performance/index.blade.php',
            'resources/views/partner/pickup/index.blade.php',
            'resources/views/partner/pickup/routing/index.blade.php',
            'resources/views/partner/poo/index.blade.php',
            'resources/views/partner/profit-sharing.blade.php',
            'resources/views/partner/profit-sharing/management-fee.blade.php',
            'resources/views/partner/profit-sharing/overview-mitra.blade.php',
            'resources/views/partner/profit-sharing/overview2.blade.php',
            'resources/views/partner/profit-sharing/royalty-history-index.blade.php',
            'resources/views/partner/reassign-sales.blade.php',
            'resources/views/partner/renewal/termination-approval.blade.php',
            'resources/views/partner/renewal/termination.blade.php',
            'resources/views/partner/renewal/transfer-membership-index.blade.php',
            'resources/views/partner/sales/aftersales-handling-complain.blade.php',
            'resources/views/partner/sales/aftersales-profit-mitra.blade.php',
            'resources/views/partner/sales/master-target.blade.php',
            'resources/views/partner/sales/pipeline-leads.blade.php',
            'resources/views/partner/sales/ratio-pipeline-deal.blade.php',
            'resources/views/partner/sales/ratio-pipeline-leads.blade.php',
            'resources/views/partner/sales/team-performance.blade.php',
            'resources/views/partner/sales/tiering.blade.php',
            'resources/views/partner/survey/approval-index.blade.php',
            'resources/views/partner/survey/form/index.blade.php',
            'resources/views/partner/survey/index.blade.php',
            'resources/views/partner/ticket-history.blade.php',
            'resources/views/partner/ticket-open.blade.php',
            'resources/views/partner/training/approval.blade.php',
            'resources/views/partner/training/attend.blade.php',
            'resources/views/partner/training/certificate-mentor.blade.php',
            'resources/views/partner/training/certificate.blade.php',
            'resources/views/partner/training/library.blade.php',
            'resources/views/partner/training/list.blade.php',
            'resources/views/partner/training/master-material.blade.php',
            'resources/views/partner/training/master-satisfication.blade.php',
            'resources/views/partner/training/report-mitra.blade.php',
            'resources/views/partner/training/satisfication-manager.blade.php',
            'resources/views/partner/training/satisfication.blade.php',
            'resources/views/partner/training/training.blade.php',
            'resources/views/partner/valuation/list.blade.php',
            'resources/views/partner/valuation/summary.blade.php',
            'resources/views/partner/warehouse/audit.blade.php',
            'resources/views/partner/warehouse/corrective.blade.php',
            'resources/views/partner/warehouse/facility/index.blade.php',
            'resources/views/partner/warehouse/journal.blade.php',
            'resources/views/partner/warehouse/list.blade.php',
            'resources/views/partner/warehouse/monitoring.blade.php',
            'resources/views/partner/warehouse/stock-opname.blade.php',
            'resources/views/partner/work-order-status/index.blade.php',
            'resources/views/partner/work-order/index.blade.php',
            'resources/views/projection/index.blade.php',
            'resources/views/renewal/approval.blade.php',
            'resources/views/renewal/renewal.blade.php',
            'resources/views/transfer-membership/index.blade.php',
            'resources/views/uco/inbound.blade.php',
    ];

    /** @test */
    public function every_bdp_index_table_is_wrapped_in_a_titled_white_card(): void
    {
        $failures = [];

        foreach (self::INDEX_VIEWS as $relativePath) {
            $path = base_path($relativePath);
            if (!file_exists($path)) {
                $failures[] = "$relativePath: file not found";
                continue;
            }

            $contents = file_get_contents($path);

            if (strpos($contents, 'JudulContent') !== false) {
                $failures[] = "$relativePath: still uses separated JudulContent bar";
            }

            if (strpos($contents, 'bdp-index-title') === false && strpos($contents, 'partner.partials.index-title') === false) {
                $failures[] = "$relativePath: missing bdp-index-title";
            }

            if (strpos($contents, 'class="card') === false && strpos($contents, "class='card") === false) {
                $failures[] = "$relativePath: missing white card wrapper";
            }

            $titlePositions = array_filter([
                strpos($contents, 'partner.partials.index-title'),
                strpos($contents, 'bdp-index-title'),
            ], function ($position) {
                return $position !== false;
            });

            $scriptPos = stripos($contents, '<script');
            $head = $scriptPos === false ? $contents : substr($contents, 0, $scriptPos);
            $tablePositions = [];

            if (preg_match_all('/<div[^>]+id="[^"]*(Grid|List|Table)[^"]*"/i', $head, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    if (stripos($match[0], 'gridTip') === false) {
                        $tablePositions[] = $match[1];
                    }
                }
            }

            $htmlTablePos = stripos($head, '<table');
            if ($htmlTablePos !== false) {
                $tablePositions[] = $htmlTablePos;
            }

            if ($tablePositions && (!$titlePositions || min($titlePositions) > min($tablePositions))) {
                $failures[] = "$relativePath: page title is not above the primary table";
            }
        }

        $this->assertSame([], $failures, implode(PHP_EOL, $failures));
    }

    /** @test */
    public function representative_bdp_index_routes_render_with_the_new_card_title(): void
    {
        $user = User::query()->where('Priv_ID', 'ADM')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        $routes = [
            '/partner-survey-index',
            '/master-container',
            '/partner-outstock',
            '/partner-business-lists',
            '/partner-work-order',
            '/partner-training-list',
            '/partner-valuation',
            '/partner-delivery-order',
            '/partner-assign-history',
            '/partner-sales-tiering',
            '/partner-branding-marketing-index',
            '/partner-profit-sharing-royalty-index',
            '/partner-compliance-tax-index',
            '/partner-transfer-membership-index',
            '/partner-uco-instock',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)
                ->get($route)
                ->assertOk()
                ->assertSee('bdp-index-title', false);
        }
    }
}
