<script src="{{ asset('jquery2.min.js')}}"></script>
<script src="{{ asset('timeline.min.js')}}"></script>

<script>
  function LoadM(DivID, PathID) {

    $("#" + DivID).ajaxloader(PathID);

  }
</script>


<link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/dhtmlx.css') }}">
<script src="{{ asset('dhtmlx/dhtmlx.js')}}"></script>



<script type="text/javascript" src="{{ asset('./jquery.confirm.js') }}"></script>
<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.0.0.js"></script>

<link rel="stylesheet" type="text/css" href="{{ asset('form_input.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('./jquery-confirm.css') }}" />

<script src="{{ asset('vault.js?v=3.0.0')}}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('vault.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('css/timeline.min.css') }}" />

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram.css') }}">
<link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram-editor.css') }}">
<link rel="STYLESHEET" type="text/css" href="{{ asset('dhtmlx/diagram.css') }}">

<script src="{{ asset('dhtmlx/diagram.js')}}"></script>
<script src="{{ asset('dhtmlx/diagram-editor.js')}}"></script>
<script src="{{ asset('highcharts/highcharts.js')}}"></script>
<script src="{{ asset('highcharts/modules/exporting.js')}}"></script>

<style>
  .JudulContent {
    height: 30px;
    position: relative;
    vertical-align: middle;
    font-size: 14px;
    font-weight: bold;
    padding-left: 10px;
    padding-top: 5px;
    width: 100%;
  }
</style>
<script>
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