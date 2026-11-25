<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Edit PR</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --emerald-dark: #064E3B;
            --emerald-primary: #047857;
            --emerald-soft: #ECFDF5;
            --bg-canvas: #F6F8F7;
            --text-dark: #111827;
            --text-muted: #6B7280;
            --border-soft: #E5EBE8;
        }
        body {
            background-color: var(--bg-canvas);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            padding-bottom: 92px;
        }
        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            padding: 16px 16px 24px;
        }
        .top-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .btn-back-soft {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            flex-shrink: 0;
        }
        .search-box {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 10px 14px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 13px;
            background: transparent;
        }
        .pr-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 10px;
            text-decoration: none;
            color: inherit;
            display: block;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .pr-card:hover {
            border-color: #A7F3D0;
            color: inherit;
        }
        .badge-status {
            font-size: 10.5px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            background: #ECFDF5;
            color: #047857;
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <div class="top-bar">
            <a href="{{ route('procurement.mobile.index') }}" class="btn-back-soft" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h6 fw-bold mb-0">Edit Purchase Request</h1>
                <div class="small text-muted">Pilih PR untuk edit lampiran, keterangan, Hold / Cancel</div>
            </div>
        </div>

        <div class="search-box">
            <i class="fas fa-search text-muted"></i>
            <input type="text" id="searchPrInput" placeholder="Cari nomor PR, department, atau status..." oninput="filterPrList()">
        </div>

        <div id="prListContainer">
            @forelse ($prs as $row)
                @php
                    $prId = $row->ID ?? $row->id;
                    $prNo = $row->PR_No ?? ($row->pr_no ?? ('PR #' . $prId));
                    $dept = $row->Department ?? ($row->department ?? '-');
                    $status = strtoupper((string) ($row->Status ?? ($row->status ?? 'OPEN')));
                    $prDate = $row->PR_Date ?? ($row->pr_date ?? ($row->created_at ?? ''));
                @endphp
                <a href="{{ route('procurement.mobile.pr.edit', ['id' => $prId]) }}"
                   class="pr-card pr-list-item"
                   data-pr-no="{{ $prNo }}"
                   data-status="{{ $status }}"
                   data-search="{{ strtolower($prNo . ' ' . $dept . ' ' . $status) }}"
                   onclick="return handlePrEditClick(event, this)">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-bold small text-success">{{ $prNo }}</div>
                            <div class="small font-weight-bold text-dark">{{ $dept }}</div>
                            <div class="text-muted" style="font-size: 11px;">
                                {{ $prDate ? \Carbon\Carbon::parse($prDate)->format('d M Y') : '' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge-status">{{ $status }}</span>
                            <div class="mt-2 text-muted small"><i class="fas fa-chevron-right"></i></div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="pr-card text-center text-muted small">Belum ada data Purchase Request.</div>
            @endforelse
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'dashboard'])

    <script>
        function filterPrList() {
            var q = (document.getElementById('searchPrInput').value || '').toLowerCase().trim();
            document.querySelectorAll('.pr-list-item').forEach(function(card) {
                var hay = card.getAttribute('data-search') || '';
                card.style.display = (q === '' || hay.indexOf(q) !== -1) ? '' : 'none';
            });
        }

        function handlePrEditClick(event, el) {
            var status = (el.getAttribute('data-status') || '').toUpperCase().trim();
            var prNo = el.getAttribute('data-pr-no') || '';

            if (status === 'APPROVED') {
                event.preventDefault();

                Swal.fire({
                    icon: 'info',
                    title: 'Status Anda Sudah Di-Approved',
                    html: 'Purchase Request <strong>' + prNo + '</strong> berstatus <strong>APPROVED</strong> sehingga tidak dapat diubah melalui halaman Edit PR.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#047857'
                });

                return false;
            }

            return true;
        }
    </script>
</body>
</html>