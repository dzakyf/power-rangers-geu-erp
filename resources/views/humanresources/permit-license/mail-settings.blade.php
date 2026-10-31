@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">GA</a></li>
        <li class="breadcrumb-item"><a href="{{ url('hrd-permit-license-index') }}">Permit &amp; License</a></li>
        <li class="breadcrumb-item active"><span class="active">Template Email</span></li>
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
    Halaman ini hanya mengatur <strong>template pesan</strong> email pengingat Permit &amp; License.
    Konfigurasi SMTP, alamat pengirim, daftar penerima email, test email, dan log email dikelola terpisah di menu
    <a href="{{ url('credential-email-settings-index') }}">Email Settings &gt; SMTP Configuration</a>.
</div>

@if(!$mailEnabled)
<div class="alert alert-warning mt-3">
    Pengiriman email belum aktif. Aktifkan SMTP dan tambahkan minimal satu penerima aktif di
    <a href="{{ url('credential-email-settings-index') }}">Email Settings</a> sebelum menjalankan sinkronisasi pengingat.
</div>
@else
<div class="alert alert-success mt-3">
    Pengiriman email aktif untuk {{ $mailRecipientCount }} penerima.
</div>
@endif

<div class="card mt-3">
    <div class="card-body">
        <h5 class="mb-1">Template Email Pengingat</h5>
        <p class="text-muted small mb-3">Atur subject, intro, dan footer pesan. Perubahan di sini tidak mengubah konfigurasi SMTP.</p>

        <form method="POST" action="{{ url('hrd-permit-license-mail-settings-save') }}">
            @csrf

            <div class="form-group">
                <label for="Subject_Template">Subject Email</label>
                <input type="text" class="form-control" id="Subject_Template" name="Subject_Template" value="{{ old('Subject_Template', $settings->Subject_Template) }}" required>
                <small class="form-text text-muted">Placeholder: <code>{permit_name}</code>, <code>{permit_number}</code>, <code>{issuer}</code>, <code>{valid_to}</code>, <code>{days_remaining}</code>, <code>{category}</code>.</small>
            </div>

            <div class="form-group">
                <label for="Intro_Text">Intro Email</label>
                <textarea class="form-control" id="Intro_Text" name="Intro_Text" rows="4">{{ old('Intro_Text', $settings->Intro_Text) }}</textarea>
                <small class="form-text text-muted">Teks pembuka di atas detail perizinan. Placeholder yang sama dapat digunakan.</small>
            </div>

            <div class="form-group">
                <label for="Footer_Text">Footer Email</label>
                <textarea class="form-control" id="Footer_Text" name="Footer_Text" rows="3">{{ old('Footer_Text', $settings->Footer_Text) }}</textarea>
                <small class="form-text text-muted">Teks penutup di bawah detail perizinan.</small>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Template</button>
            <a href="{{ url('hrd-permit-license-mail-preview') }}" target="_blank" class="btn btn-outline-secondary">Preview Template</a>
            <a href="{{ url('credential-email-settings-index') }}" class="btn btn-outline-info">Konfigurasi SMTP</a>
            <a href="{{ url('hrd-permit-license-index') }}" class="btn btn-link">Kembali</a>
        </form>
    </div>
</div>
@endsection
