@extends('layout.mainlayout-credential')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Credential</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Credential</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">System Settings</span>
		</li>
	</ol>
</div>

<div class="row m-1">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SYSTEM SETTINGS</div>
	<div class="card w-100">
		<div class="card-body">
			<p class="text-muted small mb-3">Kelola nilai konfigurasi sistem. Perubahan akan langsung dipakai oleh modul yang menggunakan config_system.</p>
			<div id="SettingsContainer"></div>
			<div class="mt-2">
				<button type="button" class="btn btn-primary" onclick="saveSettings()">Save</button>
				<button type="button" class="btn btn-secondary" onclick="loadSettings()">Reset</button>
			</div>
		</div>
	</div>
</div>

<script>
    var settingsData = [];

    function humanize(str) {
        if (!str) return '';
        return str
            .toLowerCase()
            .split('_')
            .map(function(w) { return w.charAt(0).toUpperCase() + w.slice(1); })
            .join(' ');
    }

    function fmtNum(v) {
        if (v === '' || v === null || v === undefined) return '';
        var n = Number(v);
        if (isNaN(n)) return v;
        return n.toLocaleString('id-ID', { maximumFractionDigits: 4 });
    }

    function renderSettings() {
        var html = '';
        settingsData.forEach(function(cfg) {
            html += `
                <div class="row align-items-center mb-2" data-id="${cfg.ID || ''}">
                    <div class="col-md-4">
                        <label class="mb-0 small font-weight-bold">${esc(humanize(cfg.Constraint))}</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" value="${esc(fmtNum(cfg.Value))}" data-field="value" />
                    </div>
                </div>`;
        });
        $('#SettingsContainer').html(html);
    }

    function esc(v) {
        return String(v === null || v === undefined ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function loadSettings() {
        $.get('master-config-system-data', function(res) {
            settingsData = (res.data || []).map(function(c) {
                return { ID: c.ID, Constraint: c.Constraint, Value: c.Value };
            });
            renderSettings();
        });
    }

    function saveSettings() {
        var configs = [];
        var errors = [];

        $('#SettingsContainer .row').each(function() {
            var $row = $(this);
            var id = $row.data('id');
            var value = $row.find('[data-field="value"]').val().replace(/\./g, '').replace(/,/g, '.');

            if (!id) return;
            if (value === '' || isNaN(Number(value))) {
                errors.push('Nilai harus berupa angka');
                return;
            }

            configs.push({ id: id, value: value });
        });

        if (errors.length) {
            alert(errors.join('\n'));
            return;
        }

        $.ajax({
            url: 'master-config-system-submit',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                configs: configs
            },
            success: function(res) {
                alert('Konfigurasi berhasil disimpan!');
                loadSettings();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error Internal!';
                alert(msg);
            }
        });
    }

    $(document).ready(function() {
        loadSettings();
    });
</script>
@endsection
