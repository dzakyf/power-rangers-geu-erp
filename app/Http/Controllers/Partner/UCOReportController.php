<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class UCOReportController extends Controller
{

    private $userService;
    public function __construct(UserService $u)
    {
        $this->userService = $u;
    }

    public function indexInboundSource()
    {
        return view('uco.report-inbound-source');
    }

    public function indexInboundUCO()
    {
        return view('uco.inbound');
    }

    public function createInboundUCO()
    {
        return view('uco.create-inbound');
    }

    public function getUcoInboundXml()
    {
        try {
            $data = DB::table('uco_inbound as ui')
                ->select(
                    'ui.*',
                    'users.name as User_Name'
                )
                ->join('users', 'ui.Picked_Up_By', '=', 'users.id')
                ->get();

            $xml = new SimpleXMLElement('<rows/>');

            $counter = 1;

            foreach ($data as $d) {
                $row = $xml->addChild('row');

                $row->addChild('cell', $counter);
                $row->addChild('cell', htmlspecialchars($d->WO_No));
                $row->addChild('cell', htmlspecialchars($d->POO_Name));
                $row->addChild('cell', $d->Weight);
                $row->addChild('cell', htmlspecialchars($d->Unit));
                $row->addChild('cell', $d->User_Name);
                $row->addChild('cell', $d->Created_At);
                $row->addChild('cell', $d->Inbound_Date);

                $counter++;
            }

            return response($xml->asXML(), 200)
                ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitInboundUCO(Request $request)
    {
        $b = $request->all();
        $session = $this->userService->getCurrentSessionUserInfo();

        try {
            DB::beginTransaction();

            DB::table('uco_inbound')->insert([
                'POO_Name'   => $b['POO_Name'],
                'Weight'     => $b['Weight'],
                'Picked_Up_By'    => $session->id, // Created_By
                'Unit'       => $b['Unit'],
                'Inbound_Date'       => $b['Inbound_Date'],
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Data berhasil disimpan.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // request pick up ke Site GEU (dari mitra(express) ke site)
    public function requestPickupUCO()
    {
        return;
    }
}
