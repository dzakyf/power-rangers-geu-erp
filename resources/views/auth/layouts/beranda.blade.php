<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- CSRF Token -->
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<title>{{ config('app.name','MASAJI KARGOSENTRA TAMA') }}</title>

		<!-- Scripts -->
		@extends('layouts.plugins')


	</head>
	
	@extends('layouts.menu')
	
	<body class="body">
		<div id="modcontents">
			@yield('content')
		</div>
	</body>
	
</html>

<script>
function loadmodule(urlmod){
	
	LoadM('modcontents',urlmod);
}
</script>