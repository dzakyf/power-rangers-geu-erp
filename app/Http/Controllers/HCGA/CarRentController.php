<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarRentController extends Controller
{
    public function index()
    {
        return view('humanresources.car-rent.index');
    }

    public function data(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $priv = Auth::user()->Priv_ID;
        $nik = Auth::user()->Emp_ID;

        $getCarRent = DB::table('car_rent');

        // Filter: hanya tampilkan data milik user atau yang relevan
        if ($priv != 'HCGA') {
            $getCarRent->where('User_NIK', $nik);
        }

        $getCarRent = $getCarRent->get();

        $nomor = 1;
        echo "<rows>";
        foreach ($getCarRent as $key => $value) {
            echo "<row id='$value->ID'>";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($value->Car_Rent_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_NIK) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_Jabatan) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->User_Department) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Site_Office) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Sim_Link) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Keperluan) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Tujuan_Penggunaan) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Tanggal_Peminjaman) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Jam_Peminjaman) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Tanggal_Pengembalian) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Jam_Pengembalian) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Jenis_Mobil) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Kerusakan_Denda) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Bukti_Pembayaran) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->Status) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function create()
    {
        $LSP = Auth::user()->Location;
        $NIK = Auth::user()->Emp_ID;
        $Saya = Auth::user()->username;
        $counter = DB::table('car_rent')->where("Created_By", "LIKE", date("Y-m") . "%")->count() + 1;
        $Car_Rent_No = "CR/GA/" . $LSP . "/" . date("ymdhis") . "-" . $counter;
        return view('humanresources.car-rent.create', compact('Car_Rent_No', 'NIK', 'Saya'));
    }

    public function store(Request $request)
    {
        $r = $request->all();

        unset($r['_token']);
        unset($r['SubmitCarRent']);

        $r['Created_By'] = Auth::user()->username;
        $r['Created_Date'] = now();
        $r['Status'] = "CREATED";

        DB::table('car_rent')->insert($r);

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menginput data peminjaman mobil",
        ]);
    }

    public function detail(Request $request)
    {
        $cr = DB::table('car_rent')->where('Car_Rent_No', $request->Car_Rent_No)->first();
        return view('humanresources.car-rent.detail', compact('cr'));
    }

    public function edit(Request $request)
    {
        $cr = DB::table('car_rent')->where('Car_Rent_No', $request->Car_Rent_No)->first();
        return view('humanresources.car-rent.edit', compact('cr'));
    }

    public function update(Request $request)
    {
        $r = $request->all();

        unset($r['ID']);
        unset($r['_token']);
        unset($r['SubmitCarRent']);

        $r['Updated_By'] = Auth::user()->username;
        $r['Updated_Date'] = now();

        DB::table('car_rent')->where('ID', $request->ID)->update($r);

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil update data",
        ]);
    }
}
