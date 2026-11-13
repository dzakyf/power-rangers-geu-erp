<div class="form-group h-100">
    <label class="font-weight-bold">QC Movement</label>
    <p class="text-muted small mb-2">Referensi hasil QC dari data movement (read-only).</p>

    @if($prefix === 'create')
        <input type="hidden" name="Movement_FFA" id="{{ $prefix }}_Movement_FFA" />
        <input type="hidden" name="Movement_MC" id="{{ $prefix }}_Movement_MC" />
        <input type="hidden" name="Movement_IMP" id="{{ $prefix }}_Movement_IMP" />
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-sm text-center align-middle" style="background-color:#fff;">
            <thead class="thead-light">
                <tr>
                    <th style="width:55%;text-align:left;vertical-align:middle;">Parameter</th>
                    <th style="width:45%;vertical-align:middle;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left font-weight-bold" style="vertical-align:middle;">
                        FFA <span class="badge badge-light text-secondary border ml-1">%</span>
                    </td>
                    <td>
                        <input type="text" id="{{ $prefix }}_mov_ffa" class="form-control form-control-sm text-right bg-light" readonly placeholder="-" />
                    </td>
                </tr>
                <tr>
                    <td class="text-left font-weight-bold" style="vertical-align:middle;">
                        Moisture <span class="badge badge-light text-secondary border ml-1">%</span>
                    </td>
                    <td>
                        <input type="text" id="{{ $prefix }}_mov_mc" class="form-control form-control-sm text-right bg-light" readonly placeholder="-" />
                    </td>
                </tr>
                <tr>
                    <td class="text-left font-weight-bold" style="vertical-align:middle;">
                        Impurities <span class="badge badge-light text-secondary border ml-1">%</span>
                    </td>
                    <td>
                        <input type="text" id="{{ $prefix }}_mov_imp" class="form-control form-control-sm text-right bg-light" readonly placeholder="-" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
