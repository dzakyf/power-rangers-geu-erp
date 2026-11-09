<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\CompanyGuideline;
use App\Services\HCGA\CompanyGuidelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyGuidelineMobileController extends Controller
{
    protected $guidelineService;

    public function __construct(CompanyGuidelineService $guidelineService)
    {
        $this->guidelineService = $guidelineService;
    }

    /**
     * Mobile view for Visi Misi, Core Value, and Peraturan Perusahaan
     */
    public function mobileIndex(Request $request)
    {
        $user = Auth::user();
        $grouped = $this->guidelineService->getActiveGuidelinesGrouped();
        $activeTab = $request->query('tab', 'visi'); // default tab: visi, misi, core_value, peraturan

        return view('humanresources.company-guideline.mobile.index', [
            'user'       => $user,
            'grouped'    => $grouped,
            'activeTab'  => $activeTab,
        ]);
    }

    /**
     * Mobile view detail for a specific policy/guideline
     */
    public function mobileDetail($id)
    {
        $item = $this->guidelineService->findById($id);

        return view('humanresources.company-guideline.mobile.detail', [
            'item' => $item,
        ]);
    }
}

