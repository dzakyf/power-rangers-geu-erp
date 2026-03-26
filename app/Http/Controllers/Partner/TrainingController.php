<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterMaterial;
use App\MasterTraining;
use App\Training;
use App\TrainingApplication;
use App\TrainingAttendance;
use App\TrainingMaterial;
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
        $masterMaterial = MasterMaterial::where("material_status", 'OPEN')->get();
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

        $get = MasterMaterial::where('material_status', 'OPEN')->get();
        
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
        //
    }

    public function satisficationTrainingNew(Request $request)
    {
        return view('partner.training.satisfication-new');
    }

    public function satisficationTrainingNewSubmit(Request $request)
    {
        //
    }

    public function satisficationTrainingDetail()
    {
        //
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
        return view('partner.training.print.certificate', compact('certificate', 'training'));
    }
}
