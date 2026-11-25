<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\EmployeeDetailService;
use App\Services\HCGA\EmployeeGridService;
use App\Services\HCGA\EmploymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDetailController extends Controller
{
    /** @var EmployeeGridService */
    private $gridService;

    /** @var EmployeeDetailService */
    private $detailService;

    /** @var EmploymentService */
    private $employmentService;

    public function __construct(
        EmployeeGridService $gridService,
        EmployeeDetailService $detailService,
        EmploymentService $employmentService
    ) {
        $this->gridService = $gridService;
        $this->detailService = $detailService;
        $this->employmentService = $employmentService;
    }

    private function authorizeView(): void
    {
        abort_unless(
            $this->gridService->canViewEmployeeLists(Auth::user()),
            403,
            'Anda tidak memiliki akses melihat detail karyawan.'
        );
    }

    private function authorizeEdit(): void
    {
        abort_unless(
            $this->gridService->canEditEmployeeLists(Auth::user()),
            403,
            'Anda tidak memiliki akses mengubah data karyawan.'
        );
    }

    private function resolveKey(Request $request): string
    {
        return trim((string) (
            $request->input('Task_ID')
            ?: ($request->input('ID') ?: $request->input('NIK', ''))
        ));
    }

    private function xmlResponse(string $xml)
    {
        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    // --- Tab Views ---
    public function index() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail'); }
    public function data() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-data'); }
    public function other(Request $request)
    {
        $this->authorizeView();
        $taskId = $this->resolveKey($request);
        $medical = $this->detailService->getOtherMedicalData($taskId);

        return view('humanresources.employment.employee-lists-detail-other', compact('taskId', 'medical'));
    }
    public function insurance() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-insurance'); }
    public function family() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-family'); }
    public function education() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-education'); }
    public function skill() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-skill'); }
    public function kjk() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-kjk'); }
    public function document() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-document'); }
    public function history() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-history'); }
    public function recruitment() { $this->authorizeView(); return view('humanresources.employment.employee-lists-detail-recruitment'); }

    // --- Update Endpoints ---
    public function submitData(Request $request)
    {
        $this->authorizeEdit();
        try {
            $this->employmentService->updateEmployeeDetailData($request->all());
            return response('success', 200);
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 422);
        }
    }

    public function saveOtherMedical(Request $request)
    {
        $this->authorizeEdit();
        $request->validate([
            'Task_ID' => 'required|string|max:100',
            'Height' => 'nullable|string|max:20',
            'Weight' => 'nullable|string|max:20',
            'Glasses' => 'nullable|in:Yes,No',
            'Smoker' => 'nullable|in:Yes,No',
            'Longtime' => 'nullable|in:Yes,No',
            'Longtime_Desc' => 'nullable|string|max:255',
        ]);

        $this->detailService->saveOtherMedicalInfo((string) $request->input('Task_ID'), $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Medical & Physical Information berhasil disimpan.',
        ]);
    }

    public function updateDriving(Request $request)
    {
        $this->authorizeEdit();
        $rawBody = $this->extractRawBody($request);
        $xml = $this->detailService->processDrivingDataProcessor($rawBody, $this->resolveKey($request));
        return $this->xmlResponse($xml);
    }

    public function updateHealth(Request $request)
    {
        $this->authorizeEdit();
        $rawBody = $this->extractRawBody($request);
        $xml = $this->detailService->processHealthDataProcessor($rawBody, $this->resolveKey($request));
        return $this->xmlResponse($xml);
    }

    public function updateFamily(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processFamilyDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateContactPoints(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processContactPointsDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateInsurance(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processInsuranceDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateOrganization(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processOrganizationDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateAcademic(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processAcademicDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateInformal(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processInformalDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateExperience(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processExperienceDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateOtherSkill(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processOtherSkillDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateAward(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processAwardDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function updateLanguage(Request $request)
    {
        $this->authorizeEdit();
        return $this->xmlResponse($this->detailService->processLanguageDataProcessor($this->extractRawBody($request), $this->resolveKey($request)));
    }

    public function photoModal(Request $request)
    {
        $this->authorizeEdit();
        $taskId = $this->resolveKey($request);

        return view('humanresources.employment.uploadfoto', compact('taskId'));
    }

    public function uploadPhoto(Request $request)
    {
        $this->authorizeEdit();
        $request->validate([
            'Task_ID' => 'required|string|max:100',
            'photo' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $filename = $this->detailService->uploadEmployeePhoto(
            (string) $request->input('Task_ID'),
            $request->file('photo'),
            (string) (Auth::user()->username ?? '')
        );

        return response()->json([
            'status' => 'success',
            'filename' => $filename,
            'url' => asset('doc_recruitment/photo/' . $filename),
            'message' => 'Foto karyawan berhasil diperbarui.',
        ]);
    }

    public function documentForm(Request $request)
    {
        $this->authorizeEdit();
        $taskId = $this->resolveKey($request);
        $categories = array_keys($this->detailService->getDocumentCategoryMap());

        return view('humanresources.employment.employee-lists-detail-document-form', compact('taskId', 'categories'));
    }

    public function uploadDocument(Request $request)
    {
        $this->authorizeEdit();
        $request->validate([
            'Task_ID' => 'required|string|max:100',
            'category' => 'required|string|max:30',
            'description' => 'nullable|string|max:255',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
        ]);

        try {
            $result = $this->detailService->uploadEmployeeDocument(
                (string) $request->input('Task_ID'),
                (string) $request->input('category'),
                $request->file('document_file'),
                (string) $request->input('description', ''),
                (string) (Auth::user()->username ?? '')
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
                'message' => 'Dokumen karyawan berhasil diunggah.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function deleteDocument(Request $request)
    {
        $this->authorizeEdit();
        $request->validate([
            'Task_ID' => 'required|string|max:100',
            'category' => 'required|string|max:30',
        ]);

        try {
            $this->detailService->deleteEmployeeDocument(
                (string) $request->input('Task_ID'),
                (string) $request->input('category'),
                (string) (Auth::user()->username ?? '')
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Dokumen karyawan berhasil dihapus.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function extractRawBody(Request $request): string
    {
        $rawBody = (string) $request->getContent();
        if ($rawBody === '' && !empty($request->post())) {
            $rawBody = http_build_query($request->post());
        }
        return $rawBody;
    }

    public function updateSubModulePlaceholder(Request $request)
    {
        $this->authorizeEdit();
        return response('<?xml version="1.0" encoding="UTF-8"?><data/>', 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    // --- XML Sub-Grid Endpoints ---
    public function xmlDocument(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getDocumentsXml($this->resolveKey($request))); }
    public function xmlHistory(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getHistoryXml($this->resolveKey($request))); }
    public function xmlInsurance(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getInsuranceXml($this->resolveKey($request))); }
    public function xmlOrganization(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getOrganizationXml($this->resolveKey($request))); }
    public function xmlFamily(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getFamilyXml($this->resolveKey($request))); }
    public function xmlAcademic(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getAcademicXml($this->resolveKey($request))); }
    public function xmlContactPoints(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getContactPointsXml($this->resolveKey($request))); }
    public function xmlInformal(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getInformalXml($this->resolveKey($request))); }
    public function xmlDriving(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getDrivingXml($this->resolveKey($request))); }
    public function xmlHealth(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getHealthXml($this->resolveKey($request))); }
    public function xmlExperience(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getExperienceXml($this->resolveKey($request))); }
    public function xmlOtherSkill(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getOtherSkillXml($this->resolveKey($request))); }
    public function xmlAward(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getAwardXml($this->resolveKey($request))); }
    public function xmlLanguage(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getLanguageXml($this->resolveKey($request))); }
    public function xmlKjk(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getKjkXml($this->resolveKey($request))); }
    public function xmlOvertime(Request $request) { $this->authorizeView(); return $this->xmlResponse($this->detailService->getOvertimeXml($this->resolveKey($request))); }
}
