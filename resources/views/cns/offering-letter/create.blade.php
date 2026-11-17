@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Document Control</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('cns-offering-letter.index') }}">Offering Letter</a></li>
        <li class="breadcrumb-item active"><span class="active">New Offering Letter</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-header font-weight-bold">Buat Offering Letter Baru</div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <form action="{{ route('cns-offering-letter.store') }}" method="POST">
            @csrf
            @include('cns.offering-letter._form')

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan (Draft)</button>
                <a href="{{ route('cns-offering-letter.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
