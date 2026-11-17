<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ url('hrd-tna-index') }}" class="btn btn-sm {{ ($activeTab ?? '') === 'tna' ? 'btn-primary' : 'btn-outline-primary' }}">
                1. TNA (Need Analysis)
            </a>
            <a href="{{ url('hrd-training-program-index') }}" class="btn btn-sm {{ ($activeTab ?? '') === 'program' ? 'btn-primary' : 'btn-outline-primary' }}">
                2. Module &amp; Program
            </a>
            <a href="{{ url('hrd-training-index') }}" class="btn btn-sm {{ ($activeTab ?? '') === 'schedule' ? 'btn-primary' : 'btn-outline-primary' }}">
                3. Jadwal &amp; Kurikulum
            </a>
            <a href="{{ url('hrd-training-evaluation-index') }}" class="btn btn-sm {{ ($activeTab ?? '') === 'evaluation' ? 'btn-primary' : 'btn-outline-primary' }}">
                4. Evaluasi Training
            </a>
            <a href="{{ url('hrd-training-summary-index') }}" class="btn btn-sm {{ ($activeTab ?? '') === 'summary' ? 'btn-primary' : 'btn-outline-primary' }}">
                5. Summary &amp; Sertifikat
            </a>
            <a href="{{ url('hrd-training-coaching-index') }}" class="btn btn-sm {{ ($activeTab ?? '') === 'coaching' ? 'btn-primary' : 'btn-outline-primary' }}">
                6. Coaching &amp; Refreshment
            </a>
        </div>
    </div>
</div>
