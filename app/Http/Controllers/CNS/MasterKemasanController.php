<?php

namespace App\Http\Controllers\CNS;

use App\CnsInventoryKemasan;
use App\CnsMasterKemasan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MasterKemasanController extends Controller
{
    public function index()
    {
        return view('cns.master.kemasan');
    }

    public function xmlData(Request $request)
    {
        $name = trim((string) $request->query('name'));

        $query = CnsMasterKemasan::query()->orderBy('Name', 'asc');
        if ($name !== '') {
            $query->where('Name', 'like', "%{$name}%");
        }

        $items = $query->get();

        header('Content-Type: text/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<rows>\n";

        $no = 1;
        foreach ($items as $item) {
            echo "<row id=\"{$item->Kemasan_ID}\">\n";
            echo "<cell>{$no}</cell>\n";
            echo "<cell>{$item->Kemasan_ID}</cell>\n";
            echo "<cell>" . htmlspecialchars($item->Name) . "</cell>\n";
            echo "<cell>" . number_format((float) $item->Volume, 2, '.', ',') . "</cell>\n";
            echo "<cell>" . ($item->isAmpas() ? 'Ampas' : 'Kemasan') . "</cell>\n";
            echo "<cell>" . ($item->isAmpas() || $item->Max_Stock === null ? '-' : number_format((float) $item->Max_Stock, 2, '.', ',')) . "</cell>\n";
            echo "<cell>" . ($item->Unit !== null && $item->Unit !== '' ? htmlspecialchars($item->Unit) : '-') . "</cell>\n";
            echo "<cell>" . number_format((float) ($item->Price ?? 0), 2, '.', ',') . "</cell>\n";
            echo "<cell>" . ($item->created_at ? $item->created_at->format('d/m/Y H:i') : '-') . "</cell>\n";
            echo "<cell>" . ($item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-') . "</cell>\n";
            echo "</row>\n";
            $no++;
        }

        echo "</rows>";
        exit;
    }

    protected function validatePayload(Request $request): array
    {
        $name = trim((string) $request->input('Name'));
        $request->merge(['Name' => $name]);
        $isAmpas = $request->boolean('Is_Ampas') || stripos($name, 'ampas') !== false;

        $validated = $request->validate([
            'Name'      => 'required|string|max:150',
            'Volume'    => 'required|numeric|min:0',
            'Max_Stock' => $isAmpas ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'Unit'      => 'nullable|string|max:50',
            'Price'     => 'nullable|numeric|min:0',
        ]);

        return [
            'Name'      => $validated['Name'],
            'Volume'    => (float) $validated['Volume'],
            'Is_Ampas'  => $isAmpas,
            'Max_Stock' => $isAmpas ? null : (float) $validated['Max_Stock'],
            'Unit'      => trim((string) ($validated['Unit'] ?? '')),
            'Price'     => isset($validated['Price']) && $validated['Price'] !== '' ? (float) $validated['Price'] : 0.0,
        ];
    }

    public function store(Request $request)
    {
        CnsMasterKemasan::create($this->validatePayload($request));

        return back()->with('success', 'Master kemasan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kemasan = CnsMasterKemasan::findOrFail($id);
        $kemasan->update($this->validatePayload($request));

        return back()->with('success', 'Master kemasan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kemasan = CnsMasterKemasan::findOrFail($id);

        if (Schema::hasTable('cns_inventory_kemasan') && CnsInventoryKemasan::where('packaging_id', $kemasan->Kemasan_ID)->exists()) {
            return back()->with('error', 'Kemasan tidak dapat dihapus karena sudah digunakan di Inventory Kemasan.');
        }

        $kemasan->delete();

        return back()->with('success', 'Master kemasan berhasil dihapus.');
    }
}
