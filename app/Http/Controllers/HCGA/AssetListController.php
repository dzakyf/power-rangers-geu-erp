<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetListController extends Controller
{
    public function getDataFromPO(Request $request)
    {
        if ($request->pono == '') {
            return response()->json([
                'status' => 'empty',
                'message' => 'no PO',
            ]);
        }
        $get = DB::table('purchase_order as po')->where("PO_No", $request->pono)->leftJoin('supplier', 'po.Supplier', '=', 'supplier.Supplier_ID')->select("po.PO_No", 'po.Subtotal', 'supplier.Company_Name')->first();
        $get->Details = DB::table('purchase_order_dtl as prd')->where("PO_No", $request->pono)->leftJoin('product_code as pc', 'prd.Product_Code', '=', 'pc.Product_Code')->select("prd.Category", "prd.Product_Code", "prd.SN", "prd.Product_Desc","prd.Product_Type", "prd.Purchase_Type", "prd.Qty", 'prd.Total', 'pc.Specification', 'pc.Product_Brand', 'pc.Supplies_Type')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data PO',
            'data' => $get,
        ]);
    }

    public function getDataFromWH(Request $request)
    {
        $gudang = DB::table('gudang_list')->where("Gudang_ID", $request->wh)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Data PO',
            'data' => $gudang,
        ]);
    }
}
