@extends('layout.mainlayout-cns')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
<style>
    .table-cns {
        font-size: 12px !important;
        margin-bottom: 0 !important;
    }
    .table-cns thead th {
        background-color: #174375 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        padding: 6px 8px !important;
        border: 1px solid #ffffff !important;
        white-space: nowrap !important;
    }
    .table-cns tbody td {
        font-size: 12px !important;
        vertical-align: middle !important;
        padding: 5px 8px !important;
    }
    .user-row {
        cursor: pointer;
    }
    .user-row.selected {
        background-color: #d9edf7 !important;
    }
    .badge-active {
        background-color: #28a745;
        color: #ffffff;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-inactive {
        background-color: #dc3545;
        color: #ffffff;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
    }
    .sig-thumb {
        max-height: 32px;
        max-width: 80px;
        border: 1px solid #ced4da;
        border-radius: 3px;
        background: #fff;
        padding: 2px;
    }
    .dataTables_wrapper {
        font-size: 12px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 2px 8px !important;
        font-size: 12px !important;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        font-size: 12px !important;
    }
    .dataTables_wrapper .dataTables_filter input {
        height: 28px !important;
        font-size: 12px !important;
        padding: 2px 6px !important;
    }
    .dataTables_wrapper .dataTables_length select {
        height: 28px !important;
        font-size: 12px !important;
        padding: 2px 6px !important;
    }
</style>
@endpush

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Akses & Pengguna</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Akses & Pengguna</a></li>
        <li class="breadcrumb-item active"><span class="active">Manajemen Pengguna</span></li>
    </ol>
