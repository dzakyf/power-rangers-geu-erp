<?php

namespace App\Http\Controller\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterOfficeController extends Controller
{
    /**
     * dhtmlx dataProcessor handler for the Accounting "Locations" master grid.
     * Receives row transactions (inserted/updated/deleted) and syncs the
     * `location` table, returning the dhtmlx XML acknowledgement.
     */
    public function submit(Request $request)
    {
        $param = [];
        parse_str($request->getContent(), $param);

        $ids = isset($param['ids']) ? explode(',', $param['ids']) : [];
        $user = Auth::user();
        $company = ($user && $user->Company_ID) ? $user->Company_ID : 'GEU';
        $username = $user ? $user->username : 'system';

        $xml = '<data>';
        foreach ($ids as $rowId) {
            $rowId = trim($rowId);
            if ($rowId === '') {
                continue;
            }

            $mode = $param[$rowId . '_!nativeeditor_status'] ?? '';

            // Grid column -> _cN mapping (N = 0-based grid column index)
            $name     = $param[$rowId . '_c1'] ?? '';
            $type     = trim($param[$rowId . '_c2'] ?? '');
            $locId    = trim($param[$rowId . '_c3'] ?? '');
            $address  = $param[$rowId . '_c4'] ?? '';
            $city     = $param[$rowId . '_c5'] ?? '';
            $phone    = $param[$rowId . '_c6'] ?? '';
            $pic      = $param[$rowId . '_c7'] ?? '';
            $mainCity = $param[$rowId . '_c8'] ?? '';
            $lat      = $param[$rowId . '_c9'] ?? '';
            $lng      = $param[$rowId . '_c10'] ?? '';
            $type     = $type !== '' ? $type : 'Branch Office';

            try {
                switch ($mode) {
                    case 'inserted':
                        if ($locId === '') {
                            $locId = $this->generateLocationId($type);
                        }
                        $newId = DB::table('location')->insertGetId([
                            'Company_ID'    => $company,
                            'Location_Type' => $type,
                            'Location_ID'   => $locId,
                            'Name'          => $name,
                            'Address'       => $address,
                            'Street'        => '',
                            'City'          => $city,
                            'Phone'         => $phone,
                            'Phone_Ext'     => '',
                            'Fax'           => '',
                            'PIC'           => $pic,
                            'IDCity'        => '',
                            'IDCountry'     => 'IDN',
                            'ZipCode'       => '',
                            'MainCity'      => $mainCity,
                            'Longitude'     => $lng,
                            'Latitude'      => $lat,
                            'UpdateBy'      => $username,
                            'Location_Code' => '',
                        ]);
                        $xml .= "<action type='insert' sid='" . htmlspecialchars($rowId) . "' tid='" . (int) $newId . "'/>";
                        break;

                    case 'updated':
                        DB::table('location')->where('ID', $rowId)->update([
                            'Location_Type' => $type,
                            'Name'          => $name,
                            'Address'       => $address,
                            'City'          => $city,
                            'Phone'         => $phone,
                            'PIC'           => $pic,
                            'MainCity'      => $mainCity,
                            'Longitude'     => $lng,
                            'Latitude'      => $lat,
                            'UpdateBy'      => $username,
                        ]);
                        $xml .= "<action type='update' sid='" . htmlspecialchars($rowId) . "' tid='" . htmlspecialchars($rowId) . "'/>";
                        break;

                    case 'deleted':
                        DB::table('location')->where('ID', $rowId)->delete();
                        $xml .= "<action type='delete' sid='" . htmlspecialchars($rowId) . "' tid='" . htmlspecialchars($rowId) . "'/>";
                        break;
                }
            } catch (\Exception $e) {
                Log::error('MasterOffice submit failed: ' . $e->getMessage());
            }
        }
        $xml .= '</data>';

        return response($xml)->header('Content-Type', 'text/xml');
    }

    /**
     * Auto-generate Location_ID as HO-{n} / BR-{n}, where n = max existing
     * numeric suffix for that prefix + 1 (never collides after deletes).
     */
    protected function generateLocationId($type)
    {
        $prefix = (stripos($type, 'head') !== false) ? 'HO' : 'BR';

        $max = 0;
        $codes = DB::table('location')->where('Location_ID', 'like', $prefix . '-%')->pluck('Location_ID');
        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $prefix . '-' . ($max + 1);
    }
}
