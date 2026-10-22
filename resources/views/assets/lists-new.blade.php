<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
    
$getPO = DB::table('purchase_order as po')
    ->where("Purchase_Category", "ASSET")
    ->where('po.Status_Receive', 'WAITING')
    ->where('po.Status', "CLOSED")
    ->leftJoin("supplier", 'po.Supplier', '=', 'supplier.Supplier_ID')
    ->select('po.PO_No', 'supplier.Company_Name')
    ->get();
$qx = DB::select(DB::raw("SELECT * FROM master_product_group WHERE Initial IN ('FA')"));

$gudang = DB::table('gudang_list')->get(); ?>

<div class="container-fluid mt-4">
    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white font-weight-bold p-3">
            <h5 class="m-0" style="color:azure"><i class="fas fa-boxes mr-2"></i>DATA ASSET</h5>
        </div>
        
        <div class="card-body">
            <div id="DivContentFinish">
                
                <!-- Kelompok 1: Sumber & Klasifikasi Asset -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">1. Sumber</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Incoming_Type" class="font-weight-bold">Incoming From</label>
                                <select id="Incoming_Type" class="form-control">
                                    <option>MANUAL</option>
                                    <option>PO</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6 pono" style="display:none;">
                                <label for="PO_No" class="font-weight-bold">PO No</label>
                                <select id="PO_No" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Pilih data PO --</option>
                                    <?php $__currentLoopData = $getPO; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="{{ $item->PO_No }}">{{ $item->PO_No }} - {{ $item->Company_Name }}</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                
                            <!-- Pilihan PO Item (Muncul jika pilih PO) -->
                            <div class="form-group col-md-6 pono" style="display:none;">
                                <label for="PO_Item_Select" class="font-weight-bold">Pilih Item dari PO</label>
                                <select id="PO_Item_Select" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Pilih Item --</option>
                                </select>
                            </div>
                
                            <!-- Pilihan PO Item (Muncul jika pilih PO) -->
                            <div class="form-group col-md-6 pono" style="display:none;">
                                <label for="Item_Qty_Select" class="font-weight-bold">Pilih Qty dari Item</label>
                                <select id="Item_Qty_Select" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Pilih Qty --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok 2: Detail Deskripsi & Spesifikasi -->
                <div class="card mb-3 border-light bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">2. Klasifikasi</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="Asset_Category" class="font-weight-bold">Asset Category</label>
                                <select id="Asset_Category" class="form-control" onchange="loadSubcategory();">
                                    <?php foreach ($qx as $fa){
                                            echo "<option value='$fa->Product_Group'>$fa->Product_Group - $fa->Initial</option>";
                                        } ?>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="Asset_Sub_Category" class="font-weight-bold">Asset Sub Category</label>
                                <div id="subcat">
                                    <select id="Asset_Sub_Category" class="form-control"></select>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="Asset_Type" class="font-weight-bold">Asset Type</label>
                                <div id="subcat2">
                                    <select id="Asset_Type" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Container untuk Form Item Aset (Bisa Multi-Row berdasarkan Qty) -->
                <div id="asset-items-container">
                    <!-- Form Item Aset akan di-render di sini via JavaScript -->
                </div>

                <div id="asset-items-manual" style="display:block;">
                    <!-- Kelompok 3: Detail Deskripsi & Spesifikasi -->
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
                                <div class="form-group col-md-3">
                                    <label for="Product_Brand">Brand</label>
                                    <input type="text" id="Product_Brand" class="form-control" placeholder="ex: Toyota" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Model">Model</label>
                                    <input type="text" id="Model" class="form-control" placeholder="ex: Kijang" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Product_Type">Type</label>
                                    <input type="text" id="Product_Type" class="form-control" placeholder="ex: Innova" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Sub_Product_Type">Sub Product Type</label>
                                    <input type="text" id="Sub_Product_Type" class="form-control" placeholder="ex: G" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Product_Function">Function</label>
                                    <input type="text" id="Product_Function" class="form-control" placeholder="ex: MPV" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Unit_Name">Unit Measurement</label>
                                    <input type="text" id="Unit_Name" class="form-control" placeholder="ex: Unit" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Colour">Colour</label>
                                    <input type="text" id="Colour" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Size">Size</label>
                                    <input type="text" id="Size" class="form-control" placeholder="ex: 40m x 20m / CDD" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Capacity">Capacity</label>
                                    <input type="text" id="Capacity" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Source_Production">Source Production</label>
                                    <input type="text" id="Source_Production" class="form-control" placeholder="ex: BBM, Listrik" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kelompok 4: Registrasi, Pengadaan & Depresiasi -->
                    <div class="card mb-3 border-light bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">4. Registrasi, Pengadaan & Depresiasi</h6>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="Group_No">Group No</label>
                                    <input type="text" id="Group_No" class="form-control" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Part_No">Part No</label>
                                    <input type="text" id="Part_No" class="form-control" placeholder="No Rangka / Product Code" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="SN">Serial Number</label>
                                    <input type="text" id="SN" class="form-control" placeholder="No Mesin / SN Product" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Registration_No">Registration No</label>
                                    <input type="text" id="Registration_No" class="form-control" placeholder="Plat / SHM / dll" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Manufacture_Date">Manufacture Date</label>
                                    <input type="text" id="Manufacture_Date" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Registration_Date">Registration Date</label>
                                    <input type="text" id="Registration_Date" class="form-control bg-white" readonly placeholder="Tgl BPKB / SHM" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Exp_Date">Expired Date</label>
                                    <input type="text" id="Exp_Date" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Owner">Owner</label>
                                    <input type="text" id="Owner" class="form-control" placeholder="ex: RADDIX, MAM" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Buy_Date">Buy Date</label>
                                    <input type="text" id="Buy_Date" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Buy_Val">Buy Value</label>
                                    <input type="text" id="Buy_Val" class="form-control" placeholder="000000000" />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Supplier">Supplier</label>
                                    <?php $qr = DB::select(DB::raw("SELECT Supplier_ID, Company_Name FROM supplier ORDER BY Company_Name ASC"));
                                    echo "<select id='Supplier' class='form-control select2'>";
                                    echo "<option value=''>-- Pilih Supplier --</option>";    
                                    foreach($qr as $fa){
                                        echo "<option value='".$fa->Supplier_ID."'>".$fa->Company_Name." - ".$fa->Supplier_ID."</option>";    
                                    }
                                    echo "</select>"; ?>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Depr">Depreciation Year Max</label>
                                    <input type="text" id="Depr" class="form-control" value="5" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Depr_M">Depreciation Method</label>
                                    <select id="Depr_M" class="form-control">
                                        <option>STRAIGHT_MONTH</option>
                                        
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Book_Val">Current Book Value</label>
                                    <input type="text" id="Book_Val" class="form-control" value="0" />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Additional_Val">Additional Value</label>
                                    <input type="text" id="Additional_Val" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Kelompok 5: Informasi Leasing / Cicilan -->
                    <div class="card mb-3 border-light bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">5. Informasi Leasing & Cicilan</h6>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="Leasing_Vendor">Leasing Vendor</label>
                                    <?php $qr = DB::select(DB::raw("SELECT * FROM supplier ORDER BY Company_Name ASC"));
                                    echo "<select id='Leasing_Vendor' class='form-control select2'>";
                                    echo "<option value=''>-- Pilih Vendor Leasing --</option>";    
                                    foreach($qr as $fa){
                                        echo "<option value='".$fa->Supplier_ID."'>".$fa->Company_Name." - ".$fa->Supplier_ID."</option>";    
                                    }
                                    echo "</select>"; ?>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Leasing_Val">Leasing Total Value</label>
                                    <input type="text" id="Leasing_Val" class="form-control" value="0" />
                                    <small class="form-text text-muted">Nilai total leasing</small>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Leasing_Remain">Leasing Remain Value</label>
                                    <input type="text" id="Leasing_Remain" class="form-control" value="0" />
                                    <small class="form-text text-muted">Nilai sisa pinjaman</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Leasing_Method">Payment Method</label>
                                    <select id="Leasing_Method" class="form-control">
                                        <option>MONTHLY</option>
                                        <option>YEARLY</option>
                                        <option>WEEKLY</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Leasing_Method_Value">Payment Times Number</label>
                                    <input type="text" id="Leasing_Method_Value" class="form-control" value="0" />
                                    <small class="form-text text-muted">Jumlah bulan pembayaran</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Installment">Installment Amount / Method</label>
                                    <input type="text" id="Installment" class="form-control" value="0" />
                                    <small class="form-text text-muted">Nilai setiap pembayaran</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Installment_Method">Installment Method</label>
                                    <select id="Installment_Method" class="form-control">
                                        <option>FIX_COST</option>
                                        <option>FLOATING_POINT</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Installment_Basic">Installment Basic</label>
                                    <input type="text" id="Installment_Basic" class="form-control" value="0" />
                                    <small class="form-text text-muted">Nilai pinjaman pokok</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Installment_Interest">Installment Interest</label>
                                    <input type="text" id="Installment_Interest" class="form-control" value="0" />
                                    <small class="form-text text-muted">Nilai bunga</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Installment_No">Installment Number</label>
                                    <input type="text" id="Installment_No" class="form-control" value="0" />
                                    <small class="form-text text-muted">Pembayaran cicilan ke-</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Installment_Due_Days">Installment Due Days</label>
                                    <input type="text" id="Installment_Due_Days" class="form-control" value="25" />
                                    <small class="form-text text-muted">Tgl jatuh tempo tiap bulan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Kelompok 6: Data Kendaraan -->
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
                                <div class="form-group col-md-4">
                                    <label for="Service_Terakhir">Tanggal Service Terakhir</label>
                                    <input type="text" id="Service_Terakhir" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Service_Berikutnya">Tanggal Service Berikutnya</label>
                                    <input type="text" id="Service_Berikutnya" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="Riwayat_KIR">Riwayat KIR</label>
                                    <textarea id="Riwayat_KIR" class="form-control" rows="2" placeholder="ex: KIR 1: 23/06/2026 ..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kelompok 7: Dokumen & Riwayat Aset -->
                    <div class="card mb-3 border-light bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">7. Dokumen & Riwayat Aset</h6>
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label for="Interval_PM">Interval PM (bulan)</label>
                                    <input type="text" id="Interval_PM" class="form-control" placeholder="ex: 6" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Tanggal_PM_Terakhir">Tanggal PM Terakhir</label>
                                    <input type="text" id="Tanggal_PM_Terakhir" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Tanggal_Penghapusan">Tanggal Penghapusan</label>
                                    <input type="text" id="Tanggal_Penghapusan" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="User_Sebelumnya">User Sebelumnya</label>
                                    <input type="text" id="User_Sebelumnya" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Link_QR">Link Detail QR</label>
                                    <input type="text" id="Link_QR" class="form-control" placeholder="https://..." />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="QR_Aset">QR Aset</label>
                                    <input type="text" id="QR_Aset" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="Last_Reminder_Sent">Last Reminder Sent</label>
                                    <input type="text" id="Last_Reminder_Sent" class="form-control" placeholder="ex: 2026-10-21 08:00:00" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Tanggal_Jadwal_PM">Jadwal Pemeliharaan / Next Maintenance</label>
                                    <input type="text" id="Tanggal_Jadwal_PM" class="form-control bg-white" readonly />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="KM_Jadwal_PM">KM Jadwal PM</label>
                                    <input type="text" id="KM_Jadwal_PM" class="form-control" placeholder="ex: 40000" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Jadwal_PM">Jadwal PM (bebas)</label>
                                    <input type="text" id="Jadwal_PM" class="form-control" placeholder="ex: 6 BULAN" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Repair_Vendor">Repair Vendor</label>
                                    <input type="text" id="Repair_Vendor" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="User_Responsible">PIC / User Responsible</label>
                                    <input type="text" id="User_Responsible" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="User_Responsible_NIK">NIK PIC</label>
                                    <input type="text" id="User_Responsible_NIK" class="form-control" />
                                </div>
                                <div class="form-group col-md-4">
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
                </div>
                


            </div>
            
            <div id="divtom" class="mt-3 text-right">
                <button type="button" id="SubmitNewAsset" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i> Save Data Asset</button>
            </div>
        </div>
    </div>
