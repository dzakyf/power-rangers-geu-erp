@php
    $activeTab = $activeTab ?? 'handover';
    $tabs = [
        'handover'        => ['url' => 'hrd-asset-handover-index', 'label' => '1. Daftar & Buat Handover', 'icon' => 'fa-hands-helping'],
        'approval'        => ['url' => 'hrd-asset-handover-confirmation-index', 'label' => '2. Approval Handover', 'icon' => 'fa-clipboard-check'],
        'return'          => ['url' => 'hrd-asset-handover-returned-index', 'label' => '3. Pengajuan Return ke HR', 'icon' => 'fa-undo'],
        'return_approval' => ['url' => 'hrd-asset-handover-returned-approval-index', 'label' => '4. Approval Return HR/GA', 'icon' => 'fa-check-double'],
    ];
@endphp

<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach($tabs as $key => $tab)
        <a href="{{ url($tab['url']) }}"
           class="btn btn-sm {{ $activeTab === $key ? 'btn-primary text-white fw-semibold' : 'btn-outline-primary' }}">
            <i class="fa {{ $tab['icon'] }} me-1"></i> {{ $tab['label'] }}
        </a>
    @endforeach
</div>
