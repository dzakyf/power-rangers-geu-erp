<?php

namespace App\Services\HCGA;

use App\Models\HCGA\CompanyGuideline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyGuidelineService
{
    /**
     * Get guidelines list for CMS datatable/grid
     */
    public function getCmsList(Request $request)
    {
        $query = CompanyGuideline::query();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'ACTIVE') {
                $query->where('is_active', 1);
            } elseif ($request->input('status') === 'INACTIVE') {
                $query->where('is_active', 0);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('type', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    /**
     * Get grouped active guidelines for mobile / portal
     */
    public function getActiveGuidelinesGrouped()
    {
        $all = CompanyGuideline::where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return [
            'visi'        => $all->where('type', CompanyGuideline::TYPE_VISI),
            'misi'        => $all->where('type', CompanyGuideline::TYPE_MISI),
            'core_values' => $all->where('type', CompanyGuideline::TYPE_CORE_VALUE),
            'peraturan'   => $all->where('type', CompanyGuideline::TYPE_PERATURAN),
        ];
    }

    /**
     * Find item by ID
     */
    public function findById($id)
    {
        return CompanyGuideline::findOrFail($id);
    }

    /**
     * Store new guideline
     */
    public function storeGuideline(Request $request)
    {
        $user = Auth::user();
        $username = $user ? $user->username : 'SYSTEM';

        $data = [
            'type'         => $request->input('type'),
            'title'        => $request->input('title'),
            'code'         => $request->input('code'),
            'summary'      => $request->input('summary'),
            'content'      => $request->input('content'),
            'icon'         => $request->input('icon') ?: 'fa fa-bookmark',
            'badge_color'  => $request->input('badge_color') ?: 'primary',
            'sort_order'   => (int) $request->input('sort_order', 0),
            'is_active'    => $request->has('is_active') ? 1 : 0,
            'created_by'   => $username,
            'created_date' => date('Y-m-d H:i:s'),
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('company_guidelines', $filename, 'public');
            $data['attachment_file'] = 'storage/' . $path;
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        return CompanyGuideline::create($data);
    }

    /**
     * Update guideline
     */
    public function updateGuideline($id, Request $request)
    {
        $guideline = $this->findById($id);
        $user = Auth::user();
        $username = $user ? $user->username : 'SYSTEM';

        $data = [
            'type'         => $request->input('type'),
            'title'        => $request->input('title'),
            'code'         => $request->input('code'),
            'summary'      => $request->input('summary'),
            'content'      => $request->input('content'),
            'icon'         => $request->input('icon') ?: 'fa fa-bookmark',
            'badge_color'  => $request->input('badge_color') ?: 'primary',
            'sort_order'   => (int) $request->input('sort_order', 0),
            'is_active'    => $request->has('is_active') ? 1 : 0,
            'updated_by'   => $username,
            'updated_date' => date('Y-m-d H:i:s'),
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('company_guidelines', $filename, 'public');
            $data['attachment_file'] = 'storage/' . $path;
            $data['attachment_name'] = $file->getClientOriginalName();
        } elseif ($request->input('remove_attachment') == '1') {
            $data['attachment_file'] = null;
            $data['attachment_name'] = null;
        }

        $guideline->update($data);
        return $guideline;
    }

    /**
     * Delete guideline
     */
    public function deleteGuideline($id)
    {
        $guideline = $this->findById($id);
        return $guideline->delete();
    }
}

