{{-- Vendor libraries used by BP inline page scripts. Loaded BEFORE the content
     so `new dhtmlXGridObject(...)`, jQuery, blockUI, etc. exist while the
     content is parsed. Adminor UI init stays in layout/adminor/scripts. --}}
<script src="{{ asset('adminor/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('jquery-migrate-3.4.1.min.js') }}"></script>
<script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('dhtmlx/dhtmlx.js') }}"></script>
<script src="{{ asset('js/cns-grid-pagination.js') }}"></script>
<script src="{{ asset('blockui.js') }}"></script>
<script src="{{ asset('jquery.ajaxloader.1.5.1.js') }}"></script>
<script src="{{ asset('jquery.confirm.js') }}"></script>
<script src="{{ asset('jquery-confirm.js') }}"></script>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script src="{{ asset('highcharts/highcharts.js') }}"></script>
<script src="{{ asset('highcharts/modules/exporting.js') }}"></script>
<script src="{{ asset('vault.js?v=3.0.0') }}"></script>
<script src="{{ asset('js/signature-canvas.js') }}"></script>
<script src="{{ asset('jquery.mask.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script src="{{ asset('adminor/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('adminor/plugins/moment/moment.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/js/sweetalert.min.js') }}"></script>
<script>
  if (window.swal) {
    var _origSwal = window.swal;
    window.swal = function () {
      var args = Array.prototype.slice.call(arguments);
      if (args.length > 0 && typeof args[0] === 'object' && args[0] !== null) {
        var opts = Object.assign({}, args[0]);
        if (opts.type && !opts.icon) {
          opts.icon = opts.type;
        }
        if (opts.showCancelButton && !opts.buttons) {
          opts.buttons = [opts.cancelButtonText || 'Batal', opts.confirmButtonText || 'OK'];
          if (opts.confirmButtonClass && opts.confirmButtonClass.indexOf('danger') !== -1) {
            opts.dangerMode = true;
          }
        }
        if (args.length >= 2 && typeof args[1] === 'function') {
          var cb = args[1];
          return _origSwal(opts).then(function (val) {
            cb(Boolean(val));
          });
        }
        args[0] = opts;
      }
      return _origSwal.apply(this, args);
    };
    for (var prop in _origSwal) {
      if (Object.prototype.hasOwnProperty.call(_origSwal, prop)) {
        window.swal[prop] = _origSwal[prop];
      }
    }
  }
</script>
<script>
  function LoadM(DivID, PathID) {
    $("#" + DivID).ajaxloader(PathID);
  }

  function NumOnly(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
    }
    return true;
  }

  function letterOnly(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
      return false;
    }
    return true;
  }

  function charOnly(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
      if (charCode > 31 && (charCode < 47 || charCode > 57)) {
        return false;
      } else {
        return true;
      }
    }
    return true;
  }

  function WithSym3(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
      if (charCode > 31 && (charCode < 41 || charCode > 57)) {
        return false;
      } else {
        return true;
      }
    }
    return true;
  }

  function WithSym2(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
      if (charCode > 31 && (charCode < 44 || charCode > 57)) {
        return false;
      } else {
        return true;
      }
    }
    return true;
  }

  function WithSym(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
      if (charCode > 31 && (charCode < 45 || charCode > 57)) {
        return false;
      } else {
        return true;
      }
    }
    return true;
  }

  function NoCOPAS(Elm) {
    $(Elm).bind("cut copy paste", function(e) {
      e.preventDefault();
      $(Elm).val('');
      $(Elm).bind("contextmenu", function(e) {
        e.preventDefault();
      });
    });
  }

  function NoteOnly(evt) {
    evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode : ((evt.which) ? evt.which : 0));
    if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
      if (charCode > 32 && (charCode < 41 || charCode > 57)) {
        return false;
      } else {
        return true;
      }
    }
    return true;
  }

  function number_format(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
  }
</script>
