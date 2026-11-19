<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder sampel Purchase Request -> Purchase Order kategori ASSET
 * agar proses penerimaan barang/aset dari PO pada modul Inventaris Assets
 * dapat langsung diuji dan digunakan.
 */
class AssetProcurementSampleSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $today = date('Y-m-d');

        $samples = [
            [
                'pr_no'       => 'PR/GEU-HO/SAMPLE-ASSET-001',
                'po_no'       => 'PO/GEU-HO/INV/SAMPLE-ASSET-001',
                'supplier_id' => 'SUP-SAMPLE-IT',
                'supplier'    => 'PT Teknologi Nusantara',
                'category'    => 'ASSET',
                'sub_cat'     => 'BARANG',
                'budget'      => 18500000,
                'remark'      => 'Pengadaan Laptop Operasional HCGA (Sample Seeder)',
                'items'       => [
                    [
                        'Product_Code' => 'FA-IT-LPT-001',
                        'Product_Desc' => 'Laptop ThinkPad E14 Gen 5',
                        'Spec'         => 'Intel Core i5, RAM 16GB, SSD 512GB',
                        'Qty'          => 2,
                        'Unit'         => 'UNIT',
                        'Price'        => 9250000,
                    ],
                ],
            ],
            [
                'pr_no'       => 'PR/GEU-HO/SAMPLE-ASSET-002',
                'po_no'       => 'PO/GEU-HO/INV/SAMPLE-ASSET-002',
                'supplier_id' => 'SUP-SAMPLE-AUTO',
                'supplier'    => 'PT Armada Jaya Motor',
                'category'    => 'ASSET',
                'sub_cat'     => 'BARANG',
                'budget'      => 265000000,
                'remark'      => 'Pengadaan Kendaraan Operasional Pool (Sample Seeder)',
                'items'       => [
                    [
                        'Product_Code' => 'FA-VH-MPV-001',
                        'Product_Desc' => 'Toyota Avanza 1.5 G MT',
                        'Spec'         => '1500cc Manual, Warna Silver Metalik',
                        'Qty'          => 1,
                        'Unit'         => 'UNIT',
                        'Price'        => 265000000,
                    ],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $prExists = DB::table('purchase_request')->where('PR_No', $sample['pr_no'])->exists();
            if (!$prExists) {
                DB::table('purchase_request')->insert([
                    'Location'            => 'GEU-HO',
                    'Category'            => $sample['category'],
                    'Sub_Category'        => $sample['sub_cat'],
                    'PR_No'               => $sample['pr_no'],
                    'PR_Date'             => $today,
                    'Department'          => 'HCGA',
                    'Department_Request'  => 'HCGA',
                    'Approval_Department' => 'HCGA',
                    'Status'              => 'APPROVED',
                    'PO_No'               => $sample['po_no'],
                    'PO_Status'           => 'NORMAL',
                    'Processing_Status'   => 'PO CREATED',
                    'Processing_Date'     => $now,
                    'Priority_Status'     => 'NORMAL',
                    'User_Request'        => 'admin',
                    'User_Request_Name'   => 'Administrator',
                    'Estimated_Budget'    => $sample['budget'],
                    'Supplier_ID'         => $sample['supplier_id'],
                    'Supplier_Name'       => $sample['supplier'],
                    'Remark'              => $sample['remark'],
                    'Created_By'          => 'seeder',
                    'Created_Date'        => $now,
                ]);

                foreach ($sample['items'] as $item) {
                    DB::table('purchase_request_dtl')->insert([
                        'PR_No'             => $sample['pr_no'],
                        'Purchase_Category' => $sample['sub_cat'],
                        'Purchase_Type'     => $sample['category'],
                        'Category'          => $sample['category'],
                        'Product_Code'      => $item['Product_Code'],
                        'Product_Desc'      => $item['Product_Desc'],
                        'Specification'     => $item['Spec'],
                        'Qty'               => $item['Qty'],
                        'Default_Price'     => $item['Price'],
                        'Prices'            => $item['Price'],
                        'Unit'              => $item['Unit'],
                        'Location'          => 'GEU-HO',
                        'Remark'            => $sample['remark'],
                    ]);
                }
            }

            $poExists = DB::table('purchase_order')->where('PO_No', $sample['po_no'])->exists();
            if (!$poExists) {
                DB::table('purchase_order')->insert([
                    'Location'              => 'GEU-HO',
                    'Process_By'            => 'seeder',
                    'Purchase_Type'         => 'DIRECT',
                    'Purchase_Category'     => $sample['category'],
                    'Purchase_Sub_Category' => $sample['sub_cat'],
                    'PO_No'                 => $sample['po_no'],
                    'PO_Date'               => $now,
                    'PO_Method'             => 'POSTPAID',
                    'Supplier'              => $sample['supplier_id'],
                    'Subtotal'              => $sample['budget'],
                    'PPN'                   => 0,
                    'Total'                 => $sample['budget'],
                    'Status'                => 'APPROVED',
                    'Status_Receive'        => 'WAITING',
                    'Remark'                => $sample['remark'],
                    'Date_Trx'              => $now,
                    'User_Trx'              => 'seeder',
                    'PR_No'                 => $sample['pr_no'],
                    'Currency'              => 'RP',
                ]);

                foreach ($sample['items'] as $item) {
                    $total = $item['Qty'] * $item['Price'];
                    DB::table('purchase_order_dtl')->insert([
                        'Location'      => 'GEU-HO',
                        'PO_No'         => $sample['po_no'],
                        'Category'      => $sample['category'],
                        'Sub_Category'  => $sample['sub_cat'],
                        'Purchase_Type' => 'DIRECT',
                        'Product_Code'  => $item['Product_Code'],
                        'Barcode'       => $item['Product_Code'],
                        'Product_Desc'  => $item['Product_Desc'],
                        'Product_Type'  => $item['Product_Code'],
                        'Qty'           => $item['Qty'],
                        'Qty_Receive'   => 0,
                        'Qty_Remain'    => $item['Qty'],
                        'Unit'          => $item['Unit'],
                        'Price'         => $item['Price'],
                        'Subtotal'      => $total,
                        'PPN'           => 0,
                        'Total'         => $total,
                        'Remark'        => $item['Spec'],
                        'PR_No'         => $sample['pr_no'],
                        'Currency'      => 'RP',
                    ]);
                }
            }
        }
    }
}
