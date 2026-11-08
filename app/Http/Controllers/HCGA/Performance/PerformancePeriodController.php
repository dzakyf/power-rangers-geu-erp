<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformancePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformancePeriodController extends Controller
{
    public function index()
    {
        $periods = PerformancePeriod::orderBy('id', 'desc')->paginate(15);
        return view('humanresources.performance.period.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_code' => 'required|string|max:50|unique:performance_periods,period_code',
            'name'        => 'required|string|max:150',
            'period_type' => 'required|in:ANNUAL,SEMESTER,QUARTERLY,MONTHLY',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        PerformancePeriod::create([
            'period_code' => strtoupper(trim($request->period_code)),
            'name'        => $request->name,
            'period_type' => $request->period_type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => 'ACTIVE',
            'description' => $request->description,
            'created_by'  => Auth::id() ?? 1,
            'updated_by'  => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Master Periode berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:DRAFT,ACTIVE,EVALUATION,CLOSED',
        ]);

        $period = PerformancePeriod::findOrFail($id);
        $period->update([
            'status'     => $request->status,
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json(['success' => true, 'message' => "Status periode {$period->period_code} berhasil diubah menjadi {$period->status}."]);
    }
}

