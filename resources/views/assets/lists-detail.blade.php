<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$Task_ID = $_GET['Task_ID'];
$datahasil = DB::table("asset_list")->where("Asset_No", $_GET['Task_ID'])->first();

// Riwayat log STNK & KIR (append-only, tidak pernah dihapus/ditimpa)
$stnkLog = DB::table('asset_stnk_log')->where('Asset_No', $Task_ID)->orderBy('ID', 'DESC')->get();
$kirLog = DB::table('asset_kir_log')->where('Asset_No', $Task_ID)->orderBy('Ke', 'DESC')->get();

$tglx = function ($v) {
    return ($v === null || $v === '' || $v == '0000-00-00') ? '-' : $v;
};

// Opsi Bank Pengakuan (Accounting)
$opt = '';
$Query = DB::table("bank_acc")->where("Status", 'ACTIVE')->orderBy('Bank_Acc', "asc")->get();
foreach ($Query as $Data) {
    if ($datahasil && $datahasil->Bank_Acc != '' && strcmp($datahasil->Bank_Acc, $Data->Acc_No) == 0) {
        $opt .= "<option value='" . e($Data->Acc_No) . "' selected>" . e($Data->Acc_No) . " - " . e($Data->Bank_Name) . "</option>";
    } else {
        $opt .= "<option value='" . e($Data->Acc_No) . "'>" . e($Data->Acc_No) . " - " . e($Data->Bank_Name) . "</option>";
    }
}

// Opsi Supplier (untuk field Supplier & Leasing Vendor)
$supplierOpt = '';
foreach (DB::table("supplier")->get() as $fa) {
    $supplierOpt .= "<option value='" . e($fa->Supplier_ID) . "'>" . e($fa->Company_Name) . " - " . e($fa->Supplier_ID) . "</option>";
}

$result = DB::select(DB::raw("SHOW COLUMNS FROM asset_list")); ?>

