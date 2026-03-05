<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectionProfitController extends Controller
{
    public function __construct() {}

    public function index()
    {
        return view('projection.index');
    }

    public function create()
    {
        return view('projection.create');
    }

    public function submit()
    {
        throw new Exception("Not Implemented.");
    }

    public function submitExpenditure()
    {
        return response()->json([
            "message" => "success"
        ], 200);
    }

    public function submitProjection()
    {
        return response()->json([
            "message" => "success"
        ], 200);
    }

    public function showProjection() {}

    // ?user_id=&hub_type=&projection_type=&year=
    public function projectionData(Request $r)
    {
        $q = $r->query();
        $userId = $q['user_id'];
        $projectionType = $q['projection_type'];
        $hubType = $q['hub_type'];
        $year = $q['year'];

        $proj = DB::table('projections')->select('*')->where('User_Id', '=', $userId)->where('Projection_Type', '=', $projectionType)->where('Hub_Type', '=', $hubType)->where('Year', '=', $year)->get();

        return response()->json([
            "message" => "success",
            "data" => $proj
        ], 200);
    }

    public function showExpenditure() {}

    // ?user_id=&hub_type=&projection_type=&year=
    public function expenditureData(Request $r)
    {

        $q = $r->query();
        $userId = $q['user_id'];
        $projectionType = $q['projection_type'];
        $hubType = $q['hub_type'];
        $year = $q['year'];

        $exp = DB::table('expenditure')->select('*')->where('User_Id', '=', $userId)->where('Projection_Type', '=', $projectionType)->where('Hub_Type', '=', $hubType)->where('Year', '=', $year)->get();

        return response()->json([
            "message" => "success",
            "data" => $exp
        ], 200);
    }
}
