<!-- Modal Setting Master Default Mapping COA -->
<div class="modal fade" id="FinCoaMappingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title text-white">Master Default Mapping COA (<code>branch_trial_balance</code> &rarr; <code>branch_coa</code>)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3 border rounded p-2 bg-light">
                    <div class="col-md-4">
                        <label class="form-label small">Sumber Modul</label>
                        <select id="cm_source_module" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Komponen</label>
                        <select id="cm_component_key" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Kelompok Trial Balance</label>
                        <select id="cm_tb_group" class="form-select form-select-sm" onchange="loadMappingCoaOptions();"></select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small">Akun COA (<code>branch_coa</code>)</label>
                        <select id="cm_acc_no" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="saveCoaMapping();">Simpan Mapping Default</button>
                    </div>
                </div>

                <table class="table table-sm table-bordered table-hover">
                    <thead class="table-light">
                        <tr><th>Modul</th><th>Komponen</th><th>Trial Balance</th><th>COA</th><th>Sisi</th><th style="width:55px;"></th></tr>
                    </thead>
                    <tbody id="cmTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
