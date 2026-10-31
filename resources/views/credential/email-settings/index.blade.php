@extends('layout.mainlayout-credential')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Credentials<h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('credential-email-settings-index') }}">Email Settings</a></li>
        <li class="breadcrumb-item active"><span class="active">SMTP Configuration</span></li>
    </ol>
</div>

@if(session('success'))
<div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger mt-3">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="alert alert-info mt-3">
    Konfigurasi ini digunakan oleh pengiriman email sistem, termasuk pengingat Permit &amp; License.
    Template pesan pengingat Permit &amp; License diatur terpisah di
    <a href="{{ url('hrd-permit-license-mail-settings') }}">Permit &amp; License &gt; Template Email</a>.
</div>

<div class="card mt-3">
    <div class="card-body">
        <h5 class="mb-1">Konfigurasi SMTP &amp; Pengirim</h5>
        <p class="text-muted small mb-3">Password SMTP disimpan terenkripsi dan tidak ditampilkan kembali.</p>

        <form method="POST" action="{{ url('credential-email-settings-save') }}">
            @csrf

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="Is_Enabled" name="Is_Enabled" value="1" {{ old('Is_Enabled', $settings->Is_Enabled) ? 'checked' : '' }}>
                <label class="form-check-label" for="Is_Enabled">Aktifkan pengiriman email sistem</label>
                <small class="form-text text-muted">Aktifkan setelah SMTP, alamat pengirim, dan minimal satu penerima aktif terisi.</small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="Smtp_Host">SMTP Host</label>
                    <input type="text" class="form-control" id="Smtp_Host" name="Smtp_Host" value="{{ old('Smtp_Host', $settings->Smtp_Host) }}" placeholder="mail.domain.com">
                </div>
                <div class="form-group col-md-3">
                    <label for="Smtp_Port">SMTP Port</label>
                    <input type="number" class="form-control" id="Smtp_Port" name="Smtp_Port" value="{{ old('Smtp_Port', $settings->Smtp_Port ?: 587) }}" min="1" max="65535">
                </div>
                <div class="form-group col-md-3">
                    <label for="Smtp_Encryption">Encryption</label>
                    <select class="form-control" id="Smtp_Encryption" name="Smtp_Encryption">
                        <option value="tls" {{ old('Smtp_Encryption', $settings->Smtp_Encryption ?: 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('Smtp_Encryption', $settings->Smtp_Encryption ?: 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ old('Smtp_Encryption', $settings->Smtp_Encryption ?: 'tls') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="Smtp_Username">SMTP Username</label>
                    <input type="text" class="form-control" id="Smtp_Username" name="Smtp_Username" value="{{ old('Smtp_Username', $settings->Smtp_Username) }}" placeholder="notifikasi@domain.com" autocomplete="off">
                </div>
                <div class="form-group col-md-6">
                    <label for="Smtp_Password">SMTP Password</label>
                    <input type="password" class="form-control" id="Smtp_Password" name="Smtp_Password" value="" placeholder="{{ !empty($settings->Smtp_Password) ? 'Sudah tersimpan - kosongkan jika tidak diubah' : 'Password SMTP' }}" autocomplete="new-password">
                    @if(!empty($settings->Smtp_Password))
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="Clear_Smtp_Password" name="Clear_Smtp_Password" value="1">
                        <label class="form-check-label" for="Clear_Smtp_Password">Hapus password yang tersimpan</label>
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="Mail_From_Address">Alamat Pengirim</label>
                    <input type="email" class="form-control" id="Mail_From_Address" name="Mail_From_Address" value="{{ old('Mail_From_Address', $settings->Mail_From_Address) }}" placeholder="notifikasi@domain.com">
                </div>
                <div class="form-group col-md-6">
                    <label for="Mail_From_Name">Nama Pengirim</label>
                    <input type="text" class="form-control" id="Mail_From_Name" name="Mail_From_Name" value="{{ old('Mail_From_Name', $settings->Mail_From_Name) }}" placeholder="ERP-ALERTER">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
            <a href="{{ url('credential-permit-license-mail-settings') }}" class="btn btn-outline-secondary">Atur Template Pesan</a>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h5 class="mb-1">Penerima Email (Blast)</h5>
        <p class="text-muted small mb-3">Semua penerima aktif akan menerima email pengingat untuk setiap perizinan yang mendekati masa berlaku.</p>

        <form method="POST" action="{{ url('credential-email-settings-recipient-save') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-4">
                <label for="RecipientName">Nama</label>
                <input type="text" class="form-control" id="RecipientName" name="Name" value="{{ old('Name') }}" placeholder="Nama penerima">
            </div>
            <div class="form-group col-md-5">
                <label for="RecipientEmail">Email</label>
                <input type="email" class="form-control" id="RecipientEmail" name="Email" value="{{ old('Email') }}" placeholder="email@domain.com" list="UserEmailOptions" required>
                <datalist id="UserEmailOptions">
                    @foreach($userEmails as $user)
                    <option value="{{ $user->email }}">{{ $user->Name }} ({{ $user->email }})</option>
                    @endforeach
                </datalist>
            </div>
            <div class="form-group col-md-3">
                <button type="submit" class="btn btn-success btn-block">Tambah Penerima</button>
            </div>
        </form>

        <div class="table-responsive mt-2">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th style="width:190px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipients as $recipient)
                    <tr>
                        <td>{{ $recipient->Name ?: '-' }}</td>
                        <td>{{ $recipient->Email }}</td>
                        <td>
                            @if((int) $recipient->Is_Active === 1)
                            <span class="badge badge-success">Aktif</span>
                            @else
                            <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ url('credential-email-settings-recipient-toggle') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="ID" value="{{ (int) $recipient->ID }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">{{ (int) $recipient->Is_Active === 1 ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                            <form method="POST" action="{{ url('credential-email-settings-recipient-delete') }}" class="d-inline" onsubmit="return confirm('Hapus penerima email ini?');">
                                @csrf
                                <input type="hidden" name="ID" value="{{ (int) $recipient->ID }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada penerima email. Tambahkan minimal satu penerima aktif.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h5 class="mb-1">Test Email</h5>
        <p class="text-muted small mb-3">Kirim email uji menggunakan konfigurasi SMTP di atas.</p>
        <form method="POST" action="{{ url('credential-email-settings-test') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-8">
                <label for="TestEmail">Email Tujuan</label>
                <input type="email" class="form-control" id="TestEmail" name="Test_Email" value="{{ old('Test_Email', auth()->user()->email) }}" required>
            </div>
            <div class="form-group col-md-4">
                <button type="submit" class="btn btn-info btn-block">Kirim Test Email</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h5 class="mb-1">Log Email Pengingat Permit &amp; License</h5>
        <p class="text-muted small mb-3">10 percobaan pengiriman terakhir. Status FAILED akan dicoba ulang pada sinkronisasi berikutnya (maksimal 3 kali).</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Perizinan</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Attempt</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td><small>{{ $log->Created_At }}</small></td>
                        <td>{{ $log->Permit_Name ?: '-' }}</td>
                        <td>{{ $log->Email_To }}</td>
                        <td>
                            @if($log->Status === 'SENT')
                            <span class="badge badge-success">SENT</span>
                            @elseif($log->Status === 'FAILED')
                            <span class="badge badge-danger">FAILED</span>
                            @else
                            <span class="badge badge-warning">QUEUED</span>
                            @endif
                        </td>
                        <td>{{ (int) $log->Attempts }}</td>
                        <td><small title="{{ $log->Error }}">{{ $log->Error ? \Illuminate\Support\Str::limit($log->Error, 120) : '' }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada log pengiriman email.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
