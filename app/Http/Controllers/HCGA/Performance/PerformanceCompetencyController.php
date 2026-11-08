<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformanceCompetency;
use Illuminate\Http\Request;

class PerformanceCompetencyController extends Controller
{
    public function index()
    {
        $competencies = PerformanceCompetency::orderBy('type', 'asc')->orderBy('name', 'asc')->get();
        return view('humanresources.performance.competency.index', compact('competencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'         => 'required|string|max:50|unique:performance_competencies,code',
            'name'         => 'required|string|max:150',
            'type'         => 'required|in:CORE,LEADERSHIP,FUNCTIONAL',
            'target_level' => 'required|integer|min:1|max:5',
        ]);

        PerformanceCompetency::create([
            'code'                  => strtoupper(trim($request->code)),
            'name'                  => $request->name,
            'type'                  => $request->type,
            'target_level'          => $request->target_level,
            'description'           => $request->description,
            'behavioral_indicators' => $request->behavioral_indicators,
            'is_active'             => true,
        ]);

        return redirect()->back()->with('success', 'Kompetensi baku berhasil ditambahkan.');
    }
}

