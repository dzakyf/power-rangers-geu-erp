<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $item ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' }} - Mobile</title>
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 40px;
            color: #1e293b;
        }

        .header-form {
            position: sticky;
            top: 0;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 16px;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-form {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            margin-bottom: 16px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-size: 0.9rem;
            padding: 9px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <div class="header-form shadow-sm">
        <a href="{{ $item ? route('announcement.mobile.detail', $item->id) : route('announcement.mobile.index') }}"
            class="btn btn-sm btn-light rounded-circle px-2 py-1">
            <i class="fa fa-times"></i>
        </a>
        <h6 class="fw-bold mb-0">{{ $item ? 'Edit Pengumuman' : 'Buat Berita / Pengumuman' }}</h6>
        <div style="width: 28px;"></div>
    </div>

    <div class="container px-3 py-3">
        <form id="mobileAnnouncementForm" enctype="multipart/form-data">
            @csrf
            @if ($item)
                <input type="hidden" name="ID" value="{{ $item->id }}">
            @endif

            <div class="card-form">
                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
                    <input type="text" name="Title" class="form-control" placeholder="Tuliskan judul berita..."
                        value="{{ $item->title ?? '' }}" required>
                </div>

                <!-- Category -->
                <div class="mb-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="Category" class="form-select" required>
                        <option value="ANNOUNCEMENT"
                            {{ isset($item) && $item->category === 'ANNOUNCEMENT' ? 'selected' : '' }}>Announcement
                        </option>
                        <option value="NEWS" {{ isset($item) && $item->category === 'NEWS' ? 'selected' : '' }}>News
                        </option>
                        <option value="POLICY" {{ isset($item) && $item->category === 'POLICY' ? 'selected' : '' }}>
                            Policy & Guideline</option>
                        <option value="EVENT" {{ isset($item) && $item->category === 'EVENT' ? 'selected' : '' }}>
                            Event / Gathering</option>
                    </select>
                </div>

                <!-- Summary -->
                <div class="mb-3">
                    <label class="form-label">Ringkasan Singkat (Summary)</label>
                    <textarea name="Summary" class="form-control" rows="2"
                        placeholder="Ringkasan ringkas untuk card berita mobile...">{{ $item->summary ?? '' }}</textarea>
                </div>

                <!-- Content -->
                <div class="mb-3">
                    <label class="form-label">Isi Konten Lengkap <span class="text-danger">*</span></label>
                    <textarea name="Content" class="form-control" rows="7" placeholder="Tulis rincian informasi..." required>{{ $item->content ?? '' }}</textarea>
                </div>
            </div>

            <!-- Media & Upload Card -->
            <div class="card-form">
                <h6 class="fw-bold text-dark mb-3"><i class="fa fa-paperclip text-primary me-2"></i>Media & Lampiran
                </h6>

                <!-- Thumbnail -->
                <div class="mb-3">
                    <label class="form-label">Gambar Thumbnail / Banner</label>
                    <input type="file" name="Thumbnail" class="form-control" accept="image/*">
                    @if (isset($item) && $item->thumbnail)
                            <div
                                class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset($item->thumbnail) }}" class="rounded border"
                                        style="width: 44px; height: 44px; object-fit: cover;">
                                    <span class="small text-muted">Thumbnail saat ini</span>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="checkbox" name="Remove_Thumbnail"
                                        value="1" id="rmvThumb">
                                    <label class="form-check-label text-danger small" for="rmvThumb">Hapus</label>
                                </div>
                            </div>
                    @endif
                    <div class="small text-muted mt-1">Maks 5MB (JPG, PNG).</div>
                </div>

                <!-- Attachment -->
                <div class="mb-3">
                    <label class="form-label">File Attachment / Dokumen</label>
                    <input type="file" name="Attachment" class="form-control"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                    @if (isset($item) && $item->attachment_file)
                        <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                            <span
                                class="small fw-semibold text-truncate">{{ $item->attachment_name ?: basename($item->attachment_file) }}</span>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" name="Remove_Attachment" value="1"
                                    id="rmvAttach">
                                <label class="form-check-label text-danger small" for="rmvAttach">Hapus</label>
                            </div>
                        </div>
                    @endif
                    <div class="small text-muted mt-1">Maks 20MB (PDF, Word, Excel, ZIP).</div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="card-form">
                <h6 class="fw-bold text-dark mb-3"><i class="fa fa-cog text-primary me-2"></i>Pengaturan Publikasi
                </h6>

                <div class="mb-3">
                    <label class="form-label">Target Penerima</label>
                    <select name="Target_Audience" class="form-select">
                        <option value="ALL"
                            {{ isset($item) && $item->target_audience === 'ALL' ? 'selected' : '' }}>Semua Karyawan
                            (ALL)</option>
                        <option value="INTERNAL"
                            {{ isset($item) && $item->target_audience === 'INTERNAL' ? 'selected' : '' }}>Internal
                            Perusahaan</option>
                        <option value="HCGA"
                            {{ isset($item) && $item->target_audience === 'HCGA' ? 'selected' : '' }}>HCGA Departemen
                            Saja</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="Is_Pinned" value="1"
                            id="switchPin" {{ isset($item) && $item->is_pinned ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark small" for="switchPin">Sematkan sebagai
                            Berita Utama (Pinned)</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="Status" class="form-select">
                        <option value="Final" {{ !isset($item) || $item->is_final ? 'selected' : '' }}>Published
                            (Langsung Tayang)</option>
                        <option value="Draft" {{ isset($item) && !$item->is_final ? 'selected' : '' }}>Draft
                            (Simpan Sementara)</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow" id="btnSubmit">
                    <i class="fa fa-paper-plane me-1"></i> Simpan & Publikasikan
                </button>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.getElementById('mobileAnnouncementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            var formData = new FormData(this);

            fetch("{{ route('announcement.mobile.submit') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(res) {
                    if (res.success) {
                        alert(res.message);
                        window.location.href = res.redirect || "{{ route('announcement.mobile.index') }}";
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Simpan & Publikasikan';
                        alert(res.message || 'Terjadi kesalahan validasi.');
                    }
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Simpan & Publikasikan';
                    alert('Terjadi kesalahan saat memproses data.');
                });
        });
    </script>
</body>

</html>
