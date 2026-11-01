<?php

namespace App\Http\Controllers\CNS;

use App\BuyerContract;
use App\BuyerContractBooking;
use App\Http\Controllers\Controller;
use App\Services\CNS\ProcurementListService;
use App\Services\GeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BuyerContractController extends Controller
{
    private $listService;
    private $generatorService;

    public function __construct(ProcurementListService $listService, GeneratorService $generatorService)
    {
        $this->listService = $listService;
        $this->generatorService = $generatorService;
    }

    public function indexBuyerContract()
    {
        return view('cns.procurement.buyer-contract');
    }

    public function indexBuyerContractXml(Request $request)
    {
        if (!headers_sent()) {
            header("Content-type: text/xml");
        }

        $rows = $this->listService->rows([
            'contract' => $request->query('contract', 'all'),
            'group'    => $request->query('group', 'all'),
            'product'  => $request->query('product', 'all'),
        ]);

        echo "<rows>";
        $nomor = 1;

        foreach ($rows as $row) {
            $file = '';

            if (!empty($row['file'])) {
                $file = htmlspecialchars(asset($row['file'])) . '^Lihat^_blank';
            }

            echo '<row id="' . htmlspecialchars($row['id']) . '">';
            echo '<cell>' . $nomor . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['name']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['address']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['phone']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['email']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['group']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['product']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['contract_label']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['contract_no']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['loading_period']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['contract_qty']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['delivered']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['outstanding']) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row['status']) . '</cell>';
            echo '<cell>' . $file . '</cell>';
            echo '</row>';

            $nomor++;
        }

        echo "</rows>";
    }

    public function newBuyerContract()
    {
        $highest = DB::table('cns_buyer_contracts')->max('ID') ?? 0;
        $generatedContractNo = $this->generatorService->generateApplicationCode($highest, 'CTR');

        return view('cns.procurement.buyer-contract-new', array_merge($this->formData(), [
            'nonUcoCategories' => NonUcoSupplierController::CATEGORIES,
            'generatedContractNo' => $generatedContractNo,
        ]));
    }

    public function newBuyerContractSubmit(Request $request)
    {
        $data = $this->validateContract($request);

        $buyer = $this->findBuyer($data['Supplier_ID']);

        if (!$buyer) {
            return response()->json(['message' => 'Buyer tidak ditemukan pada Master Company.'], 422);
        }

        $emails = $this->cleanEmails($data['Buyer_Emails']);

        if (count($emails) < 1) {
            return response()->json(['message' => 'Minimal satu alamat email buyer wajib diisi.'], 422);
        }

        $file = $request->file('Contract_File');
        $filename = $this->storeContractFile($file, $data['Contract_No']);

        DB::table('cns_buyer_contracts')->insert([
            'Contract_No'        => $data['Contract_No'],
            'Supplier_ID'        => $data['Supplier_ID'],
            'Buyer_Name'         => $data['Buyer_Name'],
            'Buyer_Address'      => $data['Buyer_Address'],
            'Buyer_Phone'        => $data['Buyer_Phone'],
            'Buyer_Emails'       => json_encode($emails),
            'Quantity_MT'        => $data['Quantity_MT'],
            'Container_Count'    => $data['Container_Count'],
            'Container_Size'     => $data['Container_Size'],
            'Loading_Start'      => $data['Loading_Start'],
            'Loading_End'        => $data['Loading_End'],
            'Incoterms'          => $data['Incoterms'],
            'Price_Currency'     => $data['Price_Currency'],
            'Price_Per_MT'       => $data['Price_Per_MT'],
            'Status'             => 'UNDER_REVIEW',
            'Contract_File'      => 'filescontract/contracts/' . $filename,
            'Contract_File_Name' => $file->getClientOriginalName(),
            'Note'               => $data['Note'] ?? null,
            'Location'           => Auth::user()->Location,
            'Created_By'         => Auth::user()->username,
            'Created_Date'       => now(),
        ]);

        return 'success';
    }

    public function detailBuyerContract(Request $request)
    {
        $contract = BuyerContract::where('ID', $request->ID)
            ->where('Location', Auth::user()->Location)
            ->first();

        if (!$contract) {
            abort(404);
        }

        $bookings = BuyerContractBooking::where('Contract_ID', $contract->ID)
            ->orderBy('ID')
            ->get();

        return view('cns.procurement.buyer-contract-detail', array_merge(
            $this->formData(),
            compact('contract', 'bookings')
        ));
    }

    public function detailBuyerContractSubmit(Request $request)
    {
        $id = $request->input('ID');

        $data = $request->validate([
            'ID'              => 'required|integer|exists:cns_buyer_contracts,ID',
            'Contract_No'     => ['required', 'string', 'max:100', Rule::unique('cns_buyer_contracts', 'Contract_No')->ignore($id, 'ID')],
            'Supplier_ID'     => 'required|integer|exists:supplier,ID',
            'Buyer_Name'      => 'required|string|max:150',
            'Buyer_Address'   => 'nullable|string',
            'Buyer_Phone'     => 'required|string|max:60',
            'Buyer_Emails'    => 'required|array|min:1|max:10',
            'Buyer_Emails.*'  => 'required|email|max:150',
            'Quantity_MT'     => 'required|numeric|min:0.001',
            'Container_Count' => 'required|integer|min:1',
            'Container_Size'  => 'required|in:20 ft,40 ft',
            'Loading_Start'   => 'required|date',
            'Loading_End'     => 'required|date|after_or_equal:Loading_Start',
            'Incoterms'       => 'required|in:FOB,CIF',
            'Price_Currency'  => 'required|in:IDR,USD,EUR',
            'Price_Per_MT'    => 'required|numeric|min:0.01',
            'Note'            => 'nullable|string',
            'Contract_File'   => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $contract = BuyerContract::where('ID', $id)
            ->where('Location', Auth::user()->Location)
            ->first();

        if (!$contract) {
            return response()->json(['message' => 'Kontrak tidak ditemukan.'], 422);
        }

        if (!$this->findBuyer($data['Supplier_ID'])) {
            return response()->json(['message' => 'Buyer tidak ditemukan pada Master Company.'], 422);
        }

        $emails = $this->cleanEmails($data['Buyer_Emails']);

        if (count($emails) < 1) {
            return response()->json(['message' => 'Minimal satu alamat email buyer wajib diisi.'], 422);
        }

        $update = [
            'Contract_No'     => $data['Contract_No'],
            'Supplier_ID'     => $data['Supplier_ID'],
            'Buyer_Name'      => $data['Buyer_Name'],
            'Buyer_Address'   => $data['Buyer_Address'],
            'Buyer_Phone'     => $data['Buyer_Phone'],
            'Buyer_Emails'    => json_encode($emails),
            'Quantity_MT'     => $data['Quantity_MT'],
            'Container_Count' => $data['Container_Count'],
            'Container_Size'  => $data['Container_Size'],
            'Loading_Start'   => $data['Loading_Start'],
            'Loading_End'     => $data['Loading_End'],
            'Incoterms'       => $data['Incoterms'],
            'Price_Currency'  => $data['Price_Currency'],
            'Price_Per_MT'    => $data['Price_Per_MT'],
            'Note'            => $data['Note'] ?? null,
            'Updated_By'      => Auth::user()->username,
            'Updated_Date'    => now(),
        ];

        if ($request->hasFile('Contract_File')) {
            $this->deletePublicFile($contract->Contract_File);
            $file = $request->file('Contract_File');
            $filename = $this->storeContractFile($file, $data['Contract_No']);
            $update['Contract_File'] = 'filescontract/contracts/' . $filename;
            $update['Contract_File_Name'] = $file->getClientOriginalName();
        }

        DB::table('cns_buyer_contracts')->where('ID', $id)->update($update);

        return 'success';
    }

    public function addBooking(Request $request)
    {
        $data = $request->validate([
            'Contract_ID'  => 'required|integer|exists:cns_buyer_contracts,ID',
            'Label'        => 'required|string|max:20',
            'Booking_No'   => 'required|string|max:100',
            'Booking_File' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $contract = BuyerContract::where('ID', $data['Contract_ID'])
            ->where('Location', Auth::user()->Location)
            ->first();

        if (!$contract) {
            return response()->json(['message' => 'Kontrak tidak ditemukan.'], 422);
        }

        $exists = DB::table('cns_buyer_contract_bookings')
            ->where('Contract_ID', $data['Contract_ID'])
            ->where('Label', $data['Label'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Label booking sudah dipakai pada kontrak ini.'], 422);
        }

        $file = $request->file('Booking_File');
        $safeNo = preg_replace('/[^A-Za-z0-9_-]/', '_', $contract->Contract_No);
        $safeLabel = preg_replace('/[^A-Za-z0-9_-]/', '_', $data['Label']);
        $dir = public_path('filescontract/bookings');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = 'Booking_' . $safeNo . '_' . $safeLabel . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($dir, $filename);

        DB::table('cns_buyer_contract_bookings')->insert([
            'Contract_ID'       => $data['Contract_ID'],
            'Label'             => $data['Label'],
            'Booking_No'        => $data['Booking_No'],
            'Booking_File'      => 'filescontract/bookings/' . $filename,
            'Booking_File_Name' => $file->getClientOriginalName(),
            'Created_By'        => Auth::user()->username,
            'Created_Date'      => now(),
        ]);

        return 'success';
    }

    public function deleteBooking(Request $request)
    {
        $data = $request->validate([
            'ID'          => 'required|integer',
            'Contract_ID' => 'required|integer',
        ]);

        $contract = BuyerContract::where('ID', $data['Contract_ID'])
            ->where('Location', Auth::user()->Location)
            ->first();

        if (!$contract) {
            return response()->json(['message' => 'Kontrak tidak ditemukan.'], 422);
        }

        $booking = DB::table('cns_buyer_contract_bookings')
            ->where('ID', $data['ID'])
            ->where('Contract_ID', $data['Contract_ID'])
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan.'], 422);
        }

        $this->deletePublicFile($booking->Booking_File);
        DB::table('cns_buyer_contract_bookings')->where('ID', $booking->ID)->delete();

        return 'success';
    }

    public function buyers()
    {
        $rows = DB::table('supplier')
            ->where('Location', Auth::user()->Location)
            ->where('Is_Consignee', 1)
            ->orderBy('Company_Name')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $emails = [];

            if (!empty($row->Emails)) {
                $decoded = json_decode($row->Emails, true);
                if (is_array($decoded)) {
                    $emails = array_values(array_filter($decoded));
                }
            }

            if (count($emails) === 0 && !empty($row->Email)) {
                $emails = [$row->Email];
            }

            $result[] = [
                'id'      => (int) $row->ID,
                'name'    => $row->Company_Name,
                'address' => $row->Address,
                'phone'   => $row->Phone,
                'email'   => $row->Email,
                'emails'  => $emails,
            ];
        }

        return response()->json($result);
    }

    private function formData(): array
    {
        $buyers = DB::table('supplier')
            ->where('Location', Auth::user()->Location)
            ->where('Is_Consignee', 1)
            ->orderBy('Company_Name')
            ->get();

        $buyersPayload = $buyers->map(function ($buyer) {
            $emails = [];

            if (!empty($buyer->Emails)) {
                $decoded = json_decode($buyer->Emails, true);
                if (is_array($decoded)) {
                    $emails = array_values(array_filter($decoded));
                }
            }

            if (count($emails) === 0 && !empty($buyer->Email)) {
                $emails = [$buyer->Email];
            }

            return [
                'id'      => (int) $buyer->ID,
                'name'    => $buyer->Company_Name,
                'address' => $buyer->Address,
                'phone'   => $buyer->Phone,
                'emails'  => $emails,
            ];
        })->values();

        return [
            'buyers'         => $buyers,
            'buyersPayload'  => $buyersPayload,
            'currencies'     => ['IDR' => 'IDR', 'USD' => 'USD', 'EUR' => 'EUR'],
            'incoterms'      => ['FOB' => 'FOB - Free On Board', 'CIF' => 'CIF - Cost, Insurance and Freight'],
            'containerSizes' => ['20 ft' => '20 ft', '40 ft' => '40 ft'],
        ];
    }

    private function validateContract(Request $request): array
    {
        return $request->validate([
            'Contract_No'     => 'required|string|max:100|unique:cns_buyer_contracts,Contract_No',
            'Supplier_ID'     => 'required|integer|exists:supplier,ID',
            'Buyer_Name'      => 'required|string|max:150',
            'Buyer_Address'   => 'nullable|string',
            'Buyer_Phone'     => 'required|string|max:60',
            'Buyer_Emails'    => 'required|array|min:1|max:10',
            'Buyer_Emails.*'  => 'required|email|max:150',
            'Quantity_MT'     => 'required|numeric|min:0.001',
            'Container_Count' => 'required|integer|min:1',
            'Container_Size'  => 'required|in:20 ft,40 ft',
            'Loading_Start'   => 'required|date',
            'Loading_End'     => 'required|date|after_or_equal:Loading_Start',
            'Incoterms'       => 'required|in:FOB,CIF',
            'Price_Currency'  => 'required|in:IDR,USD,EUR',
            'Price_Per_MT'    => 'required|numeric|min:0.01',
            'Note'            => 'nullable|string',
            'Contract_File'   => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);
    }

    private function findBuyer($supplierId)
    {
        return DB::table('supplier')
            ->where('ID', $supplierId)
            ->where('Location', Auth::user()->Location)
            ->where('Is_Consignee', 1)
            ->first();
    }

    private function cleanEmails(array $emails): array
    {
        return array_values(array_filter(array_unique(array_map('trim', $emails)), function ($email) {
            return $email !== '';
        }));
    }

    private function storeContractFile($file, string $contractNo): string
    {
        $safeNo = preg_replace('/[^A-Za-z0-9_-]/', '_', $contractNo);
        $dir = public_path('filescontract/contracts');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = 'Contract_' . $safeNo . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($dir, $filename);

        return $filename;
    }

    private function deletePublicFile($path): void
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