</div>

<div id="DivProcess"></div>

<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Buy_Date","Exp_Date",'Manufacture_Date','Registration_Date','STNK_Tahunan','STNK_5_Tahun','Service_Terakhir','Service_Berikutnya','Tanggal_PM_Terakhir','Tanggal_Penghapusan','Tanggal_Jadwal_PM','Leasing_Date','Installment_Last_Date']);
myCalendar.setDateFormat("%Y-%m-%d");
loadSubcategory()
function loadSubcategory(){
    LoadM('subcat','asset-lists-sub-category?Category=' + encodeURI($(`#Asset_Category`).val()));
    setTimeout(() => {
        loadassettype();
    }, 500);
}

function loadassettype(){
    LoadM('subcat2','asset-lists-sub-sub-category?Category=' + encodeURI($(`#Asset_Category`).val()) + '&Sub_Category=' + encodeURI($(`#Asset_Sub_Category`).val()));
}

function getAllValues() {
    var inputValues = [];
    $('#DivContentFinish input, #DivContentFinish select, #DivContentFinish textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio")) {
            if (this.checked) {
                inputValues.push(encodeURIComponent($(this).attr("id")) + "=" + encodeURIComponent($(this).val()));
            }
        }
        else if (type != "button" && type != "submit") {
            inputValues.push(encodeURIComponent($(this).attr("id")) + "=" + encodeURIComponent($(this).val()));
        } else if ($(this).is("textarea")) {
            inputValues.push(encodeURIComponent($(this).attr("id")) + "=" + encodeURIComponent($(this).val()));
        }
    });
    return inputValues.join('&');
}

