<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\RecruitmentCandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecruitmentCandidateBatchController extends Controller
{
    protected RecruitmentCandidateService $candidateService;

    public function __construct(RecruitmentCandidateService $candidateService)
    {
        $this->candidateService = $candidateService;
    }

    /**
     * Save columns preference for recruitment-data grid.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveColumns(Request $request): JsonResponse
    {
        $username = Auth::user()->username ?? '';
        $colsParam = (string) $request->input('cols', '');

        $result = $this->candidateService->saveColumnsPreference($username, $colsParam);
        return response()->json($result);
    }

    /**
     * Batch create user accounts for selected candidates.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batchCreateUser(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        $result = $this->candidateService->batchCreateUserAccounts((array) $ids);
        return response()->json($result);
    }

    /**
     * Batch update candidate position and link to Requisition (PTK).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batchUpdatePosition(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        $reqTaskId = $request->input('requisition_task_id');
        $customPosition = (string) $request->input('custom_position', '');

        $result = $this->candidateService->batchUpdatePosition((array) $ids, $reqTaskId, $customPosition);
        return response()->json($result);
    }
}
