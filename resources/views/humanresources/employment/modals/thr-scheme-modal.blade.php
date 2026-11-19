<form id="formEmployeeThrScheme">
    @csrf
    @foreach($employees as $emp)
        <input type="hidden" name="employee_ids[]" value="{{ $emp->Task_ID ?: $emp->ID }}">
    @endforeach

    <div class="mb-3">
        <label class="form-label fw-semibold">Karyawan Terpilih ({{ count($employees) }})</label>
        <div class="border rounded p-2 bg-light" style="max-height: 140px; overflow-y: auto;">
            @forelse($employees as $emp)
                <div class="small d-flex justify-content-between border-bottom py-1">
                    <span><strong>{{ $emp->Name }}</strong> ({{ $emp->NIK ?: $emp->Task_ID }}) — {{ $emp->Employment_Status ?: '-' }}</span>
                    <span class="badge bg-secondary text-white">{{ \App\Services\HCGA\EmployeeThrSchemeService::label($emp->thr_scheme) }}</span>
                </div>
            @empty
                <span class="text-danger small">Tidak ada karyawan terpilih.</span>
            @endforelse
        </div>
    </div>

    @php $firstEmp = $employees->first(); @endphp
    <div class="mb-3">
        <label class="form-label fw-semibold">Skema THR (Sesuai PKWT / MoU) *</label>
        <select name="thr_scheme" class="form-select" required>
            <option value="">Default (Standar Permenaker)</option>
            @foreach($schemes as $key => $label)
                <option value="{{ $key }}" {{ ($firstEmp && $firstEmp->thr_scheme === $key) ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text small">
            <strong>Upah</strong> = THP Bruto (Gapok + COLA + semua Tunjangan). <strong>Gapok</strong> = Gaji Pokok saja.
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Catatan / Nomor Kontrak PKWT / MoU</label>
        <input type="text" name="thr_contract_note" class="form-control" maxlength="255"
               value="{{ $firstEmp->thr_contract_note ?? '' }}"
               placeholder="Misal: PKWT No. 012/HRD/2026 - Disepakati Prorate Gapok">
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" {{ count($employees) === 0 ? 'disabled' : '' }}>
            Simpan Skema THR
        </button>
    </div>
</form>

<script>
    $('#formEmployeeThrScheme').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-employee-thr-scheme-save") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message || 'Skema THR berhasil disimpan.');
                if (window.AppModal && typeof window.AppModal.close === 'function') {
                    window.AppModal.close();
                } else {
                    $('.modal').modal('hide');
                }
                if (typeof buildEmployeeGrid === 'function' && typeof EmpActiveCols !== 'undefined') {
                    buildEmployeeGrid(EmpActiveCols);
                }
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan skema THR.');
            }
        });
    });
</script>
