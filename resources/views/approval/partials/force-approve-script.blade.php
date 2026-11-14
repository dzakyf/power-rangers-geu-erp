<script>
function forceApproveRequest(id) {
    if (!id) {
        alert('Pilih data terlebih dahulu.');
        return;
    }
    if (!confirm('Force Approve akan menyetujui pengajuan ini tanpa melalui approval chain. Lanjutkan?')) {
        return;
    }
    $.ajax({
        url: '{{ url('partner-approval-force-approve') }}',
        type: 'POST',
        data: {
            id: id,
            _token: '{{ csrf_token() }}'
        },
        success: function(res) {
            if (res.success) {
                alert('Force Approve berhasil');
                location.reload();
            } else {
                alert('Gagal: ' + (res.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
        }
    });
}
</script>
