<?php

namespace App\Http\Controllers\HCGA\Performance;

use App\Http\Controllers\Controller;
use App\PerformanceCompetency;
use App\PerformanceCompetencyVersion;
use App\PerformanceSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceCompetencyController extends Controller
{
    public function index(Request $request)
    {
        $versions = PerformanceCompetencyVersion::orderBy('version_no', 'desc')->get();
        $selectedVersionId = $request->get('version_id');

        $activeVersion = $selectedVersionId
            ? $versions->firstWhere('id', (int) $selectedVersionId)
            : ($versions->firstWhere('status', 'DRAFT') ?: $versions->firstWhere('status', 'PUBLISHED'));

        $competencies = $activeVersion
            ? PerformanceCompetency::where('version_id', $activeVersion->id)
                ->orderBy('sort_order', 'asc')
                ->orderBy('category_group', 'asc')
                ->orderBy('id', 'asc')
                ->get()
            : collect();

        $publishedVersion = $versions->firstWhere('status', 'PUBLISHED');

        return view('humanresources.performance.competency.index', compact(
            'versions',
            'activeVersion',
            'publishedVersion',
            'competencies'
        ));
    }

    /**
     * Buat Draft Versi Baru dengan menyalin seluruh item & konfigurasi skala dari versi Published/terpilih
     */
    public function createDraftVersion(Request $request)
    {
        $request->validate([
            'source_version_id' => 'nullable|integer|exists:performance_competency_versions,id',
            'version_label'     => 'required|string|max:100',
            'notes'             => 'nullable|string',
        ]);

        $existingDraft = PerformanceCompetencyVersion::where('status', 'DRAFT')->first();
        if ($existingDraft) {
            return redirect()
                ->route('hrd.performance.competency.index', ['version_id' => $existingDraft->id])
                ->with('warning', "Masih ada Draft ({$existingDraft->version_label}) yang belum di-publish. Silakan selesaikan atau publish draft tersebut terlebih dahulu.");
        }

        $newVersion = DB::transaction(function () use ($request) {
            $source = $request->source_version_id
                ? PerformanceCompetencyVersion::find($request->source_version_id)
                : PerformanceCompetencyVersion::getActivePublished();

            $nextNo = ((int) PerformanceCompetencyVersion::max('version_no')) + 1;

            $draft = PerformanceCompetencyVersion::create([
                'version_no'    => $nextNo,
                'version_label' => $request->version_label,
                'status'        => 'DRAFT',
                'scale_min'     => $source ? $source->scale_min : 1,
                'scale_max'     => $source ? $source->scale_max : 5,
                'scale_labels'  => $source ? $source->scale_labels : null,
                'notes'         => $request->notes,
                'created_by'    => Auth::id(),
            ]);

            if ($source) {
                $sourceItems = PerformanceCompetency::where('version_id', $source->id)->orderBy('sort_order')->get();
                foreach ($sourceItems as $item) {
                    PerformanceCompetency::create([
                        'version_id'            => $draft->id,
                        'code'                  => $item->code,
                        'name'                  => $item->name,
                        'type'                  => $item->type,
                        'category_group'        => $item->category_group,
                        'description'           => $item->description,
                        'behavioral_indicators' => $item->behavioral_indicators,
                        'target_level'          => $item->target_level,
                        'sort_order'            => $item->sort_order,
                        'is_active'             => $item->is_active,
                    ]);
                }
            }

            return $draft;
        });

        return redirect()
            ->route('hrd.performance.competency.index', ['version_id' => $newVersion->id])
            ->with('success', "Draft {$newVersion->version_label} berhasil dibuat.");
    }

    /**
     * Publish versi DRAFT menjadi versi aktif (versi PUBLISHED sebelumnya otomatis diarsipkan)
     */
    public function publishVersion($versionId)
    {
        $draft = PerformanceCompetencyVersion::findOrFail($versionId);
        if ($draft->status !== 'DRAFT') {
            return redirect()->back()->with('warning', 'Hanya versi berstatus DRAFT yang dapat di-publish.');
        }

        $itemCount = PerformanceCompetency::where('version_id', $draft->id)->where('is_active', true)->count();
        if ($itemCount === 0) {
            return redirect()->back()->with('warning', 'Minimal harus ada 1 poin Soft Skill aktif sebelum versi dapat di-publish.');
        }

        DB::transaction(function () use ($draft) {
            PerformanceCompetencyVersion::where('status', 'PUBLISHED')->update(['status' => 'ARCHIVED']);

            $draft->update([
                'status'       => 'PUBLISHED',
                'published_at' => Carbon::now(),
                'published_by' => Auth::id(),
            ]);

            PerformanceSetting::setValue('competency_scale_min', (string) $draft->scale_min, Auth::id());
            PerformanceSetting::setValue('competency_scale_max', (string) $draft->scale_max, Auth::id());
        });

        return redirect()
            ->route('hrd.performance.competency.index', ['version_id' => $draft->id])
            ->with('success', "Versi {$draft->version_label} resmi di-publish dan aktif digunakan.");
    }

    /**
     * Update konfigurasi skala penilaian (Likert 1-5, 1-10, atau custom) pada versi DRAFT
     */
    public function updateVersionScale(Request $request, $versionId)
    {
        $version = PerformanceCompetencyVersion::findOrFail($versionId);
        if ($version->status !== 'DRAFT') {
            return redirect()->back()->with('warning', 'Skala hanya dapat diubah pada versi DRAFT. Buat Draft Versi Baru untuk menjaga histori.');
        }

        $request->validate([
            'scale_min' => 'required|integer|min:0|max:10',
            'scale_max' => 'required|integer|min:2|max:100|gt:scale_min',
        ]);

        $version->update([
            'scale_min' => (int) $request->scale_min,
            'scale_max' => (int) $request->scale_max,
            'notes'     => $request->notes ?? $version->notes,
        ]);

        return redirect()->back()->with('success', "Skala penilaian pada {$version->version_label} diperbarui menjadi {$version->scale_min} - {$version->scale_max}.");
    }

    public function store(Request $request)
    {
        $version = PerformanceCompetencyVersion::findOrFail($request->version_id);
        if ($version->status !== 'DRAFT') {
            return redirect()->back()->with('warning', 'Penambahan poin hanya dapat dilakukan pada versi DRAFT. Buat Draft Versi Baru terlebih dahulu.');
        }

        $request->validate([
            'version_id'     => 'required|exists:performance_competency_versions,id',
            'code'           => 'required|string|max:50',
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:CORE,LEADERSHIP,FUNCTIONAL',
            'category_group' => 'nullable|string|max:100',
            'target_level'   => 'required|integer|min:1|max:100',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $exists = PerformanceCompetency::where('version_id', $version->id)->where('code', $code)->exists();
        if ($exists) {
            return redirect()->back()->with('warning', "Kode {$code} sudah ada di versi ini.");
        }

        PerformanceCompetency::create([
            'version_id'            => $version->id,
            'code'                  => $code,
            'name'                  => $request->name,
            'type'                  => $request->type,
            'category_group'        => $request->category_group,
            'target_level'          => (int) $request->target_level,
            'sort_order'            => (int) ($request->sort_order ?? 0),
            'description'           => $request->description,
            'behavioral_indicators' => $request->behavioral_indicators,
            'is_active'             => true,
        ]);

        return redirect()->back()->with('success', 'Poin Soft Skill / Kompetensi berhasil ditambahkan ke Draft.');
    }

    public function update(Request $request, $id)
    {
        $item = PerformanceCompetency::findOrFail($id);
        $version = PerformanceCompetencyVersion::findOrFail($item->version_id);
        if ($version->status !== 'DRAFT') {
            return redirect()->back()->with('warning', 'Item pada versi Published/Archived terkunci. Buat Draft Versi Baru untuk mengubah.');
        }

        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:CORE,LEADERSHIP,FUNCTIONAL',
            'category_group' => 'nullable|string|max:100',
            'target_level'   => 'required|integer|min:1|max:100',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $item->update([
            'name'                  => $request->name,
            'type'                  => $request->type,
            'category_group'        => $request->category_group,
            'target_level'          => (int) $request->target_level,
            'sort_order'            => (int) ($request->sort_order ?? 0),
            'description'           => $request->description,
            'behavioral_indicators' => $request->behavioral_indicators,
        ]);

        return redirect()->back()->with('success', 'Poin Soft Skill berhasil diperbarui.');
    }

    public function toggleActive($id)
    {
        $item = PerformanceCompetency::findOrFail($id);
        $version = PerformanceCompetencyVersion::findOrFail($item->version_id);
        if ($version->status !== 'DRAFT') {
            return redirect()->back()->with('warning', 'Item pada versi Published/Archived tidak dapat diubah.');
        }

        $item->update(['is_active' => !$item->is_active]);
        return redirect()->back()->with('success', 'Status poin Soft Skill berhasil diubah.');
    }

    public function destroy($id)
    {
        $item = PerformanceCompetency::findOrFail($id);
        $version = PerformanceCompetencyVersion::findOrFail($item->version_id);
        if ($version->status !== 'DRAFT') {
            return redirect()->back()->with('warning', 'Hanya poin pada versi DRAFT yang dapat dihapus.');
        }

        $item->delete();
        return redirect()->back()->with('success', 'Poin Soft Skill berhasil dihapus dari Draft.');
    }
}