$('#SubmitNewAsset').click(function(){
    var gettingVars = getAllValues();

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url : "asset-lists-new-submit?_token={{ csrf_token() }}",
        type: "POST",
        data : gettingVars,
        success: function(data, textStatus, jqXHR)
        {
            var dataPos = data.indexOf("success");
            if (dataPos != '-1'){
                alert('Data New Asset Submitted!');
                DATAASSETSRefresh();
                $("#DivContentFinish").html('');
            } else {
                alert('New Asset Failed to Submit!');
            }
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert( "Error Internal! Please Try again!" );
        }
    });
});

$("#Incoming_Type").change(function(e) {
    let val = $(this).val();

    if (val != 'PO') {
        $('#PO_No').val('').trigger('change');
        $('.pono').hide();
        $('#asset-items-manual').show()
        return;
    }
    
    $('.pono').show();
    $('#asset-items-manual').hide()
});

$('#PO_No').select2().on('change', function(e) {
    e.preventDefault();

    let val = $(this).val();
    if (val == '') {
        return;
    }

    $.ajax({
        url:"hrd-asset-lists-new-from-po",
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        type: "POST",
        data:{pono:val},
        success:function(res){
            if (res.status == 'success') {
                let options = '<option value="">-- Pilih Item PO --</option>';
                res.data.Details.forEach(item => {
                    options += `<option value="${item.Product_Code}" data-qty="${item.Qty}" data-price="${item.Total}" data-name="${item.Product_Desc}" data-category="${item.Purchase_Type}" data-brand="${item.Product_Brand}" data-subcat="${item.Category}" data-subsubcat="${item.Supplies_Type}">${item.Product_Desc} (Qty: ${item.Qty})</option>`;
                });
                $('#PO_Item_Select').html(options);
            }
        }
    });
});
$('#PO_Item_Select').on('change', function() {
    let selected = $(this).find(':selected');
    let qty = parseInt(selected.data('qty')) || 1;
    let cat = selected.data('category') || '';
    let subcat = selected.data('subcat') || '';
    let subsubcat = selected.data('subsubcat') || '';

    $('#Asset_Category').val(cat).trigger('change')
    setTimeout(() => {
        $('#Asset_Sub_Category').val(subcat)
        $('#Asset_Sub_Category').trigger('change')
        $('#Asset_Type').val(subsubcat).trigger('change')
    }, 500);
    

    $('#Item_Qty_Select').find('option:not(:first)').remove();
    
    for (let i = 1; i <= qty; i++) {
        $('#Item_Qty_Select').append($("<option/>", {
            value: i,
            text: i
        }));
    }
});
$('#Item_Qty_Select').on('change', function() {
    let selected = $("#PO_Item_Select").find(':selected');
    let qty = $(this).val();
    let itemData = {
        item_name: selected.data('name'),
        unit_price: selected.data('price'),
    };

    renderAssetForms(itemData, qty);
})
function assetLocation(e,index)
{
    let val = $(e).val();
    console.log(val,index)
	
    if (val == '') {
        return;
    }

	$('#Asset_Location_Address' + index).val('')
	$('#Asset_Location_Street' + index).val('')
	$('#Asset_Location_City' + index).val('')
	$('#Asset_Location_Department' + index).val('')

    $.ajax({
        url:"hrd-asset-lists-new-get-wh",
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        type: "POST",
        data:{wh:val},
        success:function(res){
            if (res.status == 'success') {
				const DATA = res.data;
				$('#Asset_Location_Address' + index).val(DATA.Address)
				$('#Asset_Location_Street' + index).val(DATA.Street)
				$('#Asset_Location_City' + index).val(DATA.City)
				$('#Asset_Location_Department' + index).val(DATA.Department)
            }else {
				
			}
        }
    });
}
function renderAssetForms(itemData, qty) {
    let html = '';
    
    for (let i = 0; i < qty; i++) {
        html += `
        
        <div class="card mb-4 border-primary asset-item-block" data-index="${i}">
            <div class="card-header bg-secondary text-white font-weight-bold p-2">
                Aset Unit ke-${i + 1} dari ${qty} (${itemData.item_name || 'Item Asset'})
            </div>
        </div>
        
        <!-- Kelompok 3: Detail Deskripsi & Spesifikasi -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-body">
                <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">3. Deskripsi & Spesifikasi Asset</h6>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="Product_Description${i}">Asset Name / Description</label>
                        <input type="text" id="Product_Description${i}" data-index="${i}" class="form-control" placeholder="Nama asset..." value="${itemData.item_name || ''}" />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Specification${i}">Specification</label>
                        <input type="text" id="Specification${i}" data-index="${i}" class="form-control" placeholder="Spesifikasi..." value="${itemData.spec || ''}" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Product_Brand${i}">Brand</label>
                        <input type="text" id="Product_Brand${i}" data-index="${i}" class="form-control" placeholder="ex: Toyota" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Model${i}">Model</label>
                        <input type="text" id="Model${i}" data-index="${i}" class="form-control" placeholder="ex: Kijang" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Product_Type${i}">Type</label>
                        <input type="text" id="Product_Type${i}" data-index="${i}" class="form-control" placeholder="ex: Innova" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Sub_Product_Type${i}">Sub Product Type</label>
                        <input type="text" id="Sub_Product_Type${i}" data-index="${i}" class="form-control" placeholder="ex: G" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Product_Function${i}">Function</label>
                        <input type="text" id="Product_Function${i}" data-index="${i}" class="form-control" placeholder="ex: MPV" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Unit_Name${i}">Unit Measurement</label>
                        <input type="text" id="Unit_Name${i}" data-index="${i}" class="form-control" placeholder="ex: Unit" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Colour${i}">Colour</label>
                        <input type="text" id="Colour${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Size${i}">Size</label>
                        <input type="text" id="Size${i}" data-index="${i}" class="form-control" placeholder="ex: 40m x 20m / CDD" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Capacity${i}">Capacity</label>
                        <input type="text" id="Capacity${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Source_Production${i}">Source Production</label>
                        <input type="text" id="Source_Production${i}" data-index="${i}" class="form-control" placeholder="ex: BBM, Listrik" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelompok 4: Registrasi, Pengadaan & Depresiasi -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-body">
                <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">4. Registrasi, Pengadaan & Depresiasi</h6>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="Group_No${i}">Group No</label>
                        <input type="text" id="Group_No${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Part_No${i}">Part No</label>
                        <input type="text" id="Part_No${i}" data-index="${i}" class="form-control" placeholder="No Rangka / Product Code" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="SN${i}">Serial Number</label>
                        <input type="text" id="SN${i}" data-index="${i}" class="form-control" placeholder="No Mesin / SN Product" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Registration_No${i}">Registration No</label>
                        <input type="text" id="Registration_No${i}" data-index="${i}" class="form-control" placeholder="Plat / SHM / dll" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Manufacture_Date${i}">Manufacture Date</label>
                        <input type="text" id="Manufacture_Date${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Registration_Date${i}">Registration Date</label>
                        <input type="text" id="Registration_Date${i}" data-index="${i}" class="form-control bg-white" readonly placeholder="Tgl BPKB / SHM" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Exp_Date${i}">Expired Date</label>
                        <input type="text" id="Exp_Date${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Owner${i}">Owner</label>
                        <input type="text" id="Owner${i}" data-index="${i}" class="form-control" placeholder="ex: RADDIX, MAM" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Buy_Date${i}">Buy Date</label>
                        <input type="text" id="Buy_Date${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Buy_Val${i}">Buy Value</label>
                        <input type="text" id="Buy_Val${i}" data-index="${i}" class="form-control" placeholder="000000000" value="${itemData.unit_price || 0}" />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Supplier${i}">Supplier</label>
                        <?php $qr = DB::select(DB::raw("SELECT Supplier_ID, Company_Name FROM supplier ORDER BY Company_Name ASC"));
                        echo "<select id='Supplier' class='form-control select2'>";
                        echo "<option value=''>-- Pilih Supplier --</option>";    
                        foreach($qr as $fa){
                            echo "<option value='".$fa->Supplier_ID."'>".$fa->Company_Name." - ".$fa->Supplier_ID."</option>";    
                        }
                        echo "</select>"; ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Depr${i}">Depreciation Year Max</label>
                        <input type="text" id="Depr${i}" data-index="${i}" class="form-control" value="5" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Depr_M${i}">Depreciation Method</label>
                        <select id="Depr_M${i}" data-index="${i}" class="form-control">
                            <option>STRAIGHT_MONTH</option>
                            
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Book_Val${i}">Current Book Value</label>
                        <input type="text" id="Book_Val${i}" data-index="${i}" class="form-control" value="0" />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Additional_Val${i}">Additional Value</label>
                        <input type="text" id="Additional_Val${i}" data-index="${i}" class="form-control" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelompok 5: Informasi Leasing / Cicilan -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-body">
                <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">5. Informasi Leasing & Cicilan</h6>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="Leasing_Vendor${i}">Leasing Vendor</label>
                        <?php $qr = DB::select(DB::raw("SELECT * FROM supplier ORDER BY Company_Name ASC"));
                        echo "<select id='Leasing_Vendor' class='form-control select2'>";
                        echo "<option value=''>-- Pilih Vendor Leasing --</option>";    
                        foreach($qr as $fa){
                            echo "<option value='".$fa->Supplier_ID."'>".$fa->Company_Name." - ".$fa->Supplier_ID."</option>";    
                        }
                        echo "</select>"; ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Leasing_Val${i}">Leasing Total Value</label>
                        <input type="text" id="Leasing_Val${i}" data-index="${i}" class="form-control" value="0" />
                        <small class="form-text text-muted">Nilai total leasing</small>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Leasing_Remain${i}">Leasing Remain Value</label>
                        <input type="text" id="Leasing_Remain${i}" data-index="${i}" class="form-control" value="0" />
                        <small class="form-text text-muted">Nilai sisa pinjaman</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Leasing_Method${i}">Payment Method</label>
                        <select id="Leasing_Method${i}" data-index="${i}" class="form-control">
                            <option>MONTHLY</option>
                            <option>YEARLY</option>
                            <option>WEEKLY</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Leasing_Method_Value${i}">Payment Times Number</label>
                        <input type="text" id="Leasing_Method_Value${i}" data-index="${i}" class="form-control" value="0" />
                        <small class="form-text text-muted">Jumlah bulan pembayaran</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Installment${i}">Installment Amount / Method</label>
                        <input type="text" id="Installment${i}" data-index="${i}" class="form-control" value="0" />
                        <small class="form-text text-muted">Nilai setiap pembayaran</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Installment_Method${i}">Installment Method</label>
                        <select id="Installment_Method${i}" data-index="${i}" class="form-control">
                            <option>FIX_COST</option>
                            <option>FLOATING_POINT</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Installment_Basic${i}">Installment Basic</label>
                        <input type="text" id="Installment_Basic${i}" data-index="${i}" class="form-control" value="0" />
                        <small class="form-text text-muted">Nilai pinjaman pokok</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Installment_Interest${i}">Installment Interest</label>
                        <input type="text" id="Installment_Interest${i}" data-index="${i}" class="form-control" value="0" />
                        <small class="form-text text-muted">Nilai bunga</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Installment_No${i}">Installment Number</label>
                        <input type="text" id="Installment_No${i}" data-index="${i}" class="form-control" value="0" />
                        <small class="form-text text-muted">Pembayaran cicilan ke-</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Installment_Due_Days${i}">Installment Due Days</label>
                        <input type="text" id="Installment_Due_Days${i}" data-index="${i}" class="form-control" value="25" />
                        <small class="form-text text-muted">Tgl jatuh tempo tiap bulan</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Kelompok 6: Data Kendaraan -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-body">
                <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">6. Data Kendaraan</h6>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="BPKB_No${i}">No. BPKB</label>
                        <input type="text" id="BPKB_No${i}" data-index="${i}" class="form-control" placeholder="ex: F03812510N" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Surat_Jalan_No${i}">No. Surat Jalan (Buku Servis)</label>
                        <input type="text" id="Surat_Jalan_No${i}" data-index="${i}" class="form-control" placeholder="ex: U4352034" />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Asuransi${i}">Asuransi</label>
                        <input type="text" id="Asuransi${i}" data-index="${i}" class="form-control" placeholder="ex: ASTRA INSURANCE (s.d 25 April 2028)" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="STNK_Tahunan${i}">STNK Tahunan</label>
                        <input type="text" id="STNK_Tahunan${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="STNK_5_Tahun${i}">STNK 5 Tahunan</label>
                        <input type="text" id="STNK_5_Tahun${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Jadwal_KIR${i}">Jadwal KIR Terupdate</label>
                        <input type="text" id="Jadwal_KIR${i}" data-index="${i}" class="form-control" placeholder="ex: 23/12/2026" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Berkas_KIR${i}">Berkas Update KIR Terakhir</label>
                        <input type="text" id="Berkas_KIR${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Subsid_Mypertamina${i}">Subsidi Barcode Mypertamina</label>
                        <select id="Subsid_Mypertamina${i}" data-index="${i}" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option>ADA</option>
                            <option>TIDAK ADA</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Barcode_Kode${i}">Kode Generated Barcode</label>
                        <input type="text" id="Barcode_Kode${i}" data-index="${i}" class="form-control" placeholder="ex: PCK-MLG-216/001" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Barcode_Kode_PSAK${i}">Kode Generated Barcode (PSAK Version)</label>
                        <input type="text" id="Barcode_Kode_PSAK${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Service_Terakhir${i}">Tanggal Service Terakhir</label>
                        <input type="text" id="Service_Terakhir${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Service_Berikutnya${i}">Tanggal Service Berikutnya</label>
                        <input type="text" id="Service_Berikutnya${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-12">
                        <label for="Riwayat_KIR${i}">Riwayat KIR</label>
                        <textarea id="Riwayat_KIR${i}" data-index="${i}" class="form-control" rows="2" placeholder="ex: KIR 1: 23/06/2026 ..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelompok 7: Dokumen & Riwayat Aset -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-body">
                <h6 class="card-title text-secondary font-weight-bold border-bottom pb-2">7. Dokumen & Riwayat Aset</h6>
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label for="Interval_PM${i}">Interval PM (bulan)</label>
                        <input type="text" id="Interval_PM${i}" data-index="${i}" class="form-control" placeholder="ex: 6" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Tanggal_PM_Terakhir${i}">Tanggal PM Terakhir</label>
                        <input type="text" id="Tanggal_PM_Terakhir${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Tanggal_Penghapusan${i}">Tanggal Penghapusan</label>
                        <input type="text" id="Tanggal_Penghapusan${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-2">
                        <label for="User_Sebelumnya${i}">User Sebelumnya</label>
                        <input type="text" id="User_Sebelumnya${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Link_QR${i}">Link Detail QR</label>
                        <input type="text" id="Link_QR${i}" data-index="${i}" class="form-control" placeholder="https://..." />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="QR_Aset${i}">QR Aset</label>
                        <input type="text" id="QR_Aset${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Last_Reminder_Sent${i}">Last Reminder Sent</label>
                        <input type="text" id="Last_Reminder_Sent${i}" data-index="${i}" class="form-control" placeholder="ex: 2026-10-21 08:00:00" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Tanggal_Jadwal_PM${i}">Jadwal Pemeliharaan / Next Maintenance</label>
                        <input type="text" id="Tanggal_Jadwal_PM${i}" data-index="${i}" class="form-control bg-white" readonly />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="KM_Jadwal_PM${i}">KM Jadwal PM</label>
                        <input type="text" id="KM_Jadwal_PM${i}" data-index="${i}" class="form-control" placeholder="ex: 40000" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Jadwal_PM${i}">Jadwal PM (bebas)</label>
                        <input type="text" id="Jadwal_PM${i}" data-index="${i}" class="form-control" placeholder="ex: 6 BULAN" />
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Repair_Vendor${i}">Repair Vendor</label>
                        <input type="text" id="Repair_Vendor${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="User_Responsible${i}">PIC / User Responsible</label>
                        <input type="text" id="User_Responsible${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="User_Responsible_NIK${i}">NIK PIC</label>
                        <input type="text" id="User_Responsible_NIK${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Photo${i}">Foto Aset (nama file / URL)</label>
                        <input type="text" id="Photo${i}" data-index="${i}" class="form-control" />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Riwayat_Aset${i}">Riwayat Aset</label>
                        <textarea id="Riwayat_Aset${i}" data-index="${i}" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="Dokumentasi${i}">Dokumentasi Terkait</label>
                        <textarea id="Dokumentasi${i}" data-index="${i}" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="Note${i}">Note / Catatan Tambahan</label>
                        <textarea id="Note${i}" data-index="${i}" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>`;
    }

    $('#asset-items-container').html(html)
}
</script>