<div class="container-fluid mt-4">
    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white font-weight-bold p-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0" style="color:azure"><i class="fas fa-truck mr-2"></i>DETAIL DATA ASSET</h5>
            <?php if ($datahasil && $datahasil->Asset_Sub_Category == 'VEHICLE') { ?>
                <a href="hrd-vehicle-operations" target="_blank" class="btn btn-warning btn-sm font-weight-bold text-dark">
                    <i class="fas fa-gas-pump mr-1"></i> Logbook & Fuel Tracking Kendaraan
                </a>
            <?php } ?>
        </div>

        <div class="card-body">
            <div id="DivDetailAsset">

                <!-- Kelompok 1: Sumber -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">1. Sumber</h6>
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="ID">Asset ID</label>
                                <input type="text" id="ID" class="form-control" readonly />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Asset_No">Asset No</label>
                                <input type="text" id="Asset_No" class="form-control" readonly />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="PR_No">PR No</label>
                                <input type="text" id="PR_No" class="form-control" readonly />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="PO_No">PO No</label>
                                <input type="text" id="PO_No" class="form-control" readonly />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="GR_No">GR No</label>
                                <input type="text" id="GR_No" class="form-control" readonly />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 2: Klasifikasi -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">2. Klasifikasi</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Asset_Category">Asset Category</label>
                                <input type="text" id="Asset_Category" class="form-control" disabled="disabled" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Asset_Sub_Category">Asset Sub Category</label>
                                <input type="text" id="Asset_Sub_Category" class="form-control" disabled="disabled" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Asset_Type">Asset Type</label>
                                <input type="text" id="Asset_Type" class="form-control" disabled="disabled" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 3: Deskripsi & Spesifikasi -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">3. Deskripsi & Spesifikasi Asset</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Product_Description">Asset Name / Description</label>
                                <input type="text" id="Product_Description" class="form-control" placeholder="Nama asset..." />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Specification">Specification</label>
                                <input type="text" id="Specification" class="form-control" placeholder="Spesifikasi..." />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Product_Brand">Brand</label>
                                <input type="text" id="Product_Brand" class="form-control" placeholder="ex: toyota" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Model">Model</label>
                                <input type="text" id="Model" class="form-control" placeholder="ex: Kijang" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Product_Type">Type</label>
                                <input type="text" id="Product_Type" class="form-control" placeholder="ex: innova" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Sub_Product_Type">Sub Product Type</label>
                                <input type="text" id="Sub_Product_Type" class="form-control" placeholder="ex: G" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Product_Function">Function</label>
                                <input type="text" id="Product_Function" class="form-control" placeholder="ex: MPV" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Unit_Name">Unit Measurement</label>
                                <input type="text" id="Unit_Name" class="form-control" placeholder="ex: unit" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Source_Production">Bahan Bakar / Source</label>
                                <input type="text" id="Source_Production" class="form-control" placeholder="ex: BBM, Listrik" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Colour">Colour / Warna TNKB</label>
                                <input type="text" id="Colour" class="form-control" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Size">Size</label>
                                <input type="text" id="Size" class="form-control" placeholder="ex: CDD / Engkel" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Capacity">CC / Capacity</label>
                                <input type="text" id="Capacity" class="form-control" placeholder="ex: 1496" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 4: Registrasi, Pengadaan & Depresiasi -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">4. Registrasi, Pengadaan & Depresiasi</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Group_No">Group No</label>
                                <input type="text" id="Group_No" class="form-control" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Part_No">No Rangka</label>
                                <input type="text" id="Part_No" class="form-control" placeholder="ex: nomor rangka, product code" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="SN">No Mesin / Serial Number</label>
                                <input type="text" id="SN" class="form-control" placeholder="ex: nomor mesin, SN product" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Registration_No">Registration No (NOPOL)</label>
                                <input type="text" id="Registration_No" class="form-control" placeholder="ex: nomor plat, no SHM" />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Registration_Date">Registration Date</label>
                                <input type="text" id="Registration_Date" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Exp_Date">Expired Date</label>
                                <input type="text" id="Exp_Date" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Manufacture_Date">Manufacture Date</label>
                                <input type="text" id="Manufacture_Date" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Buy_Date">Buy Date (Tahun Perolehan)</label>
                                <input type="text" id="Buy_Date" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Owner">Owner</label>
                                <input type="text" id="Owner" class="form-control" placeholder="ex: raddix, MAM" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Buy_Val">Buy Value</label>
                                <input type="text" id="Buy_Val" class="form-control" placeholder="ex: 150000000" />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Supplier">Supplier</label>
                                <select id="Supplier" class="form-control"><?php echo $supplierOpt; ?></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Depr">Depreciation Year Max</label>
                                <input type="text" id="Depr" class="form-control" value="5" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Depr_M">Depreciation Method</label>
                                <select id="Depr_M" class="form-control">
                                    <option>STRAIGHT_MONTH</option>
                                    <option>STRAIGHT_YEAR</option>
                                    <option>SUM_OF_YEAR</option>
                                    <option>UNIT_OF_PRODUCTION</option>
                                    <option>DOUBLE_DECLINING_BALANCE</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Book_Val">Current Book Value</label>
                                <input type="text" id="Book_Val" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Additional_Val">Additional Value</label>
                                <input type="text" id="Additional_Val" class="form-control" value="0" />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Kelompok 5: Lokasi & Penanggung Jawab -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">5. Lokasi & Penanggung Jawab</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Asset_Location">Asset Location (Lokasi Site)</label>
                                <input type="text" id="Asset_Location" class="form-control" />
                            </div>
                            <div class="form-group col-md-8">
                                <label for="Asset_Location_Address">Address</label>
                                <input type="text" id="Asset_Location_Address" class="form-control" />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Asset_Location_Street">Street</label>
                                <input type="text" id="Asset_Location_Street" class="form-control" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Asset_Location_City">City</label>
                                <input type="text" id="Asset_Location_City" class="form-control" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Asset_Location_Department">Departement</label>
                                <input type="text" id="Asset_Location_Department" class="form-control" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="User_Responsible">User Responsible (PIC)</label>
                                <input type="text" id="User_Responsible" class="form-control" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="User_Responsible_NIK">NIK PIC</label>
                                <input type="text" id="User_Responsible_NIK" class="form-control" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="User_Sebelumnya">User Sebelumnya</label>
                                <input type="text" id="User_Sebelumnya" class="form-control" />
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Kelompok 6: Data Kendaraan -->
                <div id="vehicle-sections-detail">
                    <div class="card mb-3 border-light bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">6. Data Kendaraan</h6>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="BPKB_No">No. BPKB</label>
                                    <input type="text" id="BPKB_No" class="form-control" placeholder="ex: F03812510N" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Surat_Jalan_No">No. Surat Jalan (Buku Servis)</label>
                                    <input type="text" id="Surat_Jalan_No" class="form-control" placeholder="ex: U4352034" />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Asuransi">Asuransi</label>
                                    <input type="text" id="Asuransi" class="form-control" placeholder="ex: ASTRA INSURANCE (s.d 25 April 2028)" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="STNK_Tahunan">STNK Tahunan</label>
                                    <input type="text" id="STNK_Tahunan" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="STNK_5_Tahun">STNK 5 Tahunan</label>
                                    <input type="text" id="STNK_5_Tahun" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Jadwal_KIR">Jadwal KIR Terupdate</label>
                                    <input type="text" id="Jadwal_KIR" class="form-control" placeholder="ex: 23/12/2026" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Berkas_KIR">Berkas Update KIR Terakhir</label>
                                    <input type="text" id="Berkas_KIR" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Subsid_Mypertamina">Subsidi Barcode Mypertamina</label>
                                    <select id="Subsid_Mypertamina" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option>ADA</option>
                                        <option>TIDAK ADA</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Barcode_Kode">Kode Generated Barcode</label>
                                    <input type="text" id="Barcode_Kode" class="form-control" placeholder="ex: PCK-MLG-216/001" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Barcode_Kode_PSAK">Kode Generated Barcode (PSAK Version)</label>
                                    <input type="text" id="Barcode_Kode_PSAK" class="form-control" />
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="Riwayat_KIR">Riwayat KIR</label>
                                    <textarea id="Riwayat_KIR" class="form-control" rows="2" placeholder="ex: KIR 1: 23/06/2026 ..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 7: Dokumen & Riwayat Aset -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">7. Dokumen & Riwayat Aset</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Tanggal_Penghapusan">Tanggal Penghapusan</label>
                                <input type="text" id="Tanggal_Penghapusan" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Link_QR">Link Detail QR</label>
                                <input type="text" id="Link_QR" class="form-control" placeholder="https://..." />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="QR_Aset">QR Aset</label>
                                <input type="text" id="QR_Aset" class="form-control" />
                            </div>
                            <div class="form-group col-md-12">
                                <label for="Photo">Foto Aset (nama file / URL)</label>
                                <input type="text" id="Photo" class="form-control" />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Riwayat_Aset">Riwayat Aset</label>
                                <textarea id="Riwayat_Aset" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Dokumentasi">Dokumentasi Terkait</label>
                                <textarea id="Dokumentasi" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="Note">Note / Catatan Tambahan</label>
                                <textarea id="Note" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 8: Leasing & Cicilan -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">8. Leasing & Cicilan</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Leasing_Vendor">Leasing Vendor</label>
                                <select id="Leasing_Vendor" class="form-control"><?php echo $supplierOpt; ?></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Leasing_Val">Leasing Total Value</label>
                                <input type="text" id="Leasing_Val" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Leasing_Remain">Leasing Remain Value</label>
                                <input type="text" id="Leasing_Remain" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Leasing_Method">Payment Method</label>
                                <select id="Leasing_Method" class="form-control">
                                    <option>MONTHLY</option>
                                    <option>YEARLY</option>
                                    <option>WEEKLY</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Leasing_Method_Value">Payment Times Number</label>
                                <input type="text" id="Leasing_Method_Value" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Installment">Installment per Method</label>
                                <input type="text" id="Installment" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Installment_Method">Installment Method</label>
                                <select id="Installment_Method" class="form-control">
                                    <option>FIX_COST</option>
                                    <option>FLOATING_POINT</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Installment_Basic">Installment Basic</label>
                                <input type="text" id="Installment_Basic" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Installment_Interest">Installment Interest</label>
                                <input type="text" id="Installment_Interest" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Installment_No">Installment Number</label>
                                <input type="text" id="Installment_No" class="form-control" value="0" />
                                <small class="form-text text-muted">Pembayaran cicilan ke-</small>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Installment_Due_Days">Installment Due Days</label>
                                <input type="text" id="Installment_Due_Days" class="form-control" value="25" />
                                <small class="form-text text-muted">Tgl jatuh tempo tiap bulan</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 9: Accounting -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">9. Accounting</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Bank_Acc">Bank Pengakuan</label>
                                <select id="Bank_Acc" class="form-control" onChange="TampilkanCOA(1,this.value)">
                                    <option value="23104">LEASING</option>
                                    <?php echo $opt; ?>
                                </select>
                                <small class="form-text text-muted">Bank yang digunakan untuk pengakuan</small>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Acc_No">Akun No Pengakuan</label>
                                <input type="text" id="Acc_No" class="form-control" placeholder="ex: 00000" />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Acc_Cost">Akun No Depr Cost</label>
                                <input type="text" id="Acc_Cost" class="form-control" placeholder="ex: 00000" />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Acc_No_Depr">Akun No Depresiasi</label>
                                <input type="text" id="Acc_No_Depr" class="form-control" placeholder="ex: 00000" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Pengakuan_Uang_Muka">Uang Muka</label>
                                <input type="text" id="Pengakuan_Uang_Muka" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Pengakuan_Asuransi">Pengakuan Asuransi</label>
                                <input type="text" id="Pengakuan_Asuransi" class="form-control" value="0" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Pengakuan_Date">Tanggal Eksekusi Pengakuan</label>
                                <input type="text" id="Pengakuan_Date" class="form-control" readonly />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 10: Preventive Maintenance (PM) -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">10. Preventive Maintenance (PM)</h6>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="Interval_PM">Interval PM (bulan)</label>
                                <input type="text" id="Interval_PM" class="form-control" placeholder="ex: 6" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Tanggal_PM_Terakhir">Tanggal PM Terakhir</label>
                                <input type="text" id="Tanggal_PM_Terakhir" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Tanggal_Jadwal_PM">Jadwal Pemeliharaan / Next Maintenance</label>
                                <input type="text" id="Tanggal_Jadwal_PM" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="KM_Jadwal_PM">KM Jadwal PM</label>
                                <input type="text" id="KM_Jadwal_PM" class="form-control" placeholder="ex: 40000" />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Repair_Vendor">Repair Vendor</label>
                                <input type="text" id="Repair_Vendor" class="form-control" />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Last_Reminder_Sent">Last Reminder Sent</label>
                                <input type="text" id="Last_Reminder_Sent" class="form-control" placeholder="ex: 2026-10-21 08:00:00" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 11: Depresiasi per Baris Asset -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">11. Depresiasi (per baris asset)</h6>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="Depr_Pct">Depresiasi (%)</label>
                                <input type="number" step="1" id="Depr_Pct" class="form-control" placeholder="ex: 13" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="Depr_Month">Depresiasi Bulan Ke-</label>
                                <input type="number" step="1" id="Depr_Month" class="form-control" placeholder="ex: 6" />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Depr_Process">Sudah Diproses (0/1)</label>
                                <input type="number" step="1" min="0" max="1" id="Depr_Process" class="form-control" placeholder="0 / 1" />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Depr_Process_Date">Tgl Proses</label>
                                <input type="text" id="Depr_Process_Date" class="form-control" readonly />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Depr_Process_Val">Nilai Depresiasi</label>
                                <input type="number" step="0.01" id="Depr_Process_Val" class="form-control" placeholder="ex: 1250000" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 12: Express PM -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">12. Express PM</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Express_PM_Task_ID">Task ID</label>
                                <input type="text" id="Express_PM_Task_ID" class="form-control" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Express_PM_Category">Category</label>
                                <input type="text" id="Express_PM_Category" class="form-control" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Express_PM_Status">Status</label>
                                <input type="text" id="Express_PM_Status" class="form-control" />
                            </div>
                            <div class="form-group col-md-8">
                                <label for="Express_PM_Description">Description</label>
                                <input type="text" id="Express_PM_Description" class="form-control" />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Express_PM_Date">Tanggal</label>
                                <input type="text" id="Express_PM_Date" class="form-control" readonly />
                            </div>
                            <div class="form-group col-md-2">
                                <label for="Express_PM_By">Oleh</label>
                                <input type="text" id="Express_PM_By" class="form-control" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 13: Dokumen Leasing Lanjutan -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">13. Dokumen Leasing Lanjutan</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Leasing_No">Leasing / Contract No</label>
                                <input type="text" id="Leasing_No" class="form-control" />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Leasing_Date">Leasing Date</label>
                                <input type="text" id="Leasing_Date" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Installment_Last_Date">Installment Last Date</label>
                                <input type="text" id="Installment_Last_Date" class="form-control bg-white" readonly />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="Installment_Last_Invoice">Installment Last Invoice</label>
                                <input type="text" id="Installment_Last_Invoice" class="form-control" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /DivDetailAsset -->

            <!-- Kelompok 14: Pembaruan STNK & KIR (di luar DivDetailAsset agar tidak ikut terkirim saat Save) -->
            <div class="card mb-3 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning font-weight-bold border-bottom pb-2"><i class="fas fa-sync-alt mr-2"></i>14. Pembaruan STNK & KIR</h6>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted" style="font-size:12px">STNK TAHUNAN (berlaku)</div>
                                <b><?= $tglx(isset($datahasil->STNK_Tahunan) ? $datahasil->STNK_Tahunan : '') ?></b>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted" style="font-size:12px">STNK 5 TAHUNAN (berlaku)</div>
                                <b><?= $tglx(isset($datahasil->STNK_5_Tahun) ? $datahasil->STNK_5_Tahun : '') ?></b>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted" style="font-size:12px">JADWAL KIR BERIKUTNYA</div>
                                <b><?= $tglx(isset($datahasil->Jadwal_KIR) ? $datahasil->Jadwal_KIR : '') ?></b>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted" style="font-size:12px">BERKAS KIR TERAKHIR</div>
                                <b><?= $tglx(isset($datahasil->Berkas_KIR) ? $datahasil->Berkas_KIR : '') ?></b>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Update STNK -->
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="font-weight-bold mb-3">Update STNK</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="STNK_Upd_Jenis">Jenis STNK</label>
                                        <select id="STNK_Upd_Jenis" class="form-control">
                                            <option value="TAHUNAN">TAHUNAN (perpanjang tiap tahun)</option>
                                            <option value="5_TAHUNAN">5 TAHUNAN (perpanjang tiap 5 tahun)</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="STNK_Upd_Tanggal">Tgl Berlaku Baru</label>
                                        <input type="text" id="STNK_Upd_Tanggal" class="form-control bg-white" readonly />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="STNK_Upd_Catatan">Catatan</label>
                                        <input type="text" id="STNK_Upd_Catatan" class="form-control" placeholder="ex: lunas tahunan" />
                                    </div>
                                </div>
                                <button type="button" id="BtnUpdSTNK" class="btn btn-warning btn-sm px-3"><i class="fas fa-save mr-1"></i>Update STNK</button>
                                <small class="form-text text-muted mt-2">Setiap perubahan nilai STNK tercatat permanen di riwayat (history tidak dihapus/ditimpa).</small>
                            </div>
                        </div>

                        <!-- Update KIR -->
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="font-weight-bold mb-3">Update KIR</h6>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="KIR_Upd_Tanggal">Tanggal KIR</label>
                                        <input type="text" id="KIR_Upd_Tanggal" class="form-control bg-white" readonly />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="KIR_Upd_Berkas">Berkas KIR</label>
                                        <input type="text" id="KIR_Upd_Berkas" class="form-control" />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="KIR_Upd_Catatan">Catatan</label>
                                        <input type="text" id="KIR_Upd_Catatan" class="form-control" placeholder="ex: lulus" />
                                    </div>
                                </div>
                                <button type="button" id="BtnUpdKIR" class="btn btn-warning btn-sm px-3"><i class="fas fa-save mr-1"></i>Update KIR</button>
                                <small class="form-text text-muted mt-2">KIR pertama dilakukan dalam waktu 1 tahun setelah STNK diterbitkan. Jadwal KIR berikutnya: <b id="KIR_NextHint">-</b> (otomatis +6 bulan dari tanggal KIR pertama).</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat STNK -->
            <div class="card mb-3 border-light">
                <div class="card-body">
                    <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2"><i class="fas fa-history mr-2"></i>Riwayat STNK (tidak pernah dihapus)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" style="background:#fff">
                            <thead class="thead-light">
                                <tr>
                                    <th width="70">No</th>
                                    <th>Jenis</th>
                                    <th>Tgl Berlaku Baru</th>
                                    <th>Tgl Sebelumnya</th>
                                    <th>Catatan</th>
                                    <th>Oleh</th>
                                    <th>Waktu Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $nourut = 0; ?>
                                @if (count($stnkLog) > 0)
                                    <?php $__currentLoopData = $stnkLog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>{{ ++$nourut }}</td>
                                        <td>{{ $log->Jenis }}</td>
                                        <td>{{ $tglx($log->Tanggal_Efektif) }}</td>
                                        <td>{{ $tglx($log->Tanggal_Sebelumnya) }}</td>
                                        <td>{{ $log->Catatan }}</td>
                                        <td>{{ $log->Updated_By }}</td>
                                        <td>{{ $log->Created_Date }}</td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                @else
                                    <tr><td colspan="7" class="text-center text-muted">Belum ada riwayat perubahan STNK</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Riwayat KIR -->
            <div class="card mb-3 border-light">
                <div class="card-body">
                    <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2"><i class="fas fa-history mr-2"></i>Riwayat KIR (append-only)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" style="background:#fff">
                            <thead class="thead-light">
                                <tr>
                                    <th width="70">Ke-</th>
                                    <th>Tanggal KIR</th>
                                    <th>Jadwal KIR Berikutnya</th>
                                    <th>Berkas</th>
                                    <th>Catatan</th>
                                    <th>Oleh</th>
                                    <th>Waktu Input</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($kirLog) > 0)
                                    <?php $__currentLoopData = $kirLog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-center"><b>{{ $log->Ke }}</b></td>
                                        <td>{{ $tglx($log->Tanggal_KIR) }}</td>
                                        <td>{{ $tglx($log->Jadwal_KIR_Berikutnya) }}</td>
                                        <td>{{ $log->Berkas }}</td>
                                        <td>{{ $log->Catatan }}</td>
                                        <td>{{ $log->Created_By }}</td>
                                        <td>{{ $log->Created_Date }}</td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                @else
                                    <tr><td colspan="7" class="text-center text-muted">Belum ada riwayat KIR (KIR pertama akan menjadi record Ke-1)</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="divtom" class="mt-3 text-right">
                <button type="button" id="SubmitEditAsset" class="btn btn-success px-4"><i class="fas fa-save mr-2"></i>Save</button>
            </div>
        </div>
    </div>
