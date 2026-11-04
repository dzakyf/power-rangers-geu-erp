<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecruitmentDetailController extends Controller
{
    public function updateDetailData(Request $request)
    {
        $request->validate([
            'ID' => 'required|integer|exists:recruitment,ID',
            'Initial' => 'nullable|string|max:50',
            'Religion' => 'nullable|string|max:50',
            'Gender' => 'nullable|in:MALE,FEMALE,L,P',
            'Birth_Date' => 'nullable|date',
            'Birth_Place' => 'nullable|string|max:255',
            'KTP' => 'nullable|digits:16',
            'kewarganegaraan' => 'nullable|string|max:100',
            'Marital_Status' => 'nullable|string|max:50',
            'Blood' => 'nullable|in:A,B,AB,O',
            'Blood_Rhesus' => 'nullable|in:+,-',
            'Address' => 'nullable|string',
            'City' => 'nullable|string|max:255',
            'Phone' => 'nullable|numeric',
            'Handphone' => 'nullable|numeric',
            'Address_Living' => 'nullable|string',
            'Address_Living_City' => 'nullable|string|max:255',
            'Email_Individual' => 'nullable|email',
            'NPWP' => 'nullable|string|max:50',
        ]);

        $id = $request->input('ID');

        DB::table('recruitment')->where('ID', $id)->update([
            'Initial' => $request->input('Initial'),
            'Religion' => $request->input('Religion'),
            'Gender' => $request->input('Gender'),
            'Birth_Date' => $request->input('Birth_Date'),
            'Birth_Place' => $request->input('Birth_Place'),
            'KTP' => $request->input('KTP'),
            'kewarganegaraan' => $request->input('kewarganegaraan'),
            'Marital_Status' => $request->input('Marital_Status'),
            'Blood' => $request->input('Blood'),
            'Blood_Rhesus' => $request->input('Blood_Rhesus'),
            'Address' => $request->input('Address'),
            'City' => $request->input('City'),
            'Phone' => $request->input('Phone'),
            'Handphone' => $request->input('Handphone'),
            'Address_Living' => $request->input('Address_Living'),
            'City_Living' => $request->input('Address_Living_City'),
            'Email_Individual' => $request->input('Email_Individual'),
            'NPWP' => $request->input('NPWP'),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Data updated.']);
    }
}
