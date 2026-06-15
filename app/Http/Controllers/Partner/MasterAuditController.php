<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterAuditCategory;
use App\MasterAuditParameter;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterAuditController extends Controller
{
    public function indexMasterAuditCategory()
    {
        return view('partner.master-audit-category');
    }

    public function indexMasterAuditCategoryXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;

        $get = MasterAuditCategory::orderBy('created_date', 'desc')->get();

        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->category_description) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->parameter_list) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->parameter_score) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->status) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function newMasterAuditCategory()
    {
        return view('partner.master-audit-category-new');
    }

    public function newMasterAuditCategorySubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->all();

            $parameters = $request->input('parameter', []);
            $scores = $request->input('score', []);

            $data = new MasterAuditCategory();
            $data->category = $request->category;
            $data->category_description = $request->category_description;
            $data->parameter_list = implode(",", $parameters);
            $data->parameter_score = implode(",", $scores);
            $data->status = $request->status ?? "NORMAL";
            $data->urutan = MasterAuditCategory::max('urutan') + 1;
            $data->created_by = auth()->user()->username;
            $data->created_date = date('Y-m-d H:i:s');
            $data->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Master Audit Category created successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    public function detailMasterAuditCategory(Request $request)
    {

        $data = MasterAuditCategory::find($request->ID);
        $parameter = explode(",", $data->parameter_list);
        $score = explode(",", $data->parameter_score);
        $count = min(count($parameter), count($score));
        return view('partner.master-audit-category-detail', compact('data', 'parameter', 'score', 'count'));
    }

    public function detailMasterAuditCategorySubmit(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('ID');
            if (!$id) {
                throw new \Exception('ID is required');
            }

            $data = MasterAuditCategory::findOrFail($id);
            $data->category = $request->category;
            $data->category_description = $request->category_description;

            $parameters = $request->input('parameter', []);
            $scores = $request->input('score', []);
            $data->parameter_list = implode(",", $parameters);
            $data->parameter_score = implode(",", $scores);

            $data->status = $request->status ?? $data->status;
            $data->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Master Audit Category updated successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            \Log::error($th);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    public function indexMasterAuditParameter()
    {
        return view('partner.master-audit-parameter');
    }

    public function indexMasterAuditParameterXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;

        $cat = MasterAuditCategory::orderBy('urutan', 'ASC')->get();
        foreach ($cat as $value) {
            $get = MasterAuditParameter::where('category', $value->category)->orderBy('category', 'ASC')->get();

            foreach ($get as $key => $Fetch) {
                echo "<row id=\"$Fetch->id\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->category) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->parameter) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->parameter_minor) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->parameter_major) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->parameter_critical) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->parameter_status) . "</cell>";
                echo "</row>";

                $nomor++;
            }
        }

        echo "</rows>";
    }

    public function newMasterAuditParameter()
    {
        $category = MasterAuditCategory::where('status', 'NORMAL')->orderBy('category', 'asc')->get();
        return view('partner.master-audit-parameter-new', compact('category'));
    }

    public function newMasterAuditParameterSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->all();

            $cat = MasterAuditCategory::where('category', $request->category)->first();
            if (!$cat) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found'
                ]);
            }

            $counter = MasterAuditParameter::where('category', $request->category)->count() + 1;

            $data = new MasterAuditParameter();
            $data->category = $request->category;
            $data->parameter = $request->parameter;
            $data->parameter_id = $request->category . $counter;
            $data->parameter_list = $cat->parameter_list;
            $data->parameter_score = $cat->parameter_score;
            $data->parameter_minor = $request->parameter_minor;
            $data->parameter_major = $request->parameter_major;
            $data->parameter_critical = $request->parameter_critical;
            $data->parameter_status = "NORMAL";
            $data->created_by = auth()->user()->username;
            $data->created_date = date('Y-m-d H:i:s');
            $data->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Master Audit Parameter created successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    public function detailMasterAuditParameter(Request $request)
    {
        $category = MasterAuditCategory::where('status', 'NORMAL')->orderBy('category', 'asc')->get();
        $data = MasterAuditParameter::find($request->ID);
        return view('partner.master-audit-parameter-detail', compact('data', 'category'));
    }

    public function detailMasterAuditParameterSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->all();

            $data = MasterAuditParameter::find($request->id);
            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Master Audit Parameter not found'
                ]);
            }

            $data->category = $request->category;
            $data->parameter = $request->parameter;
            $data->parameter_minor = $request->parameter_minor;
            $data->parameter_major = $request->parameter_major;
            $data->parameter_critical = $request->parameter_critical;
            $data->parameter_status = $request->parameter_status;
            $data->updated_by = auth()->user()->username;
            $data->updated_date = date('Y-m-d H:i:s');
            $data->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Master Audit Parameter updated successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }
}
