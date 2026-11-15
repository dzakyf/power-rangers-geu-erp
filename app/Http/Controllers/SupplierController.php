<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    // Menampilkan Halaman View Supplier List
    public function supplierList() {
        return view('humanresources.procurement.supplier-list');
    }

    // Endpoint XML Data Supplier untuk DHTMLX Grid
    public function xmlSupplierList() {
        $suppliers = DB::table('supplier')->orderBy('ID', 'DESC')->get();
        
        $xml = '<?xml version="1.0" encoding="iso-8859-1"?>';
        $xml .= '<rows>';
        
        $nomor = 1;
        foreach ($suppliers as $row) {
            $xml .= '<row id="' . $row->ID . '">';
            $xml .= '<cell>' . $nomor . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Supplier_ID ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Company_Name ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Contact_Person ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->City ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Address ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Phone ?? $row->HP ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Email ?? '-') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row->Status ?? 'ACTIVE') . '</cell>';
            $xml .= '</row>';
            $nomor++;
        }
        
        $xml .= '</rows>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    // Menyimpan Supplier Baru
    public function storeSupplier(Request $request) {
        $request->validate([
            'supplier_id'    => 'required|string|unique:supplier,Supplier_ID',
            'company_name'   => 'required|string',
            'contact_person' => 'nullable|string',
            'city'           => 'nullable|string',
            'phone'          => 'nullable|string',
            'email'          => 'nullable|email|max:255',
            'status'         => 'required|in:ACTIVE,NONACTIVE',
        ]);

        // Ambil Location dari session user login
        $username = session('username') ?? 'admin';
        $userData = DB::table('users')->where('username', $username)->first();
        $userLocation = $userData ? $userData->Location : 'GEU-HO';

        DB::table('supplier')->insert([
            'Supplier_ID'    => $request->supplier_id,
            'Company_Name'   => $request->company_name,
            'Contact_Person' => $request->contact_person,
            'Address'        => $request->address,
            'City'           => $request->city,
            'Phone'          => $request->phone,
            'HP'             => $request->phone,
            'Email'          => $request->email,
            'Status'         => $request->status,
            'Location'       => $userLocation,
            'Created_Date'   => now(),
            'Created_By'     => $username,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data supplier baru berhasil ditambahkan!'
        ]);
    }

    // Ambil Data untuk Edit Supplier
    public function editSupplier($id) {
        $supplier = DB::table('supplier')->where('ID', $id)->first();
        if (!$supplier) {
            return response()->json(['status' => 'error', 'message' => 'Supplier tidak ditemukan!'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $supplier]);
    }

    // Update Data Supplier
    public function updateSupplier(Request $request, $id) {
        $request->validate([
            'company_name'   => 'required|string',
            'contact_person' => 'nullable|string',
            'city'           => 'nullable|string',
            'phone'          => 'nullable|string',
            'email'          => 'nullable|email|max:255',
            'status'         => 'required|in:ACTIVE,NONACTIVE',
        ]);

        $supplier = DB::table('supplier')->where('ID', $id)->first();
        if (!$supplier) {
            return response()->json(['status' => 'error', 'message' => 'Supplier tidak ditemukan!'], 404);
        }

        DB::table('supplier')->where('ID', $id)->update([
            'Company_Name'   => $request->company_name,
            'Contact_Person' => $request->contact_person,
            'Address'        => $request->address,
            'City'           => $request->city,
            'Phone'          => $request->phone,
            'HP'             => $request->phone,
            'Email'          => $request->email,
            'Status'         => $request->status,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data supplier berhasil diperbarui!'
        ]);
    }
}