<div>
    <h5>Installment</h5>
    <p>Persiapan warehouse</p>


    <div class="card my-3">
        <div class="card-body">
            <h6>Kelengkapan Setup Warehouse</h6>
            <table class="table table-sm table-bordered mb-0">
                <tr>
                    <th style="width:240px;">Persyaratan</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>Proyeksi warehouse</td>
                    <td>
                        @if ($hasProjection)
                            <span class="badge badge-success">Selesai</span>
                        @else
                            <span class="badge badge-warning">Menunggu</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Manfaat &amp; dukungan (benefits asset)</td>
                    <td>
                        @if ($hasBenefitsAsset)
                            <span class="badge badge-success">Selesai</span>
                        @else
                            <span class="badge badge-warning">Menunggu</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Order peralatan (order equipment)</td>
                    <td>
                        @if ($hasOrderEquipment)
                            <span class="badge badge-success">Selesai</span>
                        @else
                            <span class="badge badge-warning">Menunggu</span>
                        @endif
                    </td>
                </tr>
            </table>
            @if ($hasProjection && $hasBenefitsAsset && $hasOrderEquipment)
            <div class="alert alert-success mt-2 mb-0">
                Semua persiapan warehouse selesai. Mitra berstatus Aktif.
            </div>
            @endif
        </div>
    </div>

    @if (!$warehouseType)
    <div class="alert alert-info">
        Tipe warehouse belum ditentukan. Pastikan paket sudah dipilih pada data pendaftaran.
    </div>
    @endif

    @if ($survey)
    <div class="card my-3">
        <div class="card-body">
            <h6>Survey Lokasi</h6>
            <table class="table table-sm table-bordered mb-0">
                <tr>
                    <th style="width:180px;">Lokasi Survey</th>
                    <td>{{ $survey->Address }}, {{ $survey->City }}</td>
                </tr>
                <tr>
                    <th>Skor</th>
                    <td>{{ $survey->Total_Score ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Rekomendasi</th>
                    <td>{{ $survey->Recommendation_Type ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $survey->Status ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    @if ($warehouseType)
    <div class="card my-3">
        <div class="card-body">
            <h6>Rencana Warehouse</h6>
            <table class="table table-sm table-bordered mb-0">
                @php
                $package = $partner->register_as === 'company' ? $partner->company_package : $partner->package_option;
                $typeLabel = $warehouseType === 'booth' ? 'Booth' : 'Mobil';
                @endphp
                <tr>
                    <th style="width:180px;">Tipe</th>
                    <td>{{ $typeLabel }}</td>
                </tr>
                <tr>
                    <th>Paket</th>
                    <td>{{ $package ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status Lokasi</th>
                    <td>{{ ucfirst($partner->location_status ?? '-') }}</td>
                </tr>
                @if ($partner->register_as === 'company')
                <tr>
                    <th>Jml Warehouse</th>
                    <td>{{ $partner->company_warehouse_count ?? '-' }}</td>
                </tr>
                @else
                <tr>
                    <th>Jml Warehouse</th>
                    <td>{{ $partner->warehouse_count ?? '-' }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    @php
    $categoryLabels = [
        'inventory' => 'Peralatan & Inventaris',
        'support' => 'Manfaat & Dukungan',
        'access' => 'Akses & Sistem',
    ];
    @endphp

    @foreach (['inventory', 'support', 'access'] as $cat)
    @if (isset($facilityGroups[$cat]) && count($facilityGroups[$cat]) > 0)
    <div class="card my-3">
        <div class="card-body">
            <h6>{{ $categoryLabels[$cat] ?? ucfirst($cat) }}</h6>
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Deskripsi</th>
                        @if ($cat === 'inventory')
                        <th>Tipe Asset</th>
                        @endif
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($facilityGroups[$cat] as $i => $f)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $f->description }}</td>
                        @if ($cat === 'inventory')
                        <td>{{ $f->asset_type ?? '-' }}</td>
                        @endif
                        <td>
                            @if (is_array($f->children))
                            {{ implode(', ', $f->children) }}
                            @elseif ($f->children)
                            {{ $f->children }}
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach
    @endif
</div>