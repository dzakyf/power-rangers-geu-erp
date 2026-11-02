<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\MasterKodeposService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterKodeposController extends Controller
{
    protected MasterKodeposService $kodeposService;

    public function __construct(MasterKodeposService $kodeposService)
    {
        $this->kodeposService = $kodeposService;
    }

    /**
     * AJAX search endpoint for zipcode / city / province.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $q = (string) $request->input('q', '');
        $limit = (int) $request->input('limit', 20);

        $results = $this->kodeposService->search($q, $limit);
        return response()->json([
            'status' => 'success',
            'data'   => $results,
        ]);
    }

    /**
     * Get distinct provinces.
     *
     * @return JsonResponse
     */
    public function provinces(): JsonResponse
    {
        $provinces = $this->kodeposService->getProvinces();
        return response()->json([
            'status' => 'success',
            'data'   => $provinces,
        ]);
    }

    /**
     * Get cities by province.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cities(Request $request): JsonResponse
    {
        $province = (string) $request->input('province', '');
        $cities = $this->kodeposService->getCitiesByProvince($province);

        return response()->json([
            'status' => 'success',
            'data'   => $cities,
        ]);
    }

    /**
     * Get districts by province and city.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function districts(Request $request): JsonResponse
    {
        $province = (string) $request->input('province', '');
        $city = (string) $request->input('city', '');
        $districts = $this->kodeposService->getDistricts($province, $city);

        return response()->json([
            'status' => 'success',
            'data'   => $districts,
        ]);
    }

    /**
     * Get subdistricts (kelurahan/desa) and their zipcodes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function subDistricts(Request $request): JsonResponse
    {
        $province = (string) $request->input('province', '');
        $city = (string) $request->input('city', '');
        $district = (string) $request->input('district', '');
        $subDistricts = $this->kodeposService->getSubDistricts($province, $city, $district);

        return response()->json([
            'status' => 'success',
            'data'   => $subDistricts,
        ]);
    }

    /**
     * Get details by zipcode (for reverse lookup).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function byZipcode(Request $request): JsonResponse
    {
        $zipcode = (string) $request->input('zipcode', '');
        $details = $this->kodeposService->getDetailsByZipcode($zipcode);

        return response()->json([
            'status' => 'success',
            'data'   => $details,
        ]);
    }
}

