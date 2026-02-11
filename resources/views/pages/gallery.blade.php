@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
        <h1>Gallery</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Gallery</span>
            </li>
        </ol>
    </div>
    <!-- End Main Content Header -->

    <!-- Gallery -->
    <div class="gallery-content">
        <div class="row">
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/1.jpg') }}" src="{{ asset('assets/images/gallery/1.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/2.jpg') }}" src="{{ asset('assets/images/gallery/2.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/3.jpg') }}" src="{{ asset('assets/images/gallery/3.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/4.jpg') }}" src="{{ asset('assets/images/gallery/4.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/5.jpg') }}" src="{{ asset('assets/images/gallery/5.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/6.jpg') }}" src="{{ asset('assets/images/gallery/6.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/7.jpg') }}" src="{{ asset('assets/images/gallery/7.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/8.jpg') }}" src="{{ asset('assets/images/gallery/8.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/9.jpg') }}" src="{{ asset('assets/images/gallery/9.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/10.jpg') }}" src="{{ asset('assets/images/gallery/10.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/11.jpg') }}" src="{{ asset('assets/images/gallery/11.jpg') }}" alt="Gallery Image">
                </div>
            </div>
            <div class="col-lg-3 col-sm-4">
                <div class="single-image">
                    <img data-original="{{ asset('assets/images/gallery/12.jpg') }}" src="{{ asset('assets/images/gallery/12.jpg') }}" alt="Gallery Image">
                </div>
            </div>
        </div>
    </div>
    <!-- End Gallery -->
@endsection
