<div class="my-4 card">
    <div class="card-body">
        <h5>Leads Detail</h5>
        <form id="ContentPages">
            <input type="hidden" name="ID" id="ID" />
            <div class="row">
                <div class="col-md-6">
                    <label for="Task_ID">Lead ID</label>
                    <input type="text" id="Task_ID" name="Task_ID" class="form-control" readonly />
                </div>
                <div class="col-md-6">
                    <label for="Full_Name">Full Name</label>
                    <input type="text" id="Full_Name" name="Full_Name" class="form-control" />
                </div>
                <div class="col-md-6 mt-3">
                    <label for="Phone_Number">Contact</label>
                    <input type="text" id="Phone_Number" name="Phone_Number" class="form-control" />
                </div>

                <div class="col-md-6 mt-3">
                    <label for="Partnership_Interest">Tipe Hub</label>
                    <select class="form-control" name="Hub_Type" id="Hub_Type">
                        <option value="" selected disabled>-- Pilih Jenis Produk --</option>
                        <option value="mobil" selected>Mobil</option>
                        <option value="booth" selected>Booth</option>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label for="Capital_Estimation">Capital Estimation</label>
                    <input type="text" id="Capital_Estimation" name="Capital_Estimation" class="form-control" />
                </div>
                <div class="col-md-6 mt-3">
                    <label class="col-form-label"><strong>Bagaimana Anda mengetahui program kemitraan ini?</strong></label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Instagram" value="Instagram" />
                                <label class="form-check-label" for="Source_Instagram">Instagram</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Facebook" value="Facebook" />
                                <label class="form-check-label" for="Source_Facebook">Facebook</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Source_Channel" id="Source_TikTok" value="TikTok" />
                                <label class="form-check-label" for="Source_TikTok">TikTok</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Website" value="Website" />
                                <label class="form-check-label" for="Source_Website">Website</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Source_Channel" id="Source_Event" value="Event" />
                                <label class="form-check-label" for="Source_Event">Pameran/Acara</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <label for="Response">Response</label>
                    <select class="form-control" name="Response" id="Response">
                        <option value="" selected disabled>-- Pilih Response --</option>

                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label for="Qualification">Kualifikasi</label>
                    <select class="form-control" name="Qualification" id="Qualification">
                        <option value="" selected disabled>-- Pilih Kualifikasi --</option>

                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label for="Classification">Klasifikasi</label>
                    <select class="form-control" name="Classification" id="Classification">
                        <option value="" selected disabled>-- Pilih Klasifikasi --</option>

                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label for="Status">Status</label>
                    <select class="form-control" name="Status" id="Status" onchange="changeStatus()">
                        <option value="" selected disabled>-- Pilih Status --</option>

                    </select>
                </div>
                <div class="col-md-6 mt-3" style="display:none" id="divReason">
                    <label for="Reason">Alasan Cancel</label>
                    <select class="form-control" name="Reason" id="Reason">
                        <option value="" selected disabled>-- Pilih Cancel Reason --</option>

                    </select>
                </div>
                <div class="col-12 mt-3">
                    <button type="button" class="btn btn-success" id="SubmitCustDetail">Submit</button>
                    <button type="button" class="btn btn-secondary" id="CancelSubmitCustDetail">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var capitalEstimationMap = {
        1: '< 50 Juta',
        2: '100 - 200 Juta',
        3: '200 - 300 Juta',
        4: '> 300 Juta'
    };

    $(document).ready(function() {
        prefillSelectBox();
        getLeads();
    })

    $('#CancelSubmitCustDetail').on('click', function() {
        $('#divDetailLeads').hide()
    })

    function prefillSelectBox() {
        var selects = [{
                url: 'master-lead-response',
                id: '#Response'
            },
            {
                url: 'master-lead-qualification',
                id: '#Qualification'
            },
            {
                url: 'master-lead-status',
                id: '#Status'
            },
            {
                url: 'master-lead-classification',
                id: '#Classification'
            },
            {
                url: 'master-lead-cancel-reason',
                id: '#Reason'
            },
        ];

        selects.forEach(function(item) {
            $.ajax({
                url: item.url,
                type: 'GET',
                success: function(res) {
                    var select = $(item.id);
                    res.data.forEach(function(row) {
                        select.append('<option value="' + row.status + '">' + row.status + '</option>');
                    });
                }
            });
        });
    }

    function getLeads() {
        var id = $('#ID').val();

        $.ajax({
            url: 'partner-leads?id=' + id,
            type: 'GET',
            success: function(res) {
                var data = res.data[0];
                if (!data) return;

                $('#ID').val(data.ID)
                $('#Task_ID').val(data.Task_ID);
                $('#Full_Name').val(data.Full_Name);
                $('#Phone_Number').val(data.Phone_Number);
                // $('#Partnership_Interest').val('Opsi ' + data.Partnership_Interest);
                $('#Hub_Type').val(data.Hub_Type);
                $('#Capital_Estimation').val(capitalEstimationMap[data.Capital_Estimation] || data.Capital_Estimation);

                $('input[name="Source_Channel"]').prop('checked', false);
                $('input[name="Source_Channel"][value="' + data.Source_Channel + '"]').prop('checked', true);

                $('#Response').val(data.Response);
                $('#Qualification').val(data.Qualification);
                $('#Classification').val(data.Classification);
                $('#Status').val(data.Status);
                $('#Reason').val(data.Note_Cancellation);
                changeStatus();
            },
            error: function() {
                alert('Gagal memuat data leads.');
            }
        });
    }

    function updateLeads() {
        var id = $('#ID').val();
        var payload = new FormData(document.getElementById('ContentPages'));

        $.ajax({
            url: 'partner-leads-update?id=' + id,
            type: 'POST',
            data: payload,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    alert('Data berhasil disimpan!');
                } else {
                    alert('Gagal menyimpan: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!';
                alert(msg);
            }
        });
    }

    function changeStatus() {
        var Status = $("#Status").find('option:selected').val();
        if (Status == 'Cancel') {
            $('#divReason').show();
        } else {
            $('#divReason').hide();
        }
    }

    $('#SubmitCustDetail').click(function() {
        let Status = $("#Status").val();
        if (Status == 'Cancel') {
            let Reason = $('#Reason').val()
            if (Reason == '') {
                alert("Alasan cancel harus diisi");
                return false;
            }
        }

        updateLeads()

        // gettingVars = getAllValues();

        // $.ajax({
        //     headers: {
        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //     },
        //     url: "partner-outstanding-leads-detail-submit?_token={{ csrf_token() }}",
        //     type: "POST",
        //     data: gettingVars,
        //     success: function(data, textStatus, jqXHR) {
        //         var dataPos = data.indexOf("success");
        //         if (dataPos != '-1') {
        //             alert('Data Opportunity Submitted!');
        //             location.href = "partner-outstanding-leads";
        //         } else {
        //             alert('Opportunity Failed to Submit!');
        //         }
        //     },
        //     error: function(jqXHR, textStatus, errorThrown) {
        //         alert("Error Internal! Please Try again!");
        //     }
        // });
    })

    function getAllValues() {
        var inputValues = [];
        $('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
            var type = $(this).attr("type");
            if ((type == "checkbox" || type == "radio") && this.checked) {
                inputValues.push($(this).attr("name") + "=" + $(this).val());
            } else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
                inputValues.push($(this).attr("id") + "=" + $(this).val());
            } else if ($(this).is("textarea")) {
                inputValues.push($(this).attr("id") + "=" + $(this).val());
            }
        })
        return inputValues.join('&');
    }
</script>