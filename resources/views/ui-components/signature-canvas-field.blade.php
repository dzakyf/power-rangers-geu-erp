@php
$id = $id ?? uniqid('sig_');
$name = $name ?? 'signature';
$label = $label ?? 'Signature';
$height = $height ?? 200;
$required = isset($required) ? true : false;
@endphp

<div class="sig-wrap" data-sig-id="{{ $id }}">

    @if($label)
    <label class="d-block mb-1">
        {{ $label }}
        @if($required) <span class="text-danger">*</span> @endif
    </label>
    @endif

    <canvas
        id="sig_canvas_{{ $id }}"
        height="{{ $height }}"
        style="border: 1px solid #ced4da; border-radius: 4px; cursor: crosshair; width: 100%; display: block; background: #fff;">
    </canvas>

    <div class="mt-1">
        <button type="button"
            class="btn btn-sm btn-outline-secondary"
            onclick="Signature.clear('{{ $id }}')">
            Clear
        </button>
    </div>

    {{-- This is what gets submitted with any form --}}
    <input type="hidden"
        id="sig_input_{{ $id }}"
        name="{{ $name }}">

    <div class="invalid-feedback d-block" id="sig_error_{{ $id }}"></div>
</div>