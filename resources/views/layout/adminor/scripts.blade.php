{{-- Adminor UI initialization only. Vendor libraries (jQuery, dhtmlx,
     Bootstrap JS, legacy BP plugins, helpers) are loaded before the content
     in layout/adminor/vendor-scripts.blade.php. --}}
<script src="{{ asset('adminor/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('adminor/plugins/perfect-scrollbar/p-scroll.js') }}"></script>
<script src="{{ asset('adminor/plugins/side-menu/sidemenu.js') }}"></script>
<script src="{{ asset('adminor/js/eva-icons.min.js') }}"></script>
<script src="{{ asset('adminor/js/sticky.js') }}"></script>
<script src="{{ asset('adminor/js/script.js') }}"></script>
<script src="{{ asset('adminor/js/themecolor.js') }}"></script>
<script src="{{ asset('adminor/js/swither-styles.js') }}"></script>
<script src="{{ asset('adminor/js/custom.js') }}"></script>
@stack('rightmenu-script')
@stack('modals-script')
@stack('scripts')
