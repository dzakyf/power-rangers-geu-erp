@php $isEdit = $assignment !== null; @endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header">
        <strong>{{ $isEdit ? 'Edit Assignment #' . $assignment->ID : 'New Position Assignment' }}</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ url('hrd-approval-position-save') }}">
            @csrf
            <input type="hidden" name="ID" value="{{ $isEdit ? $assignment->ID : 0 }}" />

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Position *</label>
                    <select name="Position_ID" class="form-control" required onchange="ApprovalPositionOrgInfo(this);">
                        <option value="">-- Pilih Position --</option>
                        @foreach($positions as $position)
                        <option value="{{ $position->ID }}"
                                data-org-id="{{ $position->Org_ID ?? '' }}"
                                data-org-code="{{ $position->Org_Code ?? '' }}"
                                data-org-level="{{ $position->Org_Level ?? '' }}"
                                data-reports-to="{{ $position->Reports_To_Position ?? '' }}"
                                {{ (string) old('Position_ID', $assignment->Position_ID ?? '') === (string) $position->ID ? 'selected' : '' }}>
                            {{ $position->Initial }} - {{ $position->Position }} (Level {{ $position->Level }})
                            @if(!empty($position->Org_Code)) [{{ $position->Org_Code }}] @else [belum terhubung ke bagan] @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" required />
                    <small class="text-muted">Gunakan username login, bukan nama.</small>
                </div>
                <div class="form-group col-md-3">
                    <label>Assignment Type</label>
                    <select name="Assignment_Type" class="form-control">
                        @foreach(['PRIMARY','ACTING','SECONDARY'] as $type)
                        <option value="{{ $type }}" {{ old('Assignment_Type', $assignment->Assignment_Type ?? 'PRIMARY') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-2">
                    <label>Status</label>
                    <select name="Status" class="form-control">
                        @foreach(['ACTIVE','INACTIVE'] as $status)
                        <option value="{{ $status }}" {{ old('Status', $assignment->Status ?? 'ACTIVE') === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Start Date</label>
                    <input type="date" name="Start_Date" class="form-control" value="{{ old('Start_Date', $assignment->Start_Date ?? '') }}" />
                </div>
                <div class="form-group col-md-3">
                    <label>End Date</label>
                    <input type="date" name="End_Date" class="form-control" value="{{ old('End_Date', $assignment->End_Date ?? '') }}" />
                </div>
                <div class="form-group col-md-4">
                    <label>Scope</label>
                    <input type="text" class="form-control" value="{{ $isEdit ? ($assignment->Location . ' / ' . $assignment->Company_ID) : 'Mengikuti Position' }}" disabled />
                    <small class="text-muted">Location dan Company_ID selalu mengikuti posisi.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Assignment' : 'Create Assignment' }}</button>
            <a href="hrd-approval-position" class="btn btn-secondary">Back to List</a>
        </form>

        <hr>
        <h6>Bagan Organisasi</h6>
        <table class="table table-sm table-bordered mb-0">
            <tr><th style="width:180px">Org Code</th><td id="ApprovalPositionOrgCode">{{ $selectedOrg->Org_Code ?? '-' }}</td></tr>
            <tr><th>Org Level</th><td id="ApprovalPositionOrgLevel">{{ $selectedOrg->Org_Level ?? '-' }}</td></tr>
            <tr><th>Reports To</th><td id="ApprovalPositionReportsTo">{{ $selectedOrg->Reports_To_Position ?? '-' }}</td></tr>
            <tr><th>Org Node ID</th><td id="ApprovalPositionOrgId">{{ $selectedOrg->Org_ID ?? '-' }}</td></tr>
            <tr><th>Bagan</th><td><a href="hrd-organization-chart" target="_blank">Buka bagan struktur organisasi</a></td></tr>
        </table>
        <small class="text-muted">Data bagan mengikuti organization_structure. Gunakan tombol Sync Bagan bila posisi baru belum terhubung.</small>

        @if($isEdit)
        <hr>
        <table class="table table-sm table-bordered mb-0">
            <tr><th style="width:180px">User</th><td>{{ $user->Name ?? '-' }} ({{ $user->username ?? '-' }})</td></tr>
            <tr><th>Department</th><td>{{ $user->Department ?? '-' }}</td></tr>
            <tr><th>Created</th><td>{{ $assignment->Created_At }} by {{ $assignment->Created_By ?: '-' }}</td></tr>
            <tr><th>Updated</th><td>{{ $assignment->Updated_At }} by {{ $assignment->Updated_By ?: '-' }}</td></tr>
        </table>
        @endif
    </div>
</div>