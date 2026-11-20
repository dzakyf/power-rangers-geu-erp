@php
    $activeStep = (int) ($activeStep ?? 1);
    $steps = [
        1 => [
            'label' => '1. Periode & Dept PIC',
            'url'   => url('hrd-performance-period-index'),
            'role'  => 'HCGA / Admin',
            'desc'  => 'Aktifkan periode penilaian tahunan dan tetapkan PIC Departemen. Hanya PIC Departemen & HCGA yang berhak menyusun target serta menginput realisasi KPI.',
            'next'  => 'Lanjut ke Langkah 2 (Master Soft Skill) untuk memastikan versi pertanyaan & skala penilaian aktif.',
        ],
        2 => [
            'label' => '2. Master Soft Skill',
            'url'   => url('hrd-performance-competency-index'),
            'role'  => 'HCGA / Admin',
            'desc'  => 'Kelola daftar poin Soft Skill & skala nilai (mis. Likert 1-5 atau 1-10) dalam sistem versi (Draft → Publish). Versi lama otomatis terarsip untuk menjaga histori.',
            'next'  => 'Lanjut ke Langkah 3 (KPI & OKR Goals) untuk menyusun target KPI Posisi, Add-on, maupun Probation.',
        ],
        3 => [
            'label' => '3. KPI & OKR Goals',
            'url'   => url('hrd-performance-kpi-index'),
            'role'  => 'PIC Dept / HCGA (Karyawan: Read-Only)',
            'desc'  => 'Susun KPI Template Posisi, KPI Tambahan (Add-on di luar posisi), atau KPI Probation (3/6/12 bln), lalu ajukan Approval 2 Tahap (Atasan Langsung + Manager HCGA).',
            'next'  => 'Setelah Approved: ke Langkah 4 (Evaluasi Probation) untuk karyawan probation, atau Langkah 5 (Performance Review) untuk evaluasi reguler/semester.',
        ],
        4 => [
            'label' => '4. Evaluasi Probation',
            'url'   => url('hrd-performance-probation-index'),
            'role'  => 'HCGA, PIC Dept, Atasan Langsung',
            'desc'  => 'Pantau karyawan masa percobaan (Date_in & Probation End Date). Sistem otomatis membuat lembar review di H-14 & mengirim pengingat ke Atasan Langsung (H-14, H-7) serta eskalasi HCGA (H-3).',
            'next'  => 'PIC mengisi realisasi target → Atasan menilai Soft Skill & kesimpulan → Cetak PDF Form FR/HCGA/14-00.',
        ],
        5 => [
            'label' => '5. Performance Review',
            'url'   => url('hrd-performance-review-index'),
            'role'  => 'HCGA (Siklus), PIC Dept (Realisasi), Atasan (Penilai)',
            'desc'  => 'Kelola siklus evaluasi reguler (Semester 1/2, Mid-Year, Annual). Gunakan tombol "Ubah Waktu Siklus" jika durasi penilaian perlu diubah (mis. 6 bulan → 3 bulan).',
            'next'  => 'Jika nilai akhir setelah finalisasi di bawah ambang batas (< 60 / Grade D), sistem otomatis menerbitkan PIP di Langkah 6.',
        ],
        6 => [
            'label' => '6. PIP Plan',
            'url'   => url('hrd-performance-pip-index'),
            'role'  => 'HCGA & Mentor / Atasan',
            'desc'  => 'Tindak lanjut pembinaan kinerja (30/60/90 hari) bagi karyawan dengan nilai akhir di bawah standar. Catat action plan, check-in mingguan, dan evaluasi hasil akhir.',
            'next'  => 'Evaluasi hasil PIP (Lulus / Perpanjang / Tindak Lanjut HR).',
        ],
    ];
    $currentGuide = $steps[$activeStep] ?? $steps[1];
@endphp

<div class="card border-primary mb-3 shadow-sm">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-1">
                @foreach($steps as $num => $st)
                    <a href="{{ $st['url'] }}"
                       class="badge text-decoration-none {{ $num === $activeStep ? 'bg-primary text-white p-2' : ($num < $activeStep ? 'bg-success text-white p-2' : 'bg-light text-dark border p-2') }}">
                        {{ $st['label'] }}
                    </a>
                    @if($num < count($steps))
                        <span class="text-muted small">&rarr;</span>
                    @endif
                @endforeach
            </div>
            <button class="btn btn-xs btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePerfGuide" aria-expanded="false">
                <i class="feather-help-circle me-1"></i> Panduan Menu Ini
            </button>
        </div>

        <div class="collapse mt-2" id="collapsePerfGuide">
            <div class="p-2 bg-light rounded border small">
                <div class="mb-1">
                    <strong>Peran Terkait:</strong> <span class="badge bg-info text-dark">{{ $currentGuide['role'] }}</span>
                </div>
                <div class="mb-1"><strong>Fungsi Menu:</strong> {{ $currentGuide['desc'] }}</div>
                <div class="text-muted"><strong>Langkah Selanjutnya:</strong> {{ $currentGuide['next'] }}</div>
            </div>
        </div>
    </div>
</div>
