@auth
    @if (!request()->is('/'))
        @php
            $isMitraChat = auth()->user() && auth()->user()->Priv_ID === 'MITRA';
        @endphp
        <a href="{{ url('/') }}" class="module-switcher-fab" title="Pilih Modul" aria-label="Pilih Modul"
           style="right: {{ $isMitraChat ? '364px' : '24px' }};">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/>
            </svg>
            <span>Modul</span>
        </a>
        <style>
            .module-switcher-fab { position: fixed; bottom: 24px; z-index: 1050; display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: 999px; background: #1a56a0; color: #fff; box-shadow: 0 8px 20px rgba(0,0,0,.22); text-decoration: none; font-weight: 600; font-size: 14px; line-height: 1; }
            .module-switcher-fab:hover, .module-switcher-fab:focus { color: #fff; background: #0d3f7a; text-decoration: none; }
            .module-switcher-fab svg { width: 18px; height: 18px; fill: currentColor; }
            @media (max-width: 768px) { .module-switcher-fab { right: 16px !important; bottom: 16px; } }
        </style>
    @endif
@endauth