</div>

<div id="DivProcess"></div>
<script>

// Prefill semua input berdasarkan kolom tabel asset_list
<?php if ($result && $datahasil) {
    foreach ($result as $row) {
        $fields = $row->Field;
        $datax = $datahasil->$fields;
        if ($datax == '0000-00-00') $datax = '';
        echo "$('#" . $fields . "').val(" . json_encode((string)$datax) . ");\n";
    }
} ?>

// Diperbaiki agar lebih fleksibel mendeteksi 'VEHICLE' atau 'KENDARAAN'
function toggleVehicleFieldsDetail(){
    var v = ($('#Asset_Sub_Category').val() || '').toString().trim().toUpperCase();
    if(v.includes('VEHICLE') || v.includes('KENDARAAN')){
        $('#vehicle-sections-detail').show();
    } else {
        $('#vehicle-sections-detail').hide();
    }
}

// Panggil fungsi saat halaman pertama kali diload agar section kendaraan langsung menyesuaikan
$(document).ready(function() {
    toggleVehicleFieldsDetail();
});

var csrfToken = '{{ csrf_token() }}';
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Buy_Date","Exp_Date",'Manufacture_Date','Registration_Date','STNK_Tahunan','STNK_5_Tahun','Tanggal_PM_Terakhir','Tanggal_Penghapusan','Tanggal_Jadwal_PM','Leasing_Date','Installment_Last_Date','Depr_Process_Date','Express_PM_Date','STNK_Upd_Tanggal','KIR_Upd_Tanggal']);
myCalendar.setDateFormat("%Y-%m-%d");

