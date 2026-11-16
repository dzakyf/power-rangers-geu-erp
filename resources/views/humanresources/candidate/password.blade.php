@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">Ubah Password</h4>
            <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Keamanan Akun</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fa fa-key me-2"></i>Perbarui Password Akun</h6>
            </div>
            <div class="card-body">
                <div id="pwdAlert" class="alert d-none" role="alert"></div>
                <form id="passwordForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Saat Ini</label>
                        <input type="password" id="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru (min. 6 karakter)</label>
                        <input type="password" id="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" id="new_password_confirmation" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" id="btnSavePwd" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#btnSavePwd');
        btn.prop('disabled', true);

        $.post('{{ url("/candidate/password-update") }}', {
            _token: '{{ csrf_token() }}',
            current_password: $('#current_password').val(),
            new_password: $('#new_password').val(),
            new_password_confirmation: $('#new_password_confirmation').val()
        }, function(res) {
            $('#pwdAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
            $('#passwordForm')[0].reset();
            btn.prop('disabled', false);
        }, 'json').fail(function(xhr) {
            var msg = 'Gagal memperbarui password.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
            }
            $('#pwdAlert').removeClass('d-none alert-success').addClass('alert-danger').text(msg);
            btn.prop('disabled', false);
        });
    });
});
</script>
@endsection
