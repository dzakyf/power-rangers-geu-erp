<div class="mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">NEW PURCHASE REQUEST</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPages">
                <div class="FormInput_Form_Content">
                    <div class="FormInput_left">

                        PR No

                    </div>
                    <div class="FormInput_right">

                        <input type="text" id="PR_No" size="30" readonly="readonly" value="<?php echo $PR_No; ?>" />

                    </div>
                    <div class="FormInput_left">

                        PR Date

                    </div>
                    <div class="FormInput_right">

                        <input type="date" id="PR_Date" size="15" value="<?php echo date('Y-m-d'); ?>" />

                    </div>
                    <div class="FormInput_left">

                        User Request

                    </div>
                    <div class="FormInput_right">

                        <input type="text" id="User_Request" size="15" value="<?php echo $NIK ?>" readonly="readonly" /> &nbsp;&nbsp;
                        <input type="text" id="User_Request_Name" size="40" value="<?php echo $Saya ?>" readonly="readonly" />

                    </div>

                    <!---->

                    <div class="FormInput_left">

                        Vendor

                    </div>
                    <div class="FormInput_right">

                        <input type="text" id="Supplier" size="10" value="" required />
                        <input type="text" id="Supplier_Name" size="40" value="" required readonly />

                    </div>

                    <div class="FormInput_left">

                        Delivery Date

                    </div>
                    <div class="FormInput_right">

                        <input type="date" id="Delivery_Date" size="30" value="{{ date("Y-m-d") }}" required />

                    </div>

                    <div class="FormInput_left" style="height:60px;">

                        Delivery Site

                    </div>
                    <div class="FormInput_right" style="height:60px;">

                        <textarea id="Delivery_Site" rows="2" cols="55" required></textarea>


                    </div>

                    <div class="FormInput_left">

                        Delivery Term

                    </div>
                    <div class="FormInput_right">

                        <input type="text" id="Delivery_Term" size="50" required />

                    </div>

                    <div class="FormInput_left">

                        Shipped By

                    </div>
                    <div class="FormInput_right">

                        <input type="text" id="Shipped_By" size="30" />

                    </div>

                    <div class="FormInput_left">

                        Payment Term

                    </div>
                    <div class="FormInput_right">

                        <input type="text" id="Payment_Term" size="80" value="Payment 25% before delivery and 75% 30 days after delivery (valid on working days)" />

                    </div>

                    <div class="FormInput_left">

                        Product Specification

                    </div>
                    <div class="FormInput_right">

                        <div style="width:250px;display:inline-block;">
                            <select id="Supplies_Type" style="width:100%;">
                                <option value="">-- Pilih Product Specification --</option>
                                @foreach(\App\CnsMasterKemasan::where('product_type', 'Export')->orderBy('Name')->get() as $spec)
                                    <option value="{{ $spec->Name }}">{{ $spec->Name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="FormInput_left">

                        References

                    </div>
                    <div class="FormInput_right">

                        <input type="text" id="Reference" size="40" />

                    </div>
                    <div class="FormInput_left">

                        Subtotal

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Subtotal" size="10" value="0.00" readonly required />

                    </div>
                    <div class="FormInput_left">

                        Tax

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Tax" size="10" value="0.00" onkeyup="recalc()" required />

                    </div>
                    <div class="FormInput_left">

                        Shipping

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Shipping" size="10" value="0.00" onkeyup="recalc()" required />

                    </div>
                    <div class="FormInput_left">

                        Other

                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Other" size="10" value="0.00" onkeyup="recalc()" required />

                    </div>
                    <div class="FormInput_left">
                        Total
                    </div>
                    <div class="FormInput_right">

                        <input type="text" inputmode="decimal" class="cns-decimal" id="Total" size="10" value="0.00" required />

                    </div>
                    <div class="FormInput_left">

                        Note

                    </div>
                    <div class="FormInput_right" style="height:150px;">
                        <textarea id="Note" cols="100" rows="5" required>- Price include PPN and delivery cost to PT PEL
        - Payment 50% before delivery and 50% 14 days after delivery (valid on working days)
        - The product be received in PT PEL not later than April 25, 2026</textarea>
                    </div>
                    <div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
                    <div id="divtom">
                        <input type="button" id="AddPRNewDetail" class="btn btn-info mt-2 mr-2" NAME="AddPRNewDetail" value="Add New" />
                        <input type="button" id="DelPRNewDetail" class="btn btn-danger mt-2 mr-2" NAME="DelPRNewDetail" value="Delete" />
                        <input type="submit" id="SubmitPRNewDetail" class="btn btn-success mt-2 mr-2" NAME="SubmitPRNewDetail" value="Submit" />
                    </div>
                    <div id="WinCOA"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script>
    var WinPR;
    var WPR;

    function loadPR_Product(text, url, width, height) {
        WinPR = new dhtmlXWindows();
        WinPR.attachViewportTo("ContentPages");
        WPR = WinPR.createWindow("WPR", 0, 0, width, height);
        WPR.keepInViewport(false);
        WPR.setText(text);
        WPR.attachURL(url, true);
        WPR.button("minmax1").hide();
        WPR.button("park").hide();
        WPR.centerOnScreen();
        WPR.allowMove();
    }


    function getAllValues() {
        var inputValues = [];
        $('#ContentPages input,select,textarea').each(function() {
            var type = $(this).attr("type");
            var val = $(this).hasClass('cns-decimal') ? cnsParseNumber($(this).val(), 0) : $(this).val();
            if ((type == "checkbox" || type == "radio") && this.checked) {
                inputValues.push($(this).attr("id") + "=" + val);
            } else if (type != "button" || type != "submit") {
                inputValues.push($(this).attr("id") + "=" + val);
            } else if ($this.is("textarea")) {
                inputValues.push($(this).attr("id") + "=" + val);
            }
        })

        return inputValues.join('&');
    }
    
    $('#PR_Date').on('change', function() {
        var date = new Date($(this).val());
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        $('#PR_No').val('PR' + year + month + day + '-' + Math.floor(Math.random() * 1000));
    });


    if ($.fn.select2) {
        $('#Supplies_Type').select2({
            width: '100%',
            placeholder: '-- Pilih Product Specification --',
            allowClear: true
        });
    }

    PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
    PRNewDetail.setImagePath("dhtmlx/imgs/");
    PRNewDetail.setHeader("No,Product Specification,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Prices,Total");
    PRNewDetail.setInitWidths("50,100,100,100,200,200,100,200,100,100,100,100");
    PRNewDetail.setColTypes("ron,ed,ed,ed,edn,ed,ed,ed,ro,ro,edn,ron");
    PRNewDetail.setColAlign("center,left,center,left,right,left,center,center,center,center,center");
    PRNewDetail.setColSorting("int,str,str,str,str,str,str,str");
    PRNewDetail.setEditable(true);
    PRNewDetail.setNumberFormat("0,000.00", 4, ".", ",");
    PRNewDetail.setNumberFormat("0,000.00", 10, ".", ",");
    PRNewDetail.setNumberFormat("0,000.00", 11, ".", ",");
    PRNewDetail.enableKeyboardSupport(true);
    PRNewDetail.setSkin("dhx_skyblue");
    PRNewDetail.enableMultiline(true);
    PPRNewDetail = new dataProcessor("cns-procurement-purchase-request-new-update");
    PPRNewDetail.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        payload: {
            "PR_No": "<?php echo $PR_No;  ?>"
        }


    })
    PPRNewDetail.setUpdateMode("Off");
    PPRNewDetail.init(PRNewDetail);
    PRNewDetail.init();
    PRNewDetail.attachEvent('onEditCell', function(stage, rId, cInd, nValue, oValue) {
        console.log(stage)
        if (stage == 2) {
            if (cInd == 4 || cInd == 10) {
                let qty = parseFloat(PRNewDetail.cells(rId, 4).getValue());
                let prices = parseFloat(PRNewDetail.cells(rId, 10).getValue());
                if (qty != "" && prices != '') {
                    PRNewDetail.cells(rId, 11).setValue((qty * prices).toFixed(2));
                    recalc();
                }
            }
        }
        return true; // Allow the edit
    })

    $("#AddPRNewDetail").click(function() {
        PRNewDetail.addRow(PRNewDetail.uid(), [PRNewDetail.getRowsNum() + 1, $("#Supplies_Type").val(), '', '', 1, '', '', '', '', '', '0', '0'], PRNewDetail.getRowsNum());

    })

    function recalc() {
        let total = 0;
        if (PRNewDetail.getRowsNum() > 0) {
            let getIds = PRNewDetail.getAllRowIds().split(',');
            for (let i = 0; i < getIds.length; i++) {
                const ele = getIds[i];
                total += cnsParseNumber(PRNewDetail.cells(ele, 11).getValue(), 0);
            }
        }
        let tax = cnsParseNumber($('#Tax').val(), 0);
        let shipping = cnsParseNumber($('#Shipping').val(), 0);
        let other = cnsParseNumber($('#Other').val(), 0);
        $('#Subtotal').val(cnsFormatNumber(total, 2));
        $('#Total').val(cnsFormatNumber(total + tax + shipping + other, 2));
    }
    $("#DelPRNewDetail").click(function() {

        PRNewDetail.deleteSelectedRows();

    })



    $("#Supplier").coolautosuggest({

        url: "search-supplier?Procode=1&chars=",
        showThumbnail: false,
        showDescription: true,
        idField: $("#Supplier"),
        width: 700,
        minChars: 1,
        onSelected: function(result) {
            if (result != null) {
                $("#Supplier").val(result.id);
                $("#Supplier_Name").val(result.data);


            }
        }
    });


    $("#SubmitPRNewDetail").click(function() {
        var count = PRNewDetail.getRowsNum();
        if (count == 0) {
            alert('Masukkan Item Request Terlebih Dahulu! ');
            return false;
        } else {
            Reference = $("#Reference").val();
            Note = $("#Note").val();
            Department = $("#Department").val();
            User_Request = $("#User_Request").val();
            Date_Request = $("#Date_Request").val();
            PR_No = $("#PR_No").val();
            Category = $("#Category").val();
            Sub_Category = $("#Sub_Category").val();
            Sub_Category2 = $("#Purchase_Type").val();
            PIC = $("#PIC").val();
            PIC_Phone = $("#PIC_Phone").val();
            PIC_Address = $("#PIC_Address").val();
            Estimated_Budget = $("#Estimated_Budget").val();

            if (Note == '') {
                alert("Kolom catatan (Note) Harus Diisi!");
                $("#Note").focus();
                return false;
            } else if (Note.length < 5) {
                alert("Kolom catatan (Note) minimal 5 karakter!");
                $("#Note").focus();
                return false;
            } else {
                PPRNewDetail.sendData();

                var sip = 1;
                for (var i = 0; i < PRNewDetail.getRowsNum(); i++) {
                    PurchaseType = PRNewDetail.cells2(i, 1).getValue();
                    if (PurchaseType == Sub_Category2) {
                        sip *= 1;
                    } else {
                        sip *= 0;
                    }
                }
                $.blockUI();

                dapat = getAllValues();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },

                    url: "cns-procurement-purchase-request-new-submit?_token={{ csrf_token() }}",
                    type: "POST",
                    data: dapat,
                    success: function(data, textStatus, jqXHR) {
                        if (data == 'success') {
                            alert('Purchase Request Submitted with no  <?php echo $PR_No ?>!');

                            setTimeout(function() {


                                $.unblockUI();
                                location.href = 'po-outstanding';
                            }, 1000);

                        } else {

                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("error");

                    }
                });


            }
        }
    });

    function selesai() {
        $.unblockUI();
        return true;
    }

    function loadSubCat(valCat) {
        LoadM('SubCat', 'pr-load-subcat?Category=' + encodeURI(valCat));
    }

    function gantiSub(valCat, vals) {
        LoadM('SubCat2', 'pr-load-sub?Main_Category=' + encodeURI(valCat) + '&subval=' + encodeURI(vals));
    }
</script>