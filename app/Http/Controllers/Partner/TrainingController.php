<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterMaterial;
use App\MasterSatisfication;
use App\MasterTraining;
use App\Training;
use App\TrainingApplication;
use App\TrainingAttendance;
use App\TrainingMaterial;
use App\TrainingSatisfication;
use App\TrainingSatisficationDtl;
use App\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrainingController extends Controller
{
    public function indexTraining()
    {
        return view('partner.training.training');
    }

    public function indexTrainingXml()
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

        $get = TrainingApplication::orderBy('created_date', 'desc');
        if (!auth()->user()->Training_Approval) {
            $get->where('participant', auth()->user()->username);
            $get = $get->get();
            
            foreach ($get as $key => $Fetch) {
                $date = date('d F Y', strtotime($Fetch->requested_date_from));
                if ($Fetch->requested_date_day > 1) {
                    $date .= " - " . date('d F Y', strtotime($Fetch->requested_date_to));
                }
                $name = $usname[$Fetch->created_by];
                echo "<row id=\"$Fetch->id\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->training_name) . "</cell>";
                echo "<cell>" . htmlspecialchars($date) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->requested_fee) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->note) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->status) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->created_date) . "</cell>";
                echo "<cell>" . htmlspecialchars($name) . "</cell>";
                echo "</row>";
    
                $nomor++;
            }
        }else {
            $get = $get->get();
            
            foreach ($get as $key => $Fetch) {
                $date = date('d F Y', strtotime($Fetch->requested_date_from));
                if ($Fetch->requested_date_day > 1) {
                    $date .= " - " . date('d F Y', strtotime($Fetch->requested_date_to));
                }
                $name = $usname[$Fetch->created_by];
                echo "<row id=\"$Fetch->id\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->training_name) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->participant_name) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->participant_position) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->participant_department) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->participant_email) . "</cell>";
                echo "<cell>" . htmlspecialchars($date) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->requested_fee) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->note) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->status) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->created_date) . "</cell>";
                echo "<cell>" . htmlspecialchars($name) . "</cell>";
                echo "</row>";
    
                $nomor++;
            }

        }

        echo "</rows>";
    }

    public function detailTraining(Request $request)
    {
        $training = TrainingApplication::where("id", $request->Task)->first();
        if ($training->training_id) {
            $training->training = Training::where("id", $training->training_id)->first();
        }
        $trainings = MasterTraining::where('status', 'OPEN')->where(function ($q) {
            $q->where('target', strtolower(auth()->user()->Priv_ID))->orWhere('target', 'general');
        })->get();
        return view('partner.training.training-detail', compact('training', 'trainings'));
    }

    public function applicationTraining()
    {
        $trainings = MasterTraining::where('status', 'OPEN')->where(function ($q) {
            $q->where('target', strtolower(auth()->user()->Priv_ID))->orWhere('target', 'general');
        })->get();

        return view('partner.training.application', compact('trainings'));
    }

    public function applicationTrainingSubmit(Request $request)
    {
        try {
            DB::beginTransaction();

            $train = MasterTraining::where("training_no", $request->training_no)->get();
            
            $dateFrom = new DateTime($request->requested_date_from);
            $dateTo = new DateTime($request->requested_date_to);
            $interval = $dateFrom->diff($dateTo);
            $dayDiff = $interval->days + 1;
            
            $timeDay = "";

            if ($request->requested_time_from == null && $request->requested_time_to == null) {
                $timeDay = 'allday';
            }
            $r = $request->all();
    
            if ($r['training_why'] == null) {
                $r['training_why'] = '';
            }
            if ($r['training_how'] == null) {
                $r['training_how'] = '';
            }
            if ($r['note'] == null) {
                $r['note'] = '';
            }
            if ($r['requested_fee'] == null) {
                $r['requested_fee'] = 0;
            }
            $r['training_name'] = $train->training_name;
            $r['requested_date_day'] = $dayDiff;
            $r['requested_time_day'] = $timeDay;
            $r['status'] = "REQUESTED";
            $r['created_by'] = auth()->user()->username;
            $r['created_date'] = now();
    
            TrainingApplication::create($r);
            
            DB::commit();
            
            // return response()->json([
            //     'status'=>'success',
            //     'message'=>'Pengajuan berhasil disimpan'
            // ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json([
                'status'=>'error',
                'message'=>'Pengajuan gagal disimpan'
            ]);
        }
    }

    public function approvalTraining()
    {
        if (auth()->user()->Training_Approval) {
            $trainings = MasterTraining::get();
            return view('partner.training.approval', compact('trainings'));
        }else {
            abort(404);
        }
    }

    public function approvalTrainingXml(Request $request)
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

        $get = TrainingApplication::where('status','REQUESTED')->where('training_id', null)->where('training_no', $request->training)->orderBy('created_date', 'desc');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $date = date('d F Y', strtotime($Fetch->requested_date_from));
            if ($Fetch->requested_date_day > 1) {
                $date .= " - " . date('d F Y', strtotime($Fetch->requested_date_to));
            }
            $name = $usname[$Fetch->created_by];
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(0) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->training_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_position) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_department) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_email) . "</cell>";
            echo "<cell>" . htmlspecialchars($date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->requested_fee) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->note) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->status) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->created_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function approvalTrainingDetail(Request $request)
    {
        $master = MasterTraining::where("training_no", $request->training_no)->first();
        $masterMaterial = MasterMaterial::where("material_status", 'OPEN')->where('material_category', 'Training')->get();
        return view('partner.training.approval-detail', compact('master', 'masterMaterial'));
    }

    public function getSpeaker(Request $request)
    {
        $getMaterial = MasterMaterial::where("material_no", $request->value)->first();
        if ($getMaterial->material_speaker != null && $getMaterial->material_speaker != '') {
            $exp = explode(',', $getMaterial->material_speaker);
            $getUsers = User::whereIn('username', $exp)->get();
        }else {
            $getUsers = User::where('Priv_ID', '!=', 'MITRA')->get();
        }
        $speakers = [];
        foreach ($getUsers as $key => $value) {
            $speakers[] = [
                'username' => $value->username,
                'Name' => $value->Name
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $speakers
        ]);
    }

    public function approvalTrainingDetailMaterial()
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
            $users[] = $value->username;
        }
        $nomor = 1;

        $get = MasterMaterial::where('material_status', 'OPEN')->where('material_category', 'Training')->get();
        
        foreach ($get as $key => $Fetch) {
            $speakers = $users;
            if ($Fetch->material_speaker != null && $Fetch->material_speaker != '') {
                $speakers = $Fetch->material_speaker;
            }
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(0) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($speakers) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function approvalTrainingDetailSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->except('_token');
            $now = now();

            $material = [];

            for ($i=1; $i <= $r['counter']; $i++) { 
                $material['material'][] = $r['material' . $i] != '' ? $r['material' . $i] : null;
                if ($r['material' . $i] != '') {
                    $material['object'][] = MasterMaterial::where('material_no', $r['material' . $i])->first();
                }
                $material['speaker'][] = $r['speaker' . $i] != '' ? $r['speaker' . $i] : null;
            }

            // $participant = implode(',', $r['training_ids']);
            $participant = TrainingApplication::whereIn('id', $r['training_ids'])->get();

            $training = Training::create([
                'training_no' => $r['training_no'],
                'training_name' => $r['training_name'],
                'training_coverage' => $r['training_coverage'],
                'category' => $r['category'],
                'target' => $r['target'],
                'method' => $r['method'],
                'address' => $r['address'],
                'training_date_from' => $r['training_date_from'],
                'training_date_to' => $r['training_date_to'],
                'training_time_from' => $r['training_time_from'],
                'training_time_to' => $r['training_time_to'],
                'training_fee' => $r['training_fee'],
                'created_by' => auth()->user()->username,
                'created_date' => $now,
            ]);

            $dateFrom = new DateTime($r['training_date_from']);
            $dateTo = new DateTime($r['training_date_to']);

            $interval = $dateFrom->diff($dateTo);

            foreach ($participant as $key => $value) {
                for ($i=0; $i <= $interval->days; $i++) { 
                    TrainingAttendance::create([
                        'training_id' => $training->id,
                        'training_no' => $r['training_no'],
                        'training_date' => date("Y-m-d", strtotime($r['training_date_from'] . " + ".$i." day")),
                        'participant' => $value->participant,
                        'created_by' => auth()->user()->username,
                        'created_date' => $now,
                    ]);
                }
                TrainingApplication::where('id', $value->id)->update([
                    'status' => 'APPROVED',
                    'training_id' => $training->id,
                    'training_date_from' => $r['training_date_from'],
                    'training_date_to' => $r['training_date_to'],
                    'training_time_from' => $r['training_time_from'],
                    'training_time_to' => $r['training_time_to'],
                    'training_fee' => $r['training_fee'],
                ]);
            }

            foreach ($material['material'] as $key => $value) {
                TrainingMaterial::create([
                    'training_id' => $training->id,
                    'training_no' => $r['training_no'],
                    'material_no' => $material['object'][$key]->material_no ?? null,
                    'material_name' => $material['object'][$key]->material_name ?? null,
                    'material_speaker' => $material['speaker'][$key],
                    'created_by' => auth()->user()->username,
                    'created_date' => $now,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            return response()->json([
                'status' => 'error',
                'messages' => $th->getMessage()
            ]);
        }
    }

    public function listTraining()
    {
        return view('partner.training.list');
    }

    public function listTrainingXml(Request $request)
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
            $users[] = $value->username;
        }
        $nomor = 1;

        $get = Training::orderBy('training_date_from', 'DESC');
        if ($request->target != '') {
            $get->where('target', $request->target);
        }
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $date = date('d F Y', strtotime($Fetch->training_date_from));
            if ($Fetch->training_date_from != $Fetch->training_date_to) {
                $date .= " - " . date('d F Y', strtotime($Fetch->training_date_to));
            }
            $warna = "";
            $status = "OPEN";
            if ($Fetch->training_date_from <= date("Y-m-d") && $Fetch->training_date_to >= date("Y-m-d")) {
                $warna = 'style="background-color:#F7C85C"';
                $status = "ON PROGRESS";
            }else if($Fetch->training_date_to < date("Y-m-d")) {
                $warna = 'style="background-color:#7FB77E"';
                $status = "DONE";
            }
            echo "<row id=\"$Fetch->id\">";
            echo "<cell $warna>$nomor</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->training_name) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($date) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->training_fee) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->method) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->address) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($status) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->created_date) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function listTrainingDetail(Request $request)
    {
        $training = Training::where('id', $request->Task)->first();
        return view('partner.training.list-detail', compact('training'));
    }

    public function listTrainingDetailParticipantXml(Request $request)
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
            $users[] = $value->username;
        }
        $nomor = 1;

        $get = TrainingAttendance::where('training_id', $request->training)->orderBy('participant', 'ASC');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $participant = TrainingApplication::where('participant', $Fetch->participant)->where('training_id', $request->training)->where('training_no', $Fetch->training_no)->first();
            if ($participant) {
                echo "<row id=\"$Fetch->id\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->training_date) . "</cell>";
                echo "<cell>" . htmlspecialchars($participant->participant_name) . "</cell>";
                echo "<cell>" . htmlspecialchars($participant->participant_position) . "</cell>";
                echo "<cell>" . htmlspecialchars($participant->participant_department) . "</cell>";
                echo "<cell>" . htmlspecialchars($participant->participant_email) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->attendance ? 'Hadir' : 'Tanpa Keterangan') . "</cell>";
                echo "</row>";
    
                $nomor++;
            }
        }

        echo "</rows>";
    }

    public function listTrainingDetailMaterialXml(Request $request)
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
            $users[] = $value->username;
        }
        $nomor = 1;

        $get = TrainingMaterial::where('training_id', $request->training)->orderBy('id', 'ASC');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_speaker) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function listTrainingAttendance(Request $request)
    {
        //
    }

    public function attendTraining()
    {
        return view('partner.training.attend');
    }

    public function attendTrainingXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $Saya = auth()->user()->username;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $users[] = $value->username;
        }
        $nomor = 1;

        $get = TrainingAttendance::where('training_date', date("Y-m-d"))->where('participant', $Saya)->orderBy('participant', 'ASC');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $participant = TrainingApplication::where('participant', $Fetch->participant)->where('training_id', $Fetch->training_id)->where('training_no', $Fetch->training_no)->first();
            if ($participant) {
                $training = Training::where('id', $Fetch->training_id)->first();
                $warna = "";
                if ($Fetch->attendance) {
                    $warna = "style='background-color:#7FB77E'";
                }
                echo "<row id=\"$Fetch->id\">";
                echo "<cell $warna>$nomor</cell>";
                echo "<cell $warna>" . htmlspecialchars($training->training_name) . "</cell>";
                echo "<cell $warna>" . htmlspecialchars($Fetch->training_date) . "</cell>";
                echo "<cell $warna>" . htmlspecialchars($training->method) . "</cell>";
                echo "<cell $warna>" . htmlspecialchars($training->address) . "</cell>";
                echo "<cell $warna>" . htmlspecialchars($Fetch->attendance ? 'Hadir' : 'Belum melakukan presensi') . "</cell>";
                echo "</row>";
    
                $nomor++;
            }
        }

        echo "</rows>";
    }

    public function attendTrainingPrecense(Request $request)
    {
        $alert = "";
        $cek = TrainingAttendance::where('id', $request->Task)->first();
        if ($cek) {
            if ($cek->attendance) {
                $alert = "Sudah melakukan presensi";
            }else {
                $alert = "Presensi berhasil dilakukan";
                TrainingAttendance::where('id', $request->Task)->update([
                    'attendance' => 1,
                ]);

                $cekJumlahTraining = TrainingAttendance::where('training_id', $cek->training_id)->where('participant', $cek->participant)->count();
                $cekJumlahHadir = TrainingAttendance::where('training_id', $cek->training_id)->where('participant', $cek->participant)->where('attendance', 1)->count();
                if ($cekJumlahTraining == $cekJumlahHadir || $cekJumlahHadir > 0) {
                    TrainingApplication::where('training_id', $cek->training_id)->where('participant', $cek->participant)->update([
                        'attendances' => 1,
                    ]);
                }

            }
        }else {
            $alert = "Gagal melakukan presensi";
        }

        echo "<script>alert('$alert');refreshdata();</script>";
    }

    public function assignmentTraining()
    {
        return view('partner.training.assignment');
    }

    public function assignmentTrainingXml()
    {
        //
    }

    public function assignmentTrainingDetail(Request $request)
    {
        //
    }

    public function assignmentTrainingDetailSubmit(Request $request)
    {
        //
    }

    public function activeListTraining()
    {
        //
    }

    public function activeListTrainingXml()
    {
        //
    }

    public function activeListTrainingDetail()
    {
        //
    }

    public function satisficationTraining()
    {
        return view('partner.training.satisfication');
    }

    public function satisficationTrainingXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $Saya = auth()->user()->username;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $users[] = $value->username;
        }
        $nomor = 1;

        $get = TrainingApplication::where('attendances', 1)->where('participant', $Saya)->orderBy('participant', 'ASC');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $date = date('d F Y', strtotime($Fetch->training_date_from));
            if ($Fetch->training_date_from != $Fetch->training_date_to) {
                $date .= " - " . date('d F Y', strtotime($Fetch->training_date_to));
            }
            $training = Training::where('id', $Fetch->training_id)->first();
            $warna = "";
            if ($Fetch->attendance) {
                $warna = "style='background-color:#7FB77E'";
            }
            echo "<row id=\"$Fetch->id\">";
            echo "<cell $warna>$nomor</cell>";
            echo "<cell $warna>" . htmlspecialchars($training->training_name) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($date) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($training->method) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($training->address) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->attendance ? 'Hadir' : 'Belum melakukan presensi') . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->satisfication_score) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars(!$Fetch->satisfication_score ? 'BELUM' : 'SUDAH') . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function satisficationTrainingNew(Request $request)
    {
        $masterSatisfication = MasterSatisfication::where('target', 'participant')->get();
        $training = TrainingApplication::where('id',$request->Task)->first();
        return view('partner.training.satisfication-new', compact('masterSatisfication','training'));
    }

    public function satisficationTrainingNewSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->except('_token');
            $counter = TrainingSatisfication::where('created_date', 'LIKE', date("Y")."%")->count();
            $task = "NPK/GEU/".date("Y")."/".sprintf("%04d", $counter + 1);
            $satisfy = TrainingSatisfication::create([
                'satisfication_no' => $task,
                'assessor' => auth()->user()->username,
                'type' => 'participant',
                'training_no' => $r['training_no'],
                'training_id' => $r['training_id'],
                'created_by' => auth()->user()->username,
                'created_date' => now(),
            ]);

            $masterSatisfication = MasterSatisfication::where('target', 'participant')->get();
            $totalScore = 0;
            foreach ($masterSatisfication as $key => $value) {
                if (isset($r['question'.$value->id])) {
                    TrainingSatisficationDtl::create([
                        'satisfication_no' => $task,
                        'satisfication_id' => $value->id,
                        'satisfication_score' => $r['question'.$value->id],
                        'satisfication_note' => $r['question'.$value->id.'_note'] ?? '',
                        'created_by' => auth()->user()->username,
                        'created_date' => now(),
                    ]);
                }

                $totalScore += $r['question'.$value->id];
            }

            TrainingSatisfication::where('id', $satisfy->id)->update([
                'satisfication_score' => $totalScore,
            ]);

            $cek = TrainingApplication::where('training_no', $r['training_no'])->where('training_id', $r['training_no'])->where('participant', auth()->user()->username)->first();
            if ($cek) {
                TrainingApplication::where('id', $cek->id)->update([
                    'satisfication_score' => $totalScore,
                ]);
            }
            

            DB::commit();

            return response()->json([
                'status' => 'success',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json([
                'status' => 'error',
            ]);
        }
        
    }

    public function satisficationTrainingDetail(Request $request)
    {
        $masterSatisfication = MasterSatisfication::where('target', 'participant')->get();
        $training = TrainingApplication::where('id',$request->Task)->first();
        $satisfication = TrainingSatisfication::where('training_id', $training->training_id)->where('assessor', $training->participant)->where('training_no', $training->training_no)->first();
        $satisficationDtl = TrainingSatisficationDtl::where('satisfication_no', $satisfication->satisfication_no)->get();
        $satisfyDtl = [];
        foreach ($satisficationDtl as $key => $value) {
            $satisfyDtl[$value->satisfication_id] = [
                'score' => $value->satisfication_score,
                'note' => $value->satisfication_note,
            ];
        }
        return view('partner.training.satisfication-detail', compact('masterSatisfication','training', 'satisfication', 'satisficationDtl', 'satisfyDtl'));
    }

    public function satisficationManagerTraining()
    {
        return view('partner.training.satisfication-manager');
    }

    public function satisficationManagerTrainingXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $Saya = auth()->user()->username;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $users[] = $value->username;
        }
        $nomor = 1;

        $get = Training::where('training_date_to', '<', date("Y-m-d"))->orderBy('training_date_from', 'DESC');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $date = date('d F Y', strtotime($Fetch->training_date_from));
            if ($Fetch->training_date_from != $Fetch->training_date_to) {
                $date .= " - " . date('d F Y', strtotime($Fetch->training_date_to));
            }
            $penilaian = TrainingSatisfication::where('assessor', $Saya)->where('training_no', $Fetch->training_no)->where('training_id', $Fetch->id)->where('type', 'manager')->first();
            $warna = "";
            echo "<row id=\"$Fetch->id\">";
            echo "<cell $warna>$nomor</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->training_name) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($date) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->method) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->address) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($penilaian ? $penilaian->satisfication_score : '') . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($penilaian ? 'SUDAH' : 'BELUM') . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function satisficationManagerTrainingNew(Request $request)
    {
        $masterSatisfication = MasterSatisfication::where('target', 'manager')->get();
        $training = Training::where('id',$request->Task)->first();
        return view('partner.training.satisfication-manager-new', compact('masterSatisfication','training'));
    }

    public function satisficationManagerTrainingNewSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->except('_token');
            $counter = TrainingSatisfication::where('created_date', 'LIKE', date("Y")."%")->count();
            $task = "NPK/GEU/".date("Y")."/".sprintf("%04d", $counter + 1);
            $satisfy = TrainingSatisfication::create([
                'satisfication_no' => $task,
                'assessor' => auth()->user()->username,
                'type' => 'manager',
                'training_no' => $r['training_no'],
                'training_id' => $r['training_id'],
                'created_by' => auth()->user()->username,
                'created_date' => now(),
            ]);

            $masterSatisfication = MasterSatisfication::where('target', 'manager')->get();
            $totalScore = 0;
            foreach ($masterSatisfication as $key => $value) {
                if (isset($r['question'.$value->id])) {
                    TrainingSatisficationDtl::create([
                        'satisfication_no' => $task,
                        'satisfication_id' => $value->id,
                        'satisfication_score' => $r['question'.$value->id],
                        'satisfication_note' => $r['question'.$value->id.'_note'] ?? '',
                        'created_by' => auth()->user()->username,
                        'created_date' => now(),
                    ]);
                }

                $totalScore += $r['question'.$value->id];
            }

            TrainingSatisfication::where('id', $satisfy->id)->update([
                'satisfication_score' => $totalScore,
            ]);            

            DB::commit();

            return response()->json([
                'status' => 'success',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json([
                'status' => 'error',
            ]);
        }
        
    }

    public function satisficationManagerTrainingDetail(Request $request)
    {
        $masterSatisfication = MasterSatisfication::where('target', 'manager')->get();
        $training = Training::where('id',$request->Task)->first();
        $satisfication = TrainingSatisfication::where('training_id', $training->id)->where('type', 'manager')->where('assessor', auth()->user()->username)->where('training_no', $training->training_no)->first();
        $satisficationDtl = TrainingSatisficationDtl::where('satisfication_no', $satisfication->satisfication_no)->get();
        $satisfyDtl = [];
        foreach ($satisficationDtl as $key => $value) {
            $satisfyDtl[$value->satisfication_id] = [
                'score' => $value->satisfication_score,
                'note' => $value->satisfication_note,
            ];
        }
        return view('partner.training.satisfication-manager-detail', compact('masterSatisfication','training', 'satisfication', 'satisficationDtl', 'satisfyDtl'));
    }

    public function satisficationTrainingDetailParticipant()
    {
        //
    }

    public function certificateTraining(Request $request)
    {
        return view('partner.training.certificate');
    }

    public function certificateTrainingXml()
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

        $get = TrainingApplication::where('status','APPROVED')->where('training_id', '!=', null)->where('attendances', 1)->orderBy('created_date', 'desc');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $date = date('d F Y', strtotime($Fetch->requested_date_from));
            if ($Fetch->requested_date_day > 1) {
                $date .= " - " . date('d F Y', strtotime($Fetch->requested_date_to));
            }
            $name = $usname[$Fetch->created_by];
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->training_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_position) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_department) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->participant_email) . "</cell>";
            echo "<cell>" . htmlspecialchars($date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->requested_fee) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->note) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->status) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->created_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }
    
    public function certificateTrainingPrint(Request $request)
    {
        $certificate = TrainingApplication::where("id", $request->Task)->first();
        $training = Training::where('id', $certificate->training_id)->first();
        $signature = DB::table('certificate_signature')->first();
        return view('partner.training.print.certificate', compact('certificate', 'training', 'signature'));
    }

    public function certificateMentorTraining(Request $request)
    {
        return view('partner.training.certificate-mentor');
    }

    public function certificateMentorTrainingXml()
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

        $get = TrainingMaterial::where('training_id', '!=', null)->orderBy('created_date', 'desc');
        $get = $get->get();
        
        foreach ($get as $key => $Fetchx) {
            $Fetch = Training::where('id', $Fetchx->training_id)->first();
            $date = date('d F Y', strtotime($Fetch->training_date_from));
            if ($Fetch->training_date_day > 1) {
                $date .= " - " . date('d F Y', strtotime($Fetch->training_date_to));
            }
            $name = $usname[$Fetch->created_by];
            echo "<row id=\"$Fetchx->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->training_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetchx->material_speaker]) . "</cell>";
            echo "<cell>" . htmlspecialchars($date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->created_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }
    
    public function certificateMentorTrainingPrint(Request $request)
    {
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $certificate = TrainingMaterial::where("id", $request->Task)->first();
        $training = Training::where('id', $certificate->training_id)->first();
        $signature = DB::table('certificate_signature')->first();
        return view('partner.training.print.certificate-mentor', compact('certificate', 'training', 'signature','usname'));
    }

    public function certificateTrainingSignature()
    {
        $sign = DB::table('certificate_signature')->first();
        return view('partner.training.certificate-signature', compact('sign'));
    }

    public function certificateTrainingSignatureSubmit(Request $request)
    {
        $cek = DB::table('certificate_signature')->first();
        if (!$cek) {
            DB::table('certificate_signature')->insert([
                'signature1_name' => $request->signature1_name,
                'signature1_position' => $request->signature1_position,
                'signature2_name' => $request->signature2_name,
                'signature2_position' => $request->signature2_position,
                'signature3_name' => $request->signature3_name,
                'signature3_position' => $request->signature3_position,
                'created_by' => auth()->user()->username,
                'created_date' => now(),
            ]);
        }else {
            DB::table('certificate_signature')->where('id', $cek->id)->update([
                'signature1_name' => $request->signature1_name,
                'signature1_position' => $request->signature1_position,
                'signature2_name' => $request->signature2_name,
                'signature2_position' => $request->signature2_position,
                'signature3_name' => $request->signature3_name,
                'signature3_position' => $request->signature3_position,
                'updated_by' => auth()->user()->username,
                'updated_date' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function libraryTraining()
    {
        return view('partner.training.library');
    }

    public function libraryTrainingXml()
    {
        /*
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
    */
        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;

        $training = TrainingAttendance::where('participant', auth()->user()->username)->where('attendance', 1)->get()->pluck('training_no');
        $material = TrainingMaterial::whereIn('training_no', $training)->get()->pluck('material_no');

        $get = MasterMaterial::where('material_status', "OPEN")->where(function($q) use($material) {
            $q->whereIn('material_no', $material)->orWhereIn('material_category', ['Umum', 'News']);
        })->orderBy('created_date', 'desc')->get();
        
        foreach ($get as $key => $Fetch) {
            $link = $Fetch->material_attachment;
            if ($link == '') {
                $link = $Fetch->material_link;
            }
            $name = $usname[$Fetch->created_by];
            echo "<row id=\"$Fetch->id||$link\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_no) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_view ? $link : '') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_download ? 'Download' : '') . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function reportMitraTraining()
    {
        return view('partner.training.report-mitra');
    }

    public function reportMitraTrainingXml()
    {
        /*
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        */

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $users[] = $value->username;
        }
        $nomor = 1;

        $nilai = [
            0 => 'Belum dinilai',
            1 => 'Sangat Tidak Puas',
            2 => 'Tidak Puas',
            3 => 'Cukup Puas',
            4 => 'Puas',
            5 => 'Sangat Puas',
        ];

        $get = Training::orderBy('training_date_from', 'DESC')->where('target','mitra');
        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            $date = date('d F Y', strtotime($Fetch->training_date_from));
            if ($Fetch->training_date_from != $Fetch->training_date_to) {
                $date .= " - " . date('d F Y', strtotime($Fetch->training_date_to));
            }
            $time = "Full Day";
            if ($Fetch->training_time_from != null) {
                $time = date('H:i', strtotime($Fetch->training_time_from)) . " - " . date('H:i', strtotime($Fetch->training_time_to));
            }
            $warna = "";
            $status = "OPEN";
            if ($Fetch->training_date_from <= date("Y-m-d") && $Fetch->training_date_to >= date("Y-m-d")) {
                $warna = 'style="background-color:#F7C85C"';
                $status = "ON PROGRESS";
            }else if($Fetch->training_date_to < date("Y-m-d")) {
                $warna = 'style="background-color:#7FB77E"';
                $status = "DONE";
            }
            // $participantSatisfication = TrainingSatisfication::where('training_id', $Fetch->id)->where('training_no', $Fetch->training_no)->where('type', 'participant')->first();
            $avgParticipantSatisfication = TrainingSatisficationDtl::whereIn('satisfication_no', function($q) use($Fetch) {
                $q->select('satisfication_no')->from('training_satisfication')->where('training_id', $Fetch->id)->where('training_no', $Fetch->training_no)->where('type', 'participant');
            })->avg('satisfication_score');

            // $managerSatisfication = TrainingSatisfication::where('training_id', $Fetch->id)->where('training_no', $Fetch->training_no)->where('type', 'manager')->first();
            $avgManagerSatisfication = TrainingSatisficationDtl::whereIn('satisfication_no', function($q) use($Fetch) {
                $q->select('satisfication_no')->from('training_satisfication')->where('training_id', $Fetch->id)->where('training_no', $Fetch->training_no)->where('type', 'manager');
            })->avg('satisfication_score');

            $speakers = TrainingMaterial::where('training_id', $Fetch->id)->get()->pluck('material_speaker')->unique()->filter()->values();
            $speakersText = "";
            foreach ($speakers as $kunci => $value) {
                $speakersText  .= htmlspecialchars($usname[$value]) . ($kunci < count($speakers) - 1 ? ", " : "");
            }
            // $speakerText = implode(", ", $speakers);

            echo "<row id=\"$Fetch->id\">";
            echo "<cell $warna>$nomor</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->training_name) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($date) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($time) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($speakersText) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->training_fee) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->method) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->address) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($nilai[round($avgParticipantSatisfication, 0)]) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($nilai[round($avgManagerSatisfication, 0)]) . "</cell>";
            echo "<cell $warna>" . htmlspecialchars($Fetch->created_date) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function mailTraining(Request $request)
    {
        $training = Training::where('id', $request->Task)->first();
        $training->date = date('d F Y', strtotime($training->training_date_from));
        if ($training->training_date_from != $training->training_date_to) {
            $training->date .= " - " . date('d F Y', strtotime($training->training_date_to));
        }
        $training->time = "Full Day";
        if ($training->training_time_from != null) {
            $training->time = date('H:i', strtotime($training->training_time_from)) . " - " . date('H:i', strtotime($training->training_time_to));
        }
        $training->speakers = TrainingMaterial::where('training_id', $training->id)->get()->pluck('material_speaker')->unique()->filter()->values();
        if ($training->method == 'offline') {
            $training->method = $training->address;
        }
        return view('partner.training.mail', compact('training'));
    }
}
