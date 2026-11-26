@php
	$modalId = $modalId ?? 'ImportMasterModal';
	$title = $title ?? 'Import Excel';
	$itemLabel = $itemLabel ?? 'data';
	$onImported = $onImported ?? 'refreshdata';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ $title }}</h5>
				<button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" onclick="closeMasterImportModal('{{ $modalId }}');">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<a href="{{ $templateUrl }}" class="btn btn-outline-primary btn-sm">Download Template Excel (.xlsx)</a>
					@if(!empty($hint))
						<small class="text-muted d-block mt-1">{!! $hint !!}</small>
					@endif
					<small class="text-muted d-block mt-1">Warna header: <span class="badge" style="background:#FADBD8;color:#78281F;">Merah</span> = wajib diisi (not null), <span class="badge" style="background:#EAECEE;color:#2C3E50;">Abu-abu</span> = opsional (nullable).</small>
				</div>
				<div class="form-group">
					<label for="{{ $modalId }}File">Pilih File Excel (.xlsx, .xls, .csv)</label>
					<input type="file" id="{{ $modalId }}File" class="text-black form-control-file" accept=".xlsx,.xls,.csv" />
				</div>
				<div id="{{ $modalId }}Result" class="mt-3" style="display:none;"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="closeMasterImportModal('{{ $modalId }}');">Tutup</button>
				<button type="button" class="btn btn-primary" id="{{ $modalId }}SubmitBtn" onclick="submitMasterImport('{{ $modalId }}', '{{ $importUrl }}', '{{ $itemLabel }}', '{{ $onImported }}');">Upload &amp; Import</button>
			</div>
		</div>
	</div>
</div>

<script>
	if (typeof window.escapeMasterImportHtml !== 'function') {
		window.escapeMasterImportHtml = function (str) {
			return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
		};
	}

	if (typeof window.closeMasterImportModal !== 'function') {
		window.closeMasterImportModal = function (modalId) {
			const fileInput = document.getElementById(modalId + 'File');
			const resultBox = document.getElementById(modalId + 'Result');
			if (fileInput) fileInput.value = '';
			if (resultBox) {
				resultBox.innerHTML = '';
				resultBox.style.display = 'none';
			}
			$('#' + modalId).modal('hide');
		};
	}

	if (typeof window.submitMasterImport !== 'function') {
		window.submitMasterImport = function (modalId, importUrl, itemLabel, onImportedName) {
		const fileInput = document.getElementById(modalId + 'File');
		const resultBox = document.getElementById(modalId + 'Result');
		if (!fileInput.files.length) {
			swal('Perhatian', 'Pilih file Excel terlebih dahulu.', 'warning');
			return;
		}

		const formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('file', fileInput.files[0]);

		const btn = document.getElementById(modalId + 'SubmitBtn');
		btn.disabled = true;
		resultBox.style.display = 'none';

		$.ajax({
			url: importUrl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (res) {
				let html = `<div class="alert alert-success mb-2">Berhasil import <strong>${Number(res.imported)}</strong> ${escapeMasterImportHtml(itemLabel)}.</div>`;
				if (res.skipped && res.skipped.length) {
					html += `<div class="alert alert-warning mb-2"><strong>Dilewati (${res.skipped.length}):</strong><ul class="mb-0">` +
						res.skipped.map(r => `<li>Baris ${Number(r.row)} (${escapeMasterImportHtml(r.name ?? r.company_name ?? '')}): ${escapeMasterImportHtml(r.reason)}</li>`).join('') + '</ul></div>';
				}
				if (res.errors && res.errors.length) {
					html += `<div class="alert alert-danger mb-0"><strong>Gagal (${res.errors.length}):</strong><ul class="mb-0">` +
						res.errors.map(r => `<li>Baris ${Number(r.row)} (${escapeMasterImportHtml(r.name ?? r.company_name ?? '')}): ${escapeMasterImportHtml(r.reason)}</li>`).join('') + '</ul></div>';
				}
				resultBox.innerHTML = html;
				resultBox.style.display = 'block';
				fileInput.value = '';
				if (res.imported > 0) {
					if (typeof window[onImportedName] === 'function') {
						window[onImportedName]();
					}
					const hasIssues = (res.skipped && res.skipped.length) || (res.errors && res.errors.length);
					if (!hasIssues) {
						closeMasterImportModal(modalId);
					}
					swal('Berhasil', `Berhasil import ${Number(res.imported)} ${itemLabel}.`, 'success');
				}
			},
			error: function (xhr) {
				const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengimport file.';
				resultBox.innerHTML = `<div class="alert alert-danger mb-0">${escapeMasterImportHtml(msg)}</div>`;
				resultBox.style.display = 'block';
			},
			complete: function () {
				btn.disabled = false;
			}
		});
		};
	}
</script>