</div>
<!-- End Main Content Header -->

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MANAJEMEN PENGGUNA MODUL CNS</div>

    <div class="card w-100">
        <div class="card-body">
            <div class="table-responsive">
                <table id="users-table" class="table table-striped table-bordered text-nowrap table-cns" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No.</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Lokasi</th>
                            <th>Role / Posisi</th>
                            <th>Status</th>
                            <th>Tanda Tangan</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Action buttons located below table (matching CNS module standards) -->
            <div class="mt-2 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary mr-1" id="newUserBtn" onclick="toggleCreateCard()">
                    <i class="fa fa-plus mr-1"></i> New Pengguna
                </button>
                <button type="button" class="btn btn-warning mr-1" id="editUserBtn" onclick="openEditModal()" disabled>
                    <i class="fa fa-pencil mr-1"></i> Edit Pengguna
                </button>
                <button type="button" class="btn btn-danger" id="deleteUserBtn" onclick="deleteUser()" disabled>
                    <i class="fa fa-trash mr-1"></i> Nonaktifkan Pengguna
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Create Form Card --}}
<div class="row m-1 d-none" id="createCardRow">
    <div class="card w-100 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 text-white font-weight-bold"><i class="fa fa-user-plus mr-2"></i> Tambah Pengguna Baru (Modul CNS)</h6>
            <button type="button" class="close text-white" onclick="toggleCreateCard()">&times;</button>
        </div>
        <div class="card-body">
            <form id="createUserForm" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label for="createEmail" class="font-weight-bold"><small>Email / Username <span class="text-danger">*</span></small></label>
                        <input type="email" id="createEmail" name="email" class="form-control form-control-sm" placeholder="user@contoh.com" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="createName" class="font-weight-bold"><small>Nama Lengkap <span class="text-danger">*</span></small></label>
                        <input type="text" id="createName" name="Name" class="form-control form-control-sm" placeholder="Nama pengguna" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="createPassword" class="font-weight-bold"><small>Password</small></label>
                        <input type="text" id="createPassword" name="password" class="form-control form-control-sm" value="12345678">
                        <small class="text-muted">Default: <code>12345678</code></small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label for="createPosition" class="font-weight-bold"><small>Role / Posisi</small></label>
                        <select id="createPosition" name="Position" class="form-control form-control-sm">
                            @foreach($roles as $roleName)
                            <option value="{{ $roleName }}">{{ $roleName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="createLocation" class="font-weight-bold"><small>Lokasi</small></label>
                        <select id="createLocation" name="Location" class="form-control form-control-sm">
                            @foreach($locations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="createSignature" class="font-weight-bold"><small>Tanda Tangan (Opsional)</small></label>
                        <input type="file" id="createSignature" name="signature" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-sm btn-primary mr-1" id="btnSubmitCreate">
                        <i class="fa fa-save mr-1"></i> Simpan Pengguna
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleCreateCard()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark py-2">
                <h6 class="modal-title font-weight-bold"><i class="fa fa-pencil mr-1"></i> Edit Pengguna CNS</h6>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#editUserModal').modal('hide');">&times;</button>
            </div>
            <div class="modal-body" style="font-size: 13px;">
                <form id="editUserForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="editUserId" name="user_id">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold"><small>Username</small></label>
                            <input type="text" id="editUsername" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold"><small>Nama Lengkap <span class="text-danger">*</span></small></label>
                            <input type="text" id="editName" name="name" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold"><small>Email</small></label>
                            <input type="email" id="editEmail" name="email" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold"><small>Lokasi</small></label>
                            <select id="editLocation" name="location" class="form-control form-control-sm">
                                @foreach($locations as $loc)
                                <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold"><small>Status</small></label>
                            <select id="editStatus" name="status" class="form-control form-control-sm">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold"><small>Role Utama</small></label>
                            <select id="editPrimaryRole" name="primary_role" class="form-control form-control-sm">
                                @foreach($roles as $roleName)
                                <option value="{{ $roleName }}">{{ $roleName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold"><small>Ganti Password (Opsional)</small></label>
                            <input type="password" id="editPassword" name="password" class="form-control form-control-sm" placeholder="Kosongkan jika tidak diubah">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label class="font-weight-bold"><small>Role Tambahan</small></label>
                            <div id="additionalRolesContainer" class="row">
                                @foreach($roles as $roleName)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input additional-role-cb" id="ar_{{ $roleName }}" value="{{ $roleName }}">
                                        <label class="form-check-label" for="ar_{{ $roleName }}"><small>{{ $roleName }}</small></label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label class="font-weight-bold"><small>Tanda Tangan</small></label>
                            <input type="file" id="editSignature" name="signature" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg">
                            <div id="editSignaturePreviewWrapper" class="mt-1" style="display:none;">
                                <small class="text-muted d-block">Tanda Tangan Saat Ini:</small>
                                <img id="editSignaturePreview" src="" alt="Signature" class="sig-thumb" style="max-height: 55px; max-width: 150px;">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#editUserModal').modal('hide');">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveEditUser()"><i class="fa fa-save mr-1"></i> Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
    var selectedUserData = null;

    var usersTable = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: '{{ url("cns-user-management-data") }}',
        columns: [
            { data: 'index', orderable: false, searchable: false, className: 'text-center' },
            { data: 'username' },
            { data: 'name' },
            { data: 'email' },
            { data: 'location' },
            {
                data: 'roles',
                render: function(data, type, row) {
                    if (data && data.length > 0) {
                        return data.map(function(r) {
                            return '<span class="badge badge-info mr-1" style="background:#17a2b8;color:#fff;padding:2px 5px;border-radius:3px;font-size:11px;">' + r + '</span>';
                        }).join(' ');
                    }
                    return row.priv_id || '-';
                }
            },
            {
                data: 'status',
                className: 'text-center',
                render: function(data) {
                    return data === 'ACTIVE'
                        ? '<span class="badge-active">ACTIVE</span>'
                        : '<span class="badge-inactive">INACTIVE</span>';
                }
            },
            {
                data: 'signature_url',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data) {
                    if (data) {
                        return '<img src="' + data + '" class="sig-thumb" alt="Signature">';
                    }
                    return '<span class="text-muted small">-</span>';
                }
            }
        ],
        createdRow: function(row, data) {
            $(row).addClass('user-row').attr('data-user-id', data.id);
        }
    });

    $('#users-table tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            selectedUserData = null;
            $('#editUserBtn').prop('disabled', true);
            $('#deleteUserBtn').prop('disabled', true);
        } else {
            usersTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            selectedUserData = usersTable.row(this).data();
            $('#editUserBtn').prop('disabled', false);
            $('#deleteUserBtn').prop('disabled', false);
        }
    });

    function toggleCreateCard() {
        $('#createCardRow').toggleClass('d-none');
        if (!$('#createCardRow').hasClass('d-none')) {
            $('html, body').animate({ scrollTop: $('#createCardRow').offset().top - 80 }, 300);
        }
    }

    $('#createUserForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('#btnSubmitCreate').prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: '{{ url("cns-user-management-store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                $('#btnSubmitCreate').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Simpan Pengguna');
                $('#createUserForm')[0].reset();
                $('#createCardRow').addClass('d-none');
                usersTable.ajax.reload(null, false);
                swal('Berhasil!', 'Pengguna CNS berhasil dibuat.', 'success');
            },
            error: function(xhr) {
                $('#btnSubmitCreate').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Simpan Pengguna');
                var msg = 'Gagal menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                swal('Error', msg, 'error');
            }
        });
    });

    function openEditModal() {
        if (!selectedUserData) return;

        $('#editUserId').val(selectedUserData.id);
        $('#editUsername').val(selectedUserData.username);
        $('#editName').val(selectedUserData.name);
        $('#editEmail').val(selectedUserData.email);
        $('#editLocation').val(selectedUserData.location);
        $('#editStatus').val(selectedUserData.status);
        $('#editPassword').val('');

        var primary = selectedUserData.priv_id;
        if (selectedUserData.roles && selectedUserData.roles.length > 0) {
            primary = selectedUserData.roles[0];
        }
        $('#editPrimaryRole').val(primary);

        $('.additional-role-cb').prop('checked', false);
        if (selectedUserData.roles) {
            selectedUserData.roles.forEach(function(r) {
                if (r !== primary) {
                    $('#ar_' + r).prop('checked', true);
                }
            });
        }


        if (selectedUserData.signature_url) {
            $('#editSignaturePreview').attr('src', selectedUserData.signature_url);
            $('#editSignaturePreviewWrapper').show();
        } else {
            $('#editSignaturePreviewWrapper').hide();
        }

        $('#editUserModal').modal('show');
    }

    function saveEditUser() {
        var formData = new FormData($('#editUserForm')[0]);

        var additionalRoles = [];
        $('.additional-role-cb:checked').each(function() {
            additionalRoles.push($(this).val());
        });
        formData.delete('additional_roles[]');
        additionalRoles.forEach(function(r) {
            formData.append('additional_roles[]', r);
        });

        $.ajax({
            url: '{{ url("cns-user-management-update") }}/' + $('#editUserId').val(),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                $('#editUserModal').modal('hide');
                usersTable.ajax.reload(null, false);
                swal('Berhasil!', 'Data pengguna berhasil diperbarui.', 'success');
            },
            error: function(xhr) {
                var msg = 'Gagal memperbarui pengguna.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                swal('Error', msg, 'error');
            }
        });
    }

    function deleteUser() {
        if (!selectedUserData) return;

        swal({
            title: "Nonaktifkan Pengguna?",
            text: "User " + selectedUserData.name + " (" + selectedUserData.username + ") akan dinonaktifkan.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Nonaktifkan!",
            cancelButtonText: "Batal",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: '{{ url("cns-user-management-delete") }}/' + selectedUserData.id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        user_id: selectedUserData.id
                    },
                    success: function() {
                        usersTable.ajax.reload(null, false);
                        selectedUserData = null;
                        $('#editUserBtn').prop('disabled', true);
                        $('#deleteUserBtn').prop('disabled', true);
                        swal('Berhasil!', 'Pengguna telah dinonaktifkan.', 'success');
                    },
                    error: function() {
                        swal('Error', 'Gagal memproses data.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