function getAllValues() {
    var inputValues = [];
    $('#DivDetailAsset input, #DivDetailAsset select, #DivDetailAsset textarea').each(function() {
        var type = $(this).attr("type");
        if (type == "checkbox" || type == "radio") {
            if (this.checked) {
                inputValues.push(encodeURIComponent($(this).attr("id")) + "=" + encodeURIComponent($(this).val()));
            }
        } else if (type != "button" && type != "submit") {
            inputValues.push(encodeURIComponent($(this).attr("id")) + "=" + encodeURIComponent($(this).val()));
        }
    });
    return inputValues.join('&');
}

function reloadDetail() {
    LoadM('DATAASSETSDTL', 'asset-lists-detail?Task_ID=' + encodeURIComponent($('#Asset_No').val()));
    if (typeof DATAASSETSRefresh === 'function') { DATAASSETSRefresh(); }
}

// Hitung jadwal KIR berikutnya (tanggal KIR + X bulan) untuk hint
function updateKIRHint() {
    var tgl = $('#KIR_Upd_Tanggal').val();
    if (!tgl) { $('#KIR_NextHint').text('-'); return; }
    var d = new Date(tgl);
    if (isNaN(d.getTime())) { $('#KIR_NextHint').text('-'); return; }
    
    // Ubah angka di bawah ini sesuai jumlah bulan yang diinginkan (misal: 6 untuk 6 bulan)
    var jumlahBulan = 6; 
    d.setMonth(d.getMonth() + jumlahBulan);
    
    var mm = ('0' + (d.getMonth() + 1)).slice(-2);
    var dd = ('0' + d.getDate()).slice(-2);
    $('#KIR_NextHint').text(d.getFullYear() + '-' + mm + '-' + dd);
}
$('#KIR_Upd_Tanggal').on('change keyup', updateKIRHint);

