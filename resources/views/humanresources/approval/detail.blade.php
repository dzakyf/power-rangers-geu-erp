@if(!$task)
<div class="alert alert-warning">Task tidak ditemukan.</div>
@else
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Task #{{ $task->ID }} - {{ $task->Request_No }}</strong>
        <span class="badge bg-info text-white badge-info">{{ $task->Task_Status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr><th style="width:180px">Request Type</th><td>{{ $task->Request_Type }}</td></tr>
                    <tr><th>Requester</th><td>{{ $task->Requester_Name }}</td></tr>
                    <tr><th>Department</th><td>{{ $task->Department }}</td></tr>
                    <tr><th>Location / Company</th><td>{{ $task->Location }} / {{ $task->Company_ID }}</td></tr>
                    <tr><th>Amount</th><td>{{ number_format((float) $task->Amount, 2, ',', '.') }}</td></tr>
                    <tr><th>Revision</th><td>{{ $task->Revision_No }}</td></tr>
                    <tr><th>Submitted</th><td>{{ $task->Submitted_At }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <tr><th style="width:180px">Position</th><td>{{ $task->Position_Code }} - {{ $task->Position_Name }}</td></tr>
                    <tr><th>Stage / Level</th><td>{{ $task->Stage_Order }} / {{ $task->Level_Number }}</td></tr>
                    <tr><th>Approval Mode</th><td>{{ $task->Approval_Mode }}{{ $task->Quorum ? ' (quorum ' . $task->Quorum . ')' : '' }}</td></tr>
                    <tr><th>Status</th><td>{{ $task->Task_Status }}</td></tr>
                    <tr><th>Claimed By</th><td>{{ $task->Claimed_By ?: '-' }}</td></tr>
                    <tr><th>Claimed At</th><td>{{ $task->Claimed_At ?: '-' }}</td></tr>
                    <tr><th>Held Reason</th><td>{{ $task->Held_Reason ?: '-' }}</td></tr>
                </table>
            </div>
        </div>

        <h5 class="mt-3">Description</h5>
        <p class="border rounded p-2 bg-light">{{ $task->Description ?: '-' }}</p>

        <h5 class="mt-3">Candidates</h5>
        <table class="table table-sm table-bordered">
            <thead>
                <tr><th>Name</th><th>Username</th><th>Type</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($candidates as $candidate)
                <tr>
                    <td>{{ $candidate->User_Name }}</td>
                    <td>{{ $candidate->username }}</td>
                    <td>{{ $candidate->Candidate_Type }}</td>
                    <td>{{ $candidate->Status }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Tidak ada kandidat.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h5 class="mt-3">Approval History</h5>
        <table class="table table-sm table-bordered">
            <thead>
                <tr><th>Waktu</th><th>Oleh</th><th>Action</th><th>Stage</th><th>Catatan</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->Action_Date }}</td>
                    <td>{{ $log->Approver_Name ?: ($log->Is_System ? 'SYSTEM' : $log->Approver_Id) }}</td>
                    <td>{{ $log->Action }}</td>
                    <td>{{ $log->Stage_Order ?: '-' }}</td>
                    <td>{{ $log->Notes ?: '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($isAdmin && in_array(strtoupper((string) $task->Request_Status), ['IN_PROGRESS', 'BLOCKED'], true))
        <hr>
        <h5>Grant Self-Approval Exception</h5>
        <form method="POST" action="{{ url('hrd-approval-self-exception') }}">
            @csrf
            <input type="hidden" name="Request_ID" value="{{ $task->Request_ID }}" />
            <input type="hidden" name="User_Id" value="{{ $task->Requester_Id }}" />
            <div class="form-group">
                <label>Alasan exception (wajib)</label>
                <textarea name="Reason" class="form-control" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>Berlaku sampai (opsional)</label>
                <input type="datetime-local" name="Expires_At" class="form-control" />
            </div>
            <button type="submit" class="btn btn-warning">Grant Exception</button>
        </form>
        @endif
    </div>
</div>
@endif