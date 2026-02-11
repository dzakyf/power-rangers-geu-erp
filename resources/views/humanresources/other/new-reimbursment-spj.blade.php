<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$nik =   Auth::user()->Emp_ID;

$result = DB::table('pum_karyawan')->where("Cust_ID", "'$nik'")->first();
$noSPJ = explode("/", $_GET['id']);


$Task_ID = 'RMB/' . $LSP . '/' . date('ymd') . '-' . date('His');

$source = filter_var($_GET['source'], FILTER_SANITIZE_STRING);
$total = str_replace(".", "", $_GET['total']);

?>
<!-- Stats Card -->
<div class="row">

    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW REIMBURSEMENT</div>
    <div id="AdvanceForm">
        <div class="FormInput_Form_Content">

            <div class="FormInput_left">

                Tanggal

            </div>

            <div class="FormInput_right">
                <input type="hidden" id="id_Task" size="15" value="<?php echo $_GET['id'] ?>" />
                <input type="hidden" id="Task_ID" size="15" value="<?php echo $Task_ID ?>" />
                <input type="text" id="Reimbursement_Date" size="15" value="<?php echo date('Y-m-d'); ?>" disabled />

            </div>

            <div class="FormInput_left">

                Responsibility

            </div>
            <div class="FormInput_right">
                <input type="text" size="10" id="User_Responsibility" value="<?php echo $result->Cust_ID ?>" disabled="disabled" /> ::
                <input type="text" readonly="readonly" size="30" id="User_Responsibility_Name" value="<?php echo $result->Name ?>" disabled="disabled" />::
                <input type="text" readonly="readonly" size="30" id="User_Reference" disabled="disabled" value="<?php echo $result->PIC ?>" /> <input type="hidden" readonly="readonly" size="20" id="User_Responsibility_ID" value="<?php echo $result->Inv_No ?>" />:: <input type="hidden" readonly="readonly" size="20" id="Acc_No" value="<?php echo $result->PIC ?>" />

            </div>
            <div class="FormInput_left">

                Departement

            </div>
            <div class="FormInput_right">

                <input type="text" id="Department" size="20" readonly="readonly" value="<?php echo $result->Department ?>" />

            </div>
            <div class="FormInput_left">

                Amount Estimated

            </div>
            <div class="FormInput_right">

                <input type="text" id="Total" size="20" value="<?= $total ?>" />

            </div>


            <div class="FormInput_left">

                Reference

            </div>
            <div class="FormInput_right">

                <input type="text" id="Reference_No" size="40" value="<?= $source ?>" />

            </div>
            <div class="FormInput_left" style="height:80px;">

                Note

            </div>
            <div class="FormInput_right" style="height:80px;">
                <textarea id="Description" cols="40" rows="3"></textarea>

            </div>


        </div>
        <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT DETAIL</div>

        <div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
        <div id="divtom">
            <input type="button" id="DelGridCash" NAME="DelGridCash" value="Delete" class="btn btn-danger mt-2 mr-2" />
            <input type="button" id="AddGridCash" NAME="AddGridCash" value="Add Detail" class="btn btn-info mt-2 mr-2" />
            <input type="button" value="Create Reimbursement" id="SubmitAdvance" onclick="SubmitAdvance()" class="btn btn-success mt-2 mr-2" />


        </div>
        <div id="WinCOA"></div>
        <script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
        <script src="{{ asset('jquery.coolfieldset.js') }}"></script>


        <script>
            $('#AddGridCash').click(function() {
                if (GridCash.getRowsNum() == 0) {
                    nums_reqrow = 1;
                    nomorrowsreq = 0;
                } else {
                    nums_reqrow = GridCash.getRowsNum() + 1;
                    nomorrowsreq = nums_reqrow - 1;
                }

                GridCash.addRow(GridCash.uid(), [nums_reqrow, '<?php echo date("Y-m-d"); ?>', '', '', '', 0, '1', "<?php echo $result->Department ?>"], GridCash.getRowsNum());
                GridCash.selectRow(nomorrowsreq);
            });

            $('#DelGridCash').click(function() {


                GridCash.deleteSelectedRows();


            });




            $("#Total").on("keyup", function(event) {

                // 1.
                var selection = window.getSelection().toString();
                if (selection !== '') {
                    return;
                }

                // 2.
                if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
                    return;
                }
                var $this = $(this);
                var input = $this.val();

                // 2
                var input = input.replace(/[\D\s\._\-]+/g, "");

                // 3
                input = input ? parseInt(input, 10) : 0;

                // 4
                $this.val(function() {
                    return (input === 0) ? "" : input.toLocaleString("en-US");
                });

            });



            $("#Reference_No").coolautosuggest({
                url: "reference-search?chars=",
                showThumbnail: false,
                showDescription: true,
                idField: $("#Reference_No"),
                width: 500,
                minChars: 1,
                onSelected: function(result) {
                    if (result != null) {

                        var Description = result.description;
                        Expr = Description.split('<br>');
                        $("#Reference_No").val(result.id);

                    }
                }
            });

            function getAllValues() {
                var inputValues = [];
                $('#AdvanceForm input,select,textarea').each(function() {
                    var type = $(this).attr("type");
                    if ((type == "checkbox" || type == "radio") && this.checked) {
                        inputValues.push($(this).attr("id") + "=" + $(this).val());
                    } else if (type != "button" || type != "submit") {
                        inputValues.push($(this).attr("id") + "=" + $(this).val());
                    } else if ($this.is("textarea")) {
                        inputValues.push($(this).attr("id") + "=" + $(this).val());
                    }
                })

                return inputValues.join('&');
            }

            function SubmitAdvance() {


                Total = document.getElementById("Total").value;
                if (Total <= 0) {
                    text = "Ammount Tidak boleh 0";
                    alert(text);
                    document.getElementById('Total').focus();
                    return false;
                }

                Note = document.getElementById("Description").value;
                if (Note.length < 10) {
                    text = "Note setidaknya memiliki 10 huruf";
                    alert(text);
                    document.getElementById('Description').focus();
                    return false;
                }
                dapat = getAllValues();
                PGridCash.sendData();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },

                    url: "reimbursement-spj-submit?_token={{ csrf_token() }}",
                    type: "POST",
                    data: dapat,
                    success: function(data, textStatus, jqXHR) {
                        if (data == 'success') {
                            alert('Reimbursement Task Created!');

                            setTimeout(function() {

                                location.href = 'submitted-spj';


                            }, 1000);

                        } else {

                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("error");

                    }
                });
            }

            GridCash = new dhtmlXGridObject('grid_kbterima');
            GridCash.setImagePath("./dhtmlx/imgs/");
            GridCash.setHeader("No.,Date,Reference,External Ref No,Note,Amount,Currency Val,Department");
            GridCash.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', "#stat_total", "", ""]);
            GridCash.setInitWidths("70,100,100,150,200,100,100,100");
            GridCash.setColAlign("center,left,center,left,left,right,right,center");
            GridCash.setColTypes("ro,ro,ed,ed,ed,edn,ro,ro");
            GridCash.setColSorting("int,str,str,str,str,str,str");
            GridCash.setDateFormat("%Y-%m-%d");
            GridCash.setEditable(true);
            GridCash.setNumberFormat("0,000", 5, ".", ",");
            GridCash.enableKeyboardSupport(true);
            GridCash.setSkin("dhx_skyblue");
            PGridCash = new dataProcessor("my-reimbursement-new-update");
            PGridCash.setTransactionMode({
                mode: "POST",
                total: true,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                payload: {
                    "ID": "<?php echo $Task_ID ?>"
                }

            })

            PGridCash.setUpdateMode("Off");
            PGridCash.init(GridCash);
            GridCash.init();
        </script>
    </div>