$("#SubmitEditAsset").click(function() {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "asset-lists-detail-submit?_token=" + csrfToken,
        type: "POST",
        data: getAllValues(),
        success: function(data, textStatus, jqXHR) {
            if (data.indexOf('success') != -1) {
                alert('Data Asset Berhasil Disimpan!');
                reloadDetail();
            } else {
                alert('Gagal menyimpan! ' + data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error Internal! Please Try again!");
        }
    });
});

$("#BtnUpdSTNK").click(function() {
    var tgl = $('#STNK_Upd_Tanggal').val();
    if (!tgl) { alert('Tanggal berlaku STNK wajib diisi!'); return; }
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "asset-lists-stnk-update?_token=" + csrfToken,
        type: "POST",
        data: {
            Asset_No: $('#Asset_No').val(),
            Jenis: $('#STNK_Upd_Jenis').val(),
            Tanggal_Berlaku: tgl,
            Catatan: $('#STNK_Upd_Catatan').val()
        },
        success: function(data) {
            if (data.indexOf('success') != -1) {
                alert('STNK diperbarui! Perubahan tercatat di riwayat.');
                reloadDetail();
            } else {
                alert(data);
            }
        },
        error: function() {
            alert("Error Internal! Please Try again!");
        }
    });
});

$("#BtnUpdKIR").click(function() {
    var tgl = $('#KIR_Upd_Tanggal').val();
    if (!tgl) { alert('Tanggal KIR wajib diisi!'); return; }
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "asset-lists-kir-update?_token=" + csrfToken,
        type: "POST",
        data: {
            Asset_No: $('#Asset_No').val(),
            Tanggal_KIR: tgl,
            Berkas_KIR: $('#KIR_Upd_Berkas').val(),
            Catatan: $('#KIR_Upd_Catatan').val()
        },
        success: function(data) {
            if (data.indexOf('success') != -1) {
                alert('KIR tercatat sebagai record baru! Jadwal KIR berikutnya diperbarui.');
                reloadDetail();
            } else {
                alert(data);
            }
        },
        error: function() {
            alert("Error Internal! Please Try again!");
        }
    });
});
</script>
