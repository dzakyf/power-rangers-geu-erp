<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>GREEN ENERGI UTAMA</title>
<link rel="icon" href="{{ asset('adminor/img/favicon.png') }}" type="image/x-icon">

{{-- Adminor V3 (Bootstrap 5) --}}
<link href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" id="style">
<link href="{{ asset('adminor/css/style.css') }}" rel="stylesheet">
<link href="{{ asset('adminor/css/plugins.css') }}" rel="stylesheet">
<link href="{{ asset('adminor/css/icons.css') }}" rel="stylesheet">
<link href="{{ asset('adminor/css/animate.css') }}" rel="stylesheet">

<style>
    :root {
        --primary-bg-color: #0a5221ff;
        --primary-bg-hover: #083b19ff;
        --primary-bg-border: #0d7a30ff;
    }
</style>

{{-- Legacy BP plugin CSS (dhtmlx grids, confirm, vault, select2, timeline) --}}
<link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/dhtmlx.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolautosuggest.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolfieldset.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('jquery-confirm.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('form_input.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('vault.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/timeline.min.css') }}">
<link href="{{ asset('ajaxloader/ajaxloader.css') }}" type="text/css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">
<style>.select2-container{width:100%!important;display:inline-block!important}.select2-container .selection,.select2-container .select2-selection,.select2-container .select2-selection--single{width:100%!important;display:block!important}</style>
<link href="{{ asset('css/font-uniform.css') }}" rel="stylesheet">
@stack('head')
