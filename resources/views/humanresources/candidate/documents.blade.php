@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="main-content-header">
    <h1>My Documents</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/candidate/dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active"><span>Documents</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Document</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td>{{ $labels[$cat] ?? $cat }}</td>
                        <td>
                            @if($docStatus[$cat]['uploaded'])
                            <span class="badge badge-success">Uploaded</span>
                            @else
                            <span class="badge badge-secondary">Not Uploaded</span>
                            @endif
                        </td>
                        <td>
                            <form class="upload-form" data-category="{{ strtolower($cat) }}" style="display:inline;">
                                @csrf
                                <input type="file" name="file" accept="image/*,.pdf" class="file-input" style="display:none">
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Upload</button>
                            </form>
                            @if($docStatus[$cat]['uploaded'])
                            <a href="{{ asset($docStatus[$cat]['filePath']) }}" class="btn btn-sm btn-success" target="_blank">View</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.upload-btn').click(function() {
        $(this).siblings('.file-input').click();
    });
    $('.file-input').change(function() {
        var form = $(this).closest('.upload-form');
        var category = form.data('category');
        var file = this.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('file', file);
        fd.append('category', category);
        fd.append('_token', '{{ csrf_token() }}');
        $.ajax({
            url: '{{ url("/candidate/document-upload") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.indexOf('success') !== -1) {
                    alert('File uploaded successfully!');
                    location.reload();
                } else {
                    alert(res);
                }
            },
            error: function() {
                alert('Upload failed!');
            }
        });
    });
});
</script>
@endsection
