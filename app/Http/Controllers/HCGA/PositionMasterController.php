<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\PositionMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PositionMasterController extends Controller
{
    protected $positionMasterService;

    public function __construct(PositionMasterService $positionMasterService)
    {
        $this->positionMasterService = $positionMasterService;
    }

    /**
     * Tampilan Halaman Master Position
     */
    public function index()
    {
        return view('humanresources.master.position');
    }

    /**
     * XML Feed Data Grid Master Position
     */
    public function xmlData()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }

        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
        echo "<rows>";

        $parents = DB::table('master_position')->pluck('Position', 'ID')->all();
        $positions = $this->positionMasterService->getPositionsQuery();

        $nomor = 1;
        foreach ($positions as $fetch) {
            $status = $fetch->Status ?: 'ACTIVE';
            $posType = $fetch->Position_Type ?: 'STRUCTURAL';
            // Jika kosong / null, set ke PAID agar tampil jelas sebagai PAID (Default)
            $compensation = $fetch->overtime_compensation ?: 'PAID';

            echo "<row id=\"$fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Directorat ?: '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Department ?: '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Subsection ?: '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Position ?: '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Initial_Code ?: '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Initial ?: '') . "</cell>";
            echo "<cell>" . (int) $fetch->Level . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Parent_Position_ID ?: '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Location ?: 'GEU-HO') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Company_ID ?: 'GEU') . "</cell>";
            echo "<cell>" . htmlspecialchars($posType) . "</cell>";
            echo "<cell>" . htmlspecialchars($status) . "</cell>";
            echo "<cell>" . htmlspecialchars($compensation) . "</cell>"; // c13: Overtime Compensation
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Handler Update / Insert / Delete dari dhtmlx DataProcessor
     */
    public function update(Request $request)
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));
        $ids = explode("&", $request_body);
        $param = [];
        foreach ($ids as $datax) {
            $idenya = explode("=", $datax);
            if (isset($idenya[0]) && isset($idenya[1])) {
                $param[$idenya[0]] = $idenya[1];
            }
        }

        $syncExisting = ($request->input('sync_existing') == '1' || ($param['sync_existing'] ?? '') == '1');

        if (!empty($param["ids"])) {
            $checkids = explode(",", $param["ids"]);
            for ($i = 0; $i < sizeof($checkids); $i++) {
                $rowId = $checkids[$i];
                $newId = $rowId;
                $mode = $param[$rowId . "_!nativeeditor_status"] ?? '';
                $action = 'error';

                switch ($mode) {
                    case "updated":
                        $action = $this->positionMasterService->updatePositionRow($rowId, $param, $syncExisting);
                        break;
                    case "deleted":
                        $action = $this->positionMasterService->deletePositionRow($rowId);
                        break;
                    case "inserted":
                        $action = $this->positionMasterService->insertPositionRow($rowId, $param);
                        break;
                }

                echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
            }
        }

        echo "</data>";
    }

    /**
     * Endpoint API Sinkronisasi Massal Karyawan Existing berdasarkan Jabatan Terpilih
     */
    public function syncExisting(Request $request)
    {
        $request->validate([
            'position_id'  => 'required|integer',
            'compensation' => 'nullable|in:PAID,TIME_OFF,DEFAULT',
        ]);

        $posId = (int) $request->input('position_id');
        $comp = $request->input('compensation');
        if ($comp === 'DEFAULT') {
            $comp = null; // Reset override
        }

        $res = $this->positionMasterService->syncExistingByPositionId($posId, $comp);

        return response()->json($res);
    }
}

