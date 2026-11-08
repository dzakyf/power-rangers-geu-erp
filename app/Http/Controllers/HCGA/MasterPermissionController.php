<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\MasterPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MasterPermissionController extends Controller
{
    protected $permissionService;

    public function __construct(MasterPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Tampilan Halaman Modern Master Permission
     */
    public function index()
    {
        $jenisList = $this->permissionService->getJenisIzinList();
        $totalKategori = DB::table('kategori_izin')->count();
        $totalAktif = DB::table('kategori_izin')->where('status', 'ACTIVE')->count();
        $potongCuti = DB::table('kategori_izin')->where('is_deduct_annual_leave', 'YES')->count();
        $wajibLampiran = DB::table('kategori_izin')->where('is_need_attachment', 'YES')->count();

        return view('humanresources.permission.master-permission', compact('jenisList', 'totalKategori', 'totalAktif', 'potongCuti', 'wajibLampiran'));
    }

    /**
     * XML Feed Data Grid Master Permission
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

        $categories = $this->permissionService->getCategories();

        $nomor = 1;
        foreach ($categories as $fetch) {
            $code = $fetch->kat_izin_code ?: '';
            $nama = $fetch->kat_izin_nama ?: '';
            $groupId = $fetch->izin_jenis_id ?: 1;
            $days = (int) $fetch->jatah_hari;
            $deduct = $fetch->is_deduct_annual_leave ?: 'NO';
            $attachment = $fetch->is_need_attachment ?: 'NO';
            $status = $fetch->status ?: 'ACTIVE';

            echo "<row id=\"$fetch->kat_izin_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($code) . "</cell>";
            echo "<cell>" . htmlspecialchars($nama) . "</cell>";
            echo "<cell>" . htmlspecialchars($groupId) . "</cell>";
            echo "<cell>$days</cell>";
            echo "<cell>" . htmlspecialchars($deduct) . "</cell>";
            echo "<cell>" . htmlspecialchars($attachment) . "</cell>";
            echo "<cell>" . htmlspecialchars($status) . "</cell>";
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

        if (!empty($param["ids"])) {
            $checkids = explode(",", $param["ids"]);
            for ($i = 0; $i < sizeof($checkids); $i++) {
                $rowId = $checkids[$i];
                $newId = $rowId;
                $mode = $param[$rowId . "_!nativeeditor_status"] ?? '';
                $action = 'error';

                switch ($mode) {
                    case "updated":
                        $action = $this->permissionService->updateCategoryRow($rowId, $param);
                        break;
                    case "deleted":
                        $action = $this->permissionService->deleteCategoryRow($rowId);
                        break;
                    case "inserted":
                        $action = $this->permissionService->insertCategoryRow($rowId, $param);
                        break;
                }

                echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
            }
        }

        echo "</data>";
    }
}

