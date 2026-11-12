<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CNS\StuffingContainerRequest;
use App\Services\CNS\FlexibagInventoryService;
use App\Services\CNS\StuffingContainerService;
use App\Services\CNS\StuffingDeliveryOrderService;
use App\Services\CNS\StuffingNoteService;
use App\Services\CNS\StuffingPhotoService;
use App\Services\CNS\StuffingReportService;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StuffingController extends Controller
{
    protected $containers;
    protected $notes;
    protected $photos;
    protected $deliveryOrders;
    protected $reports;
    protected $flexibag;

    public function __construct(
        StuffingContainerService $containers,
        StuffingNoteService $notes,
        StuffingPhotoService $photos,
        StuffingDeliveryOrderService $deliveryOrders,
        StuffingReportService $reports,
        FlexibagInventoryService $flexibag
    ) {
        $this->containers = $containers;
        $this->notes = $notes;
        $this->photos = $photos;
        $this->deliveryOrders = $deliveryOrders;
        $this->reports = $reports;
        $this->flexibag = $flexibag;
    }

    public function indexStuffing()
    {
        return view('cns.vs.stuffing');
    }

    public function indexStuffingXml(Request $request)
    {
        if (!headers_sent()) {
            if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
                header("Content-type: application/xhtml+xml");
            } else {
                header("Content-type: text/xml");
            }
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $dtl = VesselScheduleDtl::active()->get();
        $vsd = [];
        foreach ($dtl as $value) {
            $vsd[$value->Order_No][] = $value->Container . " (" . $value->Seal . ")";
        }

        $canonicalIds = VesselSchedule::whereBetween('Stuffing', [$request->tgl1, $request->tgl2])
            ->selectRaw('MIN(id) as id')
            ->groupBy('Order_No')
            ->pluck('id');

        $vessels = VesselSchedule::whereIn('id', $canonicalIds)
            ->whereBetween('Stuffing', [$request->tgl1, $request->tgl2])
            ->orderBy('id')
            ->get();

        foreach ($vessels as $key => $vessel) {
            $hasContainers = isset($vsd[$vessel->Order_No]) && count($vsd[$vessel->Order_No]) > 0;
            $isDone = !empty($vessel->Checklist_Done_Kapal_Berangkat);

            if ($isDone) {
                $warna = "style='background-color: #ccffcfff'";
            } elseif ($hasContainers) {
                $warna = "style='background-color: #feffccff'";
            } else {
                $warna = "style='background-color: #ffccccff'";
            }
            echo "<row id=\"$vessel->id\" $warna>";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Qty) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Vessel) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->POL) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->POD) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Stuffing != "" ? date("d F Y", strtotime($vessel->Stuffing)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Checking_BC != "" ? date("d F Y", strtotime($vessel->Checking_BC)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Closing_CY != "" ? date("d F Y", strtotime($vessel->Closing_CY)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ETD != "" ? date("d F Y", strtotime($vessel->ETD)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ATD != "" ? date("d F Y", strtotime($vessel->ATD)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ETA != "" ? date("d F Y", strtotime($vessel->ETA)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ATA != "" ? date("d F Y", strtotime($vessel->ATA)) : "") . "</cell>";
            if ($vessel->ETD != "0000-00-00" && $vessel->ETA != "0000-00-00") {
                $etd = new \DateTime($vessel->ETD);
                $eta = new \DateTime($vessel->ETA);
                $interval = $etd->diff($eta);
                echo "<cell>" . htmlspecialchars($interval->format('%a days')) . "</cell>";
            } else {
                echo "<cell></cell>";
            }
            if ($vessel->ATD != "0000-00-00" && $vessel->ATA != "0000-00-00") {
                $atd = new \DateTime($vessel->ATD);
                $ata = new \DateTime($vessel->ATA);
                $interval = $atd->diff($ata);
                echo "<cell>" . htmlspecialchars($interval->format('%a days')) . "</cell>";
            } else {
                echo "<cell></cell>";
            }
            echo "<cell>" . htmlspecialchars((string) $vessel->Note) . "</cell>";
            if (isset($vsd[$vessel->Order_No])) {
                echo "<cell>" . htmlspecialchars(implode(', ', $vsd[$vessel->Order_No])) . "</cell>";
            } else {
                echo "<cell></cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function generateStuffingReport(Request $request)
    {
        $schedule = VesselSchedule::where('id', $request->query('task'))->first();

        abort_if(!$schedule, 404);

        $path = $this->reports->generate($schedule);
        $label = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($schedule->Order_No ?: $schedule->Task_ID));
        $filename = 'Final_Stuffing_Report_' . trim($label, '_') . '_' . date('Ymd_His') . '.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function detailStuffing(Request $request)
    {
        $vs = VesselSchedule::where('id', $request->task)->first();

        abort_if(!$vs, 404);

        $contract = $vs->Contract_ID
            ? DB::table('cns_buyer_contracts')->where('ID', $vs->Contract_ID)->first()
            : null;
        $deliveryOrder = $this->deliveryOrders->forSchedule($vs);

        return view('cns.vs.stuffing-detail', compact('vs', 'contract', 'deliveryOrder'));
    }

    public function detailStuffingXml(Request $request)
    {
        if (!headers_sent()) {
            if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
                header("Content-type: application/xhtml+xml");
            } else {
                header("Content-type: text/xml");
            }
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $withCheckbox = (string) $request->query('has_checkbox') === '1';
        $schedule = VesselSchedule::where('Task_ID', $request->task)->orderBy('id')->first();
        $note = $schedule ? $this->notes->forSchedule($schedule) : null;
        $vessels = VesselScheduleDtl::active()->where('Task_ID', $request->task)->get();

        foreach ($vessels as $key => $vessel) {
            $tare = (float) $vessel->Tare;
            $nettoKg = (float) $vessel->Netto_KG;
            $nettoMt = round($nettoKg / 1000, 2);
            $grossKg = $tare + $nettoKg;
            $grossMt = round($grossKg / 1000, 2);

            echo "<row id=\"" . $vessel->ID . "\" >";

            if ($withCheckbox) {
                echo "<cell>0</cell>";
            }

            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i", strtotime($vessel->Time_From))) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i", strtotime($vessel->Time_To))) . "</cell>";
            echo "<cell>" . htmlspecialchars(date('d F Y', strtotime($vessel->Date))) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Police_No) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Container) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Seal) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($tare, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($nettoKg, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($nettoMt, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($grossKg, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($grossMt, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Note) . "</cell>";
            echo "<cell>" . ($note ? 'Ada' : 'Tidak Ada') . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function detailStuffingData(Request $request)
    {
        $schedule = VesselSchedule::where('id', $request->query('task'))->first();

        abort_if(!$schedule, 404);

        $container = $this->containers->findForSchedule($schedule, (int) $request->query('ID'));

        if (!$container) {
            return response()->json(['status' => 'error', 'message' => 'Container not found'], 404);
        }

        $note = $this->notes->forSchedule($schedule);

        return response()->json([
            'ID' => $container->ID,
            'Order_No' => $container->Order_No,
            'Time_From' => substr($container->Time_From, 0, 5),
            'Time_To' => substr($container->Time_To, 0, 5),
            'Date' => $container->Date,
            'Police_No' => $container->Police_No,
            'Container' => $container->Container,
            'Seal' => $container->Seal,
            'Tare' => (float) $container->Tare,
            'Netto_KG' => (float) $container->Netto_KG,
            'Note' => $container->Note,
            'Note_Url' => $this->notes->url($note),
            'Note_Name' => $note ? $note->Filename : null,
            'photos' => $this->photos->photosForContainer((int) $container->ID),
        ]);
    }

    public function saveDetailStuffing(StuffingContainerRequest $request)
    {
        $schedule = VesselSchedule::where('id', $request->query('task'))->first();

        abort_if(!$schedule, 404);

        try {
            $container = null;

            DB::transaction(function () use ($request, $schedule, &$container) {
                $container = $this->containers->save(
                    $schedule,
                    $request->all(),
                    $request->filled('id') ? (int) $request->input('id') : null
                );

                if ($request->hasFile('Weighbridge_Note')) {
                    $this->notes->replace($schedule, $request->file('Weighbridge_Note'));
                }

                $this->photos->storeForContainer($container, $request->allFiles());
            });
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        $note = $this->notes->forSchedule($schedule);

        return response()->json([
            'status' => 'success',
            'message' => $request->filled('id') ? 'Container updated successfully' : 'Container created successfully',
            'id' => $container ? $container->ID : null,
            'note_url' => $this->notes->url($note),
            'note_name' => $note ? $note->Filename : null,
        ]);
    }

    public function deleteDetailStuffing(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $schedule = VesselSchedule::where('id', $request->query('task'))->first();

        abort_if(!$schedule, 404);

        try {
            $result = $this->containers->cancelMany($schedule, $request->input('ids'));
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Container berhasil dihapus.',
            'count' => $result['count'],
        ]);
    }

    public function deleteDetailPhoto(Request $request)
    {
        $request->validate([
            'container_id' => 'required|integer',
            'photo_id' => 'required|integer',
        ]);

        $schedule = VesselSchedule::where('id', $request->query('task'))->first();

        abort_if(!$schedule, 404);

        $container = $this->containers->findForSchedule($schedule, (int) $request->input('container_id'));

        if (!$container) {
            return response()->json(['status' => 'error', 'message' => 'Container not found'], 404);
        }

        try {
            $this->photos->deletePhoto((int) $container->ID, (int) $request->input('photo_id'));
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json(['status' => 'success', 'message' => 'Foto berhasil dihapus.']);
    }

    public function saveDetailDeliveryOrder(Request $request)
    {
        $request->validate([
            'DO_File' => 'nullable|file|mimes:pdf,jpg,jpeg|max:10240',
            'DO_No' => 'nullable|string|max:150',
            'DO_Status' => 'nullable|in:KURANG,SESUAI,MELEBIH',
            'DO_Note' => 'nullable|string|max:1000',
        ]);

        $schedule = VesselSchedule::where('id', $request->query('task'))->first();

        abort_if(!$schedule, 404);

        if (!$schedule->DO_No && !$request->filled('DO_No')) {
            return response()->json([
                'message' => 'Nomor Delivery Order wajib diisi.',
            ], 422);
        }

        if (!$schedule->DO_Meta_File_ID && !$request->hasFile('DO_File')) {
            return response()->json([
                'message' => 'File Delivery Order wajib diunggah pada penyimpanan pertama.',
            ], 422);
        }

        DB::transaction(function () use ($request, $schedule) {
            $this->deliveryOrders->save(
                $schedule,
                $request->file('DO_File'),
                $request->input('DO_No'),
                $request->input('DO_Status'),
                $request->input('DO_Note'),
                auth()->check() ? (string) auth()->user()->username : 'system'
            );

            $this->containers->writeScheduleLog($schedule, 'DELIVERY ORDER UPDATED');
        });

        $meta = $this->deliveryOrders->forSchedule($schedule);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery Order berhasil disimpan.',
            'url' => $this->deliveryOrders->url($meta),
            'name' => $meta ? $meta->Filename : null,
            'DO_No' => $schedule->DO_No,
            'DO_Status' => $schedule->DO_Status,
            'DO_Note' => $schedule->DO_Note,
        ]);
    }

    public function saveDetailChecklist(Request $request)
    {
        $request->validate([
            'Checklist_Pickup_Container' => 'nullable|boolean',
            'Checklist_Stuffing_Process' => 'nullable|boolean',
            'Checklist_Rilis_NPE' => 'nullable|boolean',
            'Checklist_Get_In_CY' => 'nullable|boolean',
            'Checklist_Done_Kapal_Berangkat' => 'nullable|boolean',
        ]);

        $schedule = VesselSchedule::where('id', $request->query('task'))->first();

        abort_if(!$schedule, 404);

        if ((int) $schedule->Checklist_Done_Kapal_Berangkat === 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status pengiriman sudah final (Done/Kapal Berangkat). Checklist terkunci dan tidak dapat diubah lagi.',
            ], 422);
        }

        $wasDone = (bool) $schedule->Checklist_Done_Kapal_Berangkat;
        $isDoneNow = (bool) $request->input('Checklist_Done_Kapal_Berangkat');

        $fields = [
            'Checklist_Pickup_Container',
            'Checklist_Stuffing_Process',
            'Checklist_Rilis_NPE',
            'Checklist_Get_In_CY',
            'Checklist_Done_Kapal_Berangkat',
        ];

        foreach ($fields as $field) {
            $schedule->{$field} = $request->input($field) ? 1 : 0;
        }

        $schedule->Checklist_Updated_By = auth()->check() ? auth()->user()->username : 'system';
        $schedule->Checklist_Updated_Date = now();
        $schedule->save();

        if (!$wasDone && $isDoneNow) {
            $this->flexibag->recordStuffingDeduction($schedule, $schedule->Checklist_Updated_By);
        }

        $this->containers->writeScheduleLog($schedule, 'CHECKLIST UPDATED');

        return response()->json([
            'status' => 'success',
            'message' => 'Checklist status pengiriman berhasil disimpan.',
        ]);
    }

    public function detailNoteInfo(Request $request)
    {
        $note = $this->notes->forOrder((string) $request->query('Order_No'));

        return response()->json([
            'url' => $this->notes->url($note),
            'name' => $note ? $note->Filename : null,
        ]);
    }

    public function detailNote(Request $request)
    {
        $note = $this->notes->forOrder((string) $request->query('Order_No'));

        if (!$note) {
            abort(404, 'Weighbridge note not found');
        }

        $path = public_path('filevendor/' . $note->Path);

        if (!is_file($path)) {
            abort(404, 'Weighbridge note file not found');
        }

        return response()->file($path);
    }
}
