<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShipmentInstruction;
use App\Services\UploadService;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VesselScheduleController extends Controller
{
    private $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function indexVesselSchedule()
    {
        return view('cns.vs.vessel-schedule');
    }

    public function indexVesselScheduleXml(Request $request)
    {
        header('Content-type: text/xml');

        $query = VesselSchedule::query();

        if ($request->filled('tgl1') && $request->filled('tgl2')) {
            $query->whereBetween('Created_Date', [
                $request->tgl1 . ' 00:00:00',
                $request->tgl2 . ' 23:59:59',
            ]);
        }

        $vessels = $query->orderBy('Created_Date', 'desc')->get();

        $q88Ids = $vessels->pluck('Q88_Meta_File_ID')->filter()->unique()->values()->all();
        $q88Files = count($q88Ids)
            ? DB::table('meta_files')->whereIn('ID', $q88Ids)->get()->keyBy('ID')
            : collect();

        echo "<rows>";

        foreach ($vessels as $vessel) {
            $method = $vessel->Shipping_Method;
            $isContainer = $method === 'CONTAINER';
            $isVessel = $method === 'VESSEL';
            $isTruck = $method === 'TANK_TRUCK';
            $isLegacy = $method === null || $method === '';

            $q88 = $vessel->Q88_Meta_File_ID ? ($q88Files[$vessel->Q88_Meta_File_ID] ?? null) : null;
            $q88Cell = ($isVessel && $q88)
                ? htmlspecialchars(asset('filevendor/' . $q88->Path)) . '^Lihat^_blank'
                : '';

            if ($isTruck) {
                $stuffingDate = $vessel->Truck_Schedule
                    ? $this->dateTimeCell($vessel->Truck_Schedule)
                    : 'Menunggu Buyer';
            } elseif ($isContainer || $isLegacy) {
                $stuffingDate = $this->dateCell($vessel->Stuffing);
            } else {
                $stuffingDate = '';
            }

            if ($isVessel) {
                $laycan = trim($this->dateCell($vessel->Laycan_Start) . ' - ' . $this->dateCell($vessel->Laycan_End), ' -');
            } else {
                $laycan = '';
            }

            echo '<row id="' . $vessel->id . '">';
            echo '<cell>' . htmlspecialchars((string) $vessel->Contract_No) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Incoterms) . '</cell>';
            echo '<cell>' . htmlspecialchars($this->methodLabel($method)) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Booking_No) . '</cell>';
            echo '<cell>' . htmlspecialchars($this->quantityCell($vessel, $method)) . '</cell>';
            echo '<cell>' . htmlspecialchars($isContainer || $isLegacy ? (string) $vessel->Qty2 : '') . '</cell>';
            echo '<cell>' . htmlspecialchars($isTruck ? (string) $vessel->Truck : (string) $vessel->Vessel) . '</cell>';
            echo '<cell>' . htmlspecialchars($isTruck ? '' : (string) $vessel->POL) . '</cell>';
            echo '<cell>' . htmlspecialchars($isTruck ? '' : (string) $vessel->POD) . '</cell>';
            echo '<cell>' . htmlspecialchars($stuffingDate) . '</cell>';
            echo '<cell>' . htmlspecialchars(($isContainer || $isLegacy) ? $this->dateCell($vessel->Closing_CY) : '') . '</cell>';
            echo '<cell>' . htmlspecialchars($laycan) . '</cell>';
            echo '<cell>' . htmlspecialchars(($isTruck) ? '' : $this->dateCell($vessel->ETD)) . '</cell>';
            echo '<cell>' . htmlspecialchars(($isTruck) ? '' : $this->dateCell($vessel->ETA)) . '</cell>';
            echo '<cell>' . $q88Cell . '</cell>';
            echo '</row>';
        }

        echo '</rows>';
    }

    public function newVesselSchedule()
    {
        return view('cns.vs.vessel-schedule-new', [
            'vs' => null,
            'contracts' => $this->activeContracts(),
            'vendors' => $this->vendors(),
            'bookings' => collect(),
            'q88' => null,
            'isEdit' => false,
        ]);
    }

    public function newVesselScheduleSubmit(Request $request)
    {
        $method = (string) $request->input('Shipping_Method');

        $data = $request->validate(array_merge([
            'Contract_ID' => 'required|integer',
            'Shipping_Method' => 'required|in:CONTAINER,VESSEL,TANK_TRUCK',
        ], $this->methodRules($method, false)));

        $contract = $this->contractForNew($data['Contract_ID']);

        if (!$contract) {
            return response()->json(['message' => 'Kontrak tidak ditemukan atau belum aktif.'], 422);
        }

        $booking = null;
        $vendor = null;

        if ($method === 'CONTAINER') {
            $booking = $this->bookingFor($contract->ID, $data['Container_Booking_ID']);

            if (!$booking) {
                return response()->json(['message' => 'Booking confirmation tidak ditemukan pada kontrak ini.'], 422);
            }
        }

        if ($method === 'TANK_TRUCK') {
            $vendor = $this->vendorFor($data['Truck_Supplier_ID']);

            if (!$vendor) {
                return response()->json(['message' => 'Vendor tidak ditemukan.'], 422);
            }
        }

        $vs = new VesselSchedule();
        $vs->Task_ID = $this->taskId();
        $vs->Contract_ID = $contract->ID;
        $vs->Contract_No = $contract->Contract_No;
        $vs->Incoterms = $contract->Incoterms;
        $vs->Shipping_Method = $method;
        $vs->Created_By = auth()->user()->username;

        $this->fillMethodFields($vs, $data, $method, $contract, $booking, $vendor);
        $vs->save();

        $this->handleQ88($request, $vs, false);
        $this->writeLog($vs, 'CREATED');

        return 'success';
    }

    public function detailVesselSchedule(Request $request)
    {
        $vs = VesselSchedule::where('id', $request->task)->first();

        if (!$vs) {
            abort(404);
        }

        $contracts = $this->activeContracts();
        $currentContract = $vs->Contract_ID
            ? DB::table('cns_buyer_contracts')->where('ID', $vs->Contract_ID)->first()
            : null;

        $hasCurrentContract = $currentContract && $contracts->contains(function ($contract) use ($currentContract) {
            return (int) $contract->ID === (int) $currentContract->ID;
        });

        if ($currentContract && !$hasCurrentContract) {
            $contracts->prepend($currentContract);
        }

        $bookings = $vs->Contract_ID ? $this->bookingsFor($vs->Contract_ID) : collect();
        $q88 = $vs->Q88_Meta_File_ID
            ? DB::table('meta_files')->where('ID', $vs->Q88_Meta_File_ID)->first()
            : null;

        return view('cns.vs.vessel-schedule-detail', [
            'vs' => $vs,
            'contracts' => $contracts,
            'vendors' => $this->vendors(),
            'bookings' => $bookings,
            'q88' => $q88,
            'isEdit' => true,
        ]);
    }

    public function detailVesselScheduleSubmit(Request $request)
    {
        if (!$request->filled('Shipping_Method')) {
            return $this->legacyDetailSubmit($request);
        }

        $vs = VesselSchedule::where('id', $request->input('id'))->first();

        if (!$vs) {
            abort(404);
        }

        $method = (string) $request->input('Shipping_Method');

        $data = $request->validate(array_merge([
            'id' => 'required|integer|exists:vessel_schedules,id',
            'Contract_ID' => 'required|integer',
            'Shipping_Method' => 'required|in:CONTAINER,VESSEL,TANK_TRUCK',
        ], $this->methodRules($method, true)));

        $contract = $this->contractForEdit($data['Contract_ID'], $vs->Contract_ID);

        if (!$contract) {
            return response()->json(['message' => 'Kontrak tidak ditemukan atau belum aktif.'], 422);
        }

        $booking = null;
        $vendor = null;

        if ($method === 'CONTAINER') {
            $booking = $this->bookingFor($contract->ID, $data['Container_Booking_ID']);

            if (!$booking) {
                return response()->json(['message' => 'Booking confirmation tidak ditemukan pada kontrak ini.'], 422);
            }
        }

        if ($method === 'TANK_TRUCK') {
            $vendor = $this->vendorFor($data['Truck_Supplier_ID']);

            if (!$vendor) {
                return response()->json(['message' => 'Vendor tidak ditemukan.'], 422);
            }
        }

        $oldOrderNo = $vs->Order_No;

        DB::transaction(function () use ($request, $vs, $data, $method, $contract, $booking, $vendor, $oldOrderNo) {
            $vs->Contract_ID = $contract->ID;
            $vs->Contract_No = $contract->Contract_No;
            $vs->Incoterms = $contract->Incoterms;
            $vs->Shipping_Method = $method;
            $vs->Updated_By = auth()->user()->username;
            $vs->Updated_Date = now();

            $this->fillMethodFields($vs, $data, $method, $contract, $booking, $vendor);

            if ($oldOrderNo && $oldOrderNo !== $vs->Order_No) {
                ShipmentInstruction::where('Order_No', $oldOrderNo)
                    ->update(['Order_No' => $vs->Order_No]);

                PackingList::where('Order_No', $oldOrderNo)
                    ->update(['Order_No' => $vs->Order_No]);

                VesselScheduleDtl::active()->where('Task_ID', $vs->Task_ID)
                    ->update(['Order_No' => $vs->Order_No]);
            }

            $vs->save();
            $this->handleQ88($request, $vs, true);
            $this->writeLog($vs, 'UPDATE DATA');
        });

        return 'success';
    }

    public function detailLogVesselScheduleXml(Request $request)
    {
        header('Content-type: text/xml');

        $vessels = VesselScheduleLog::where('Last_ID', $request->task)->get();

        echo '<rows>';

        foreach ($vessels as $key => $vessel) {
            echo '<row id="' . $vessel->id . '">';
            echo '<cell>' . ($key + 1) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Action_By) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Action_Date) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Action_Status) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Contract_No) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Shipping_Method) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->Qty) . '</cell>';
            echo '<cell>' . htmlspecialchars($vessel->Truck ? (string) $vessel->Truck : (string) $vessel->Vessel) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->POL) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $vessel->POD) . '</cell>';
            echo '<cell>' . htmlspecialchars($this->dateCell($vessel->Stuffing)) . '</cell>';
            echo '<cell>' . htmlspecialchars($this->dateCell($vessel->Closing_CY)) . '</cell>';
            echo '<cell>' . htmlspecialchars($this->dateCell($vessel->ETD)) . '</cell>';
            echo '<cell>' . htmlspecialchars($this->dateCell($vessel->ETA)) . '</cell>';
            echo '</row>';
        }

        echo '</rows>';
    }

    public function bookings(Request $request)
    {
        $contract = DB::table('cns_buyer_contracts')
            ->where('ID', $request->query('contract_id'))
            ->where('Location', auth()->user()->Location)
            ->first();

        if (!$contract) {
            return response()->json([]);
        }

        $rows = $this->bookingsFor($contract->ID);

        return response()->json($rows->map(function ($row) {
            return [
                'id' => (int) $row->ID,
                'label' => $row->Label,
                'booking_no' => $row->Booking_No,
                'file_url' => $row->Booking_File ? asset($row->Booking_File) : null,
                'file_name' => $row->Booking_File_Name,
            ];
        })->values());
    }

    private function legacyDetailSubmit(Request $request)
    {
        $vs = VesselSchedule::where('id', $request->input('id'))->first();

        if (!$vs) {
            abort(404);
        }

        $cek = VesselSchedule::where('Order_No', $request->input('Order_No'))
            ->where('ID', '!=', $request->input('id'))
            ->first();

        if ($cek) {
            echo 'duplicate';
            return;
        }

        $oldOrderNo = $vs->Order_No;

        DB::transaction(function () use ($request, $vs, $oldOrderNo) {
            if ($oldOrderNo != $request->input('Order_No')) {
                ShipmentInstruction::where('Order_No', $oldOrderNo)
                    ->update(['Order_No' => $request->input('Order_No')]);

                PackingList::where('Order_No', $oldOrderNo)
                    ->update(['Order_No' => $request->input('Order_No')]);

                VesselScheduleDtl::active()->where('Task_ID', $vs->Task_ID)
                    ->update(['Order_No' => $request->input('Order_No')]);
            }

            $vs->Order_No = $request->input('Order_No');
            $vs->Qty = $request->input('Qty1') . ' x ' . $request->input('Qty2');
            $vs->Qty1 = $request->input('Qty1');
            $vs->Qty2 = $request->input('Qty2');
            $vs->Vessel = $request->input('Vessel');
            $vs->POL = $request->input('POL');
            $vs->POD = $request->input('POD');
            $vs->Stuffing = $request->input('Stuffing');
            $vs->Checking_BC = $request->input('Checking_BC');
            $vs->Closing_CY = $request->input('Closing_CY');
            $vs->ETD = $request->input('ETD');
            $vs->ATD = $request->input('ATD');
            $vs->ETA = $request->input('ETA');
            $vs->ATA = $request->input('ATA');
            $vs->Note = $request->input('Note');
            $vs->Created_By = auth()->user()->username;
            $vs->Updated_By = auth()->user()->username;
            $vs->Updated_Date = now();
            $vs->save();

            $log = $vs->toArray();
            $log['Last_ID'] = $log['id'];
            unset($log['id']);
            $log['Action_By'] = auth()->user()->username;
            $log['Action_Date'] = now();
            $log['Action_Status'] = 'UPDATE DATA';
            VesselScheduleLog::create($log);
        });

        echo 'success';
    }

    private function fillMethodFields(VesselSchedule $vs, array $data, string $method, $contract, $booking, $vendor): void
    {
        $vs->Order_No = (string) $contract->Contract_No;
        $vs->Qty = '';
        $vs->Qty1 = '';
        $vs->Qty2 = '';
        $vs->Vessel = '';
        $vs->POL = '';
        $vs->POD = '';
        $vs->Stuffing = null;
        $vs->Checking_BC = null;
        $vs->Closing_CY = null;
        $vs->ETD = null;
        $vs->ATD = null;
        $vs->ETA = null;
        $vs->ATA = null;
        $vs->Note = '';
        $vs->Booking_ID = null;
        $vs->Booking_No = null;
        $vs->Laycan_Start = null;
        $vs->Laycan_End = null;
        $vs->Truck_Supplier_ID = null;
        $vs->Truck = null;
        $vs->Truck_Schedule = null;
        $vs->Truck_Destination = null;
        $vs->Truck_Shipment_Data = null;

        if ($method === 'CONTAINER') {
            $vs->Booking_ID = $booking->ID;
            $vs->Booking_No = $booking->Booking_No;
            $vs->Order_No = $booking->Booking_No;
            $vs->Qty1 = (string) $data['Container_Qty1'];
            $vs->Qty2 = (string) $data['Container_Qty2'];
            $vs->Qty = $vs->Qty1 . ' x ' . $vs->Qty2;
            $vs->Vessel = (string) $data['Container_Vessel'];
            $vs->POL = (string) $data['Container_POL'];
            $vs->POD = (string) $data['Container_POD'];
            $vs->Stuffing = $data['Container_Stuffing'];
            $vs->Closing_CY = $data['Container_Closing_CY'];
            $vs->ETD = $data['Container_ETD'];
            $vs->ETA = $data['Container_ETA'];
        }

        if ($method === 'VESSEL') {
            $vs->Qty1 = (string) $data['Vessel_Qty1'];
            $vs->Qty = $vs->Qty1 . ' MT';
            $vs->Vessel = (string) $data['Vessel_Name'];
            $vs->POL = (string) $data['Vessel_POL'];
            $vs->POD = (string) $data['Vessel_POD'];
            $vs->Laycan_Start = $data['Vessel_Laycan_Start'];
            $vs->Laycan_End = $data['Vessel_Laycan_End'];
            $vs->ETD = $data['Vessel_ETD'];
            $vs->ETA = $data['Vessel_ETA'];
        }

        if ($method === 'TANK_TRUCK') {
            $vs->Qty1 = (string) $data['Truck_Qty1'];
            $vs->Qty = $vs->Qty1;
            $vs->Truck_Supplier_ID = $vendor->ID;
            $vs->Truck = $vendor->Company_Name;
            $vs->Truck_Schedule = !empty($data['Truck_Schedule'])
                ? str_replace('T', ' ', $data['Truck_Schedule'])
                : null;
            $vs->Truck_Destination = (string) $data['Truck_Destination'];
            $vs->Truck_Shipment_Data = (string) $data['Truck_Shipment_Data'];
        }
    }

    private function methodRules(string $method, bool $isEdit): array
    {
        $rules = [];

        if ($method === 'CONTAINER') {
            $rules = [
                'Container_Booking_ID' => 'required|integer',
                'Container_Qty1' => 'required|numeric|min:0.001',
                'Container_Qty2' => 'required|string|max:50',
                'Container_Vessel' => 'required|string|max:150',
                'Container_POL' => 'required|string|max:150',
                'Container_POD' => 'required|string|max:150',
                'Container_Stuffing' => 'required|date',
                'Container_Closing_CY' => 'required|date',
                'Container_ETD' => 'required|date',
                'Container_ETA' => 'required|date',
            ];
        } elseif ($method === 'VESSEL') {
            $rules = [
                'Vessel_Qty1' => 'required|numeric|min:0.001',
                'Vessel_POL' => 'required|string|max:150',
                'Vessel_POD' => 'required|string|max:150',
                'Vessel_Laycan_Start' => 'required|date',
                'Vessel_Laycan_End' => 'required|date|after_or_equal:Vessel_Laycan_Start',
                'Vessel_Name' => 'required|string|max:150',
                'Vessel_ETD' => 'required|date',
                'Vessel_ETA' => 'required|date',
                'Q88_File' => ($isEdit ? 'nullable' : 'required') . '|file|mimes:pdf|max:5120',
            ];
        } elseif ($method === 'TANK_TRUCK') {
            $rules = [
                'Truck_Qty1' => 'required|numeric|min:0.001',
                'Truck_Supplier_ID' => 'required|integer',
                'Truck_Schedule' => 'nullable|date',
                'Truck_Destination' => 'required|string|max:150',
                'Truck_Shipment_Data' => 'required|string',
            ];
        }

        return $rules;
    }

    private function handleQ88(Request $request, VesselSchedule $vs, bool $isEdit): void
    {
        if ($vs->Shipping_Method !== 'VESSEL') {
            if ($vs->Q88_Meta_File_ID) {
                $this->deleteMetaFile($vs->Q88_Meta_File_ID);
                $vs->Q88_Meta_File_ID = null;
                $vs->save();
            }
            return;
        }

        if ($request->hasFile('Q88_File')) {
            if ($isEdit && $vs->Q88_Meta_File_ID) {
                $this->deleteMetaFile($vs->Q88_Meta_File_ID);
            }

            $fileId = $this->uploadService->storeFile(
                $request->file('Q88_File'),
                'file',
                'vessel-q88',
                $vs->id,
                'Q88 Document'
            );

            $vs->Q88_Meta_File_ID = $fileId;
            $vs->save();
            return;
        }

        if (!$vs->Q88_Meta_File_ID) {
            throw ValidationException::withMessages([
                'Q88_File' => 'Q88 PDF wajib diunggah.',
            ]);
        }
    }

    private function deleteMetaFile($id): void
    {
        $meta = DB::table('meta_files')->where('ID', $id)->first();

        if (!$meta) {
            return;
        }

        $path = public_path('filevendor/' . $meta->Path);

        if ($meta->Path && is_file($path)) {
            @unlink($path);
        }

        DB::table('meta_files')->where('ID', $id)->delete();
    }

    private function writeLog(VesselSchedule $vs, string $status): void
    {
        $log = $vs->toArray();
        $log['Last_ID'] = $log['id'];
        unset($log['id']);
        $log['Action_By'] = auth()->user()->username;
        $log['Action_Date'] = now();
        $log['Action_Status'] = $status;

        VesselScheduleLog::create($log);
    }

    private function activeContracts()
    {
        return DB::table('cns_buyer_contracts')
            ->where('Location', auth()->user()->Location)
            ->where('Status', 'ACTIVE_IN_PROGRESS')
            ->orderBy('Contract_No')
            ->get();
    }

    private function contractForNew($id)
    {
        return DB::table('cns_buyer_contracts')
            ->where('ID', $id)
            ->where('Location', auth()->user()->Location)
            ->where('Status', 'ACTIVE_IN_PROGRESS')
            ->first();
    }

    private function contractForEdit($id, $currentId)
    {
        $query = DB::table('cns_buyer_contracts')
            ->where('ID', $id)
            ->where('Location', auth()->user()->Location);

        if ((int) $id !== (int) $currentId) {
            $query->where('Status', 'ACTIVE_IN_PROGRESS');
        }

        return $query->first();
    }

    private function bookingsFor($contractId)
    {
        return DB::table('cns_buyer_contract_bookings')
            ->where('Contract_ID', $contractId)
            ->orderBy('Label')
            ->get();
    }

    private function bookingFor($contractId, $bookingId)
    {
        return DB::table('cns_buyer_contract_bookings')
            ->where('ID', $bookingId)
            ->where('Contract_ID', $contractId)
            ->first();
    }

    private function vendors()
    {
        return DB::table('supplier')
            ->where('Location', auth()->user()->Location)
            ->where('Is_Supplier', 1)
            ->orderBy('Company_Name')
            ->get();
    }

    private function vendorFor($id)
    {
        return DB::table('supplier')
            ->where('ID', $id)
            ->where('Location', auth()->user()->Location)
            ->where('Is_Supplier', 1)
            ->first();
    }

    private function taskId(): string
    {
        return 'VS/GEU/' . substr(md5(auth()->user()->username), 0, 5) . '/' . date('YmdHis') . substr(uniqid(), -4);
    }

    private function methodLabel($method): string
    {
        if ($method === 'CONTAINER') {
            return 'Container';
        }

        if ($method === 'VESSEL') {
            return 'Vessel';
        }

        if ($method === 'TANK_TRUCK') {
            return 'Tank Truck';
        }

        return '';
    }

    private function quantityCell(VesselSchedule $vs, $method): string
    {
        $quantity = $vs->Qty1;

        if ($quantity === null || $quantity === '') {
            $quantity = $vs->Qty;
        }

        if ($method === 'VESSEL') {
            return $quantity ? $quantity . ' MT' : '';
        }

        return (string) $quantity;
    }

    private function dateCell($value): string
    {
        return $value ? date('d M Y', strtotime($value)) : '';
    }

    private function dateTimeCell($value): string
    {
        return $value ? date('d M Y H:i', strtotime($value)) : '';
    }
}
