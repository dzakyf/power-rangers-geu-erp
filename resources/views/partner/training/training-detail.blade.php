<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL APPLICATION</div>
	<div class="card">
		<div class="card-body" id="ContentPages">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_name">Participant</label>
                        <input type="text" name="participant_name" id="participant_name" class="form-control" value="{{ $training->participant_name }}">
                        <input type="hidden" name="participant" id="participant" value="{{ $training->participant }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_position">Position</label>
                        <input type="text" name="participant_position" id="participant_position" class="form-control" value="{{ $training->participant_position }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_department">Department</label>
                        <input type="text" name="participant_department" id="participant_department" class="form-control" value="{{ $training->participant_department }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_email">Email</label>
                        <input type="text" name="participant_email" id="participant_email" class="form-control" value="{{ $training->participant_email }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_no">Nama pelatihan yang dibutuhkan</label>
                        <select name="training_no" id="training_no" class="form-control">
                            <option value="">-- Nama Pelatihan --</option>
                            @foreach ($trainings as $item)
                                <option value="{{ $item->training_no }}" {{ $item->training_no == $training->training_no ? 'selected' : '' }}>{{ $item->training_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_why">Mengapa perlu mengikuti pelatihan ini?</label>
                        <textarea name="training_why" id="training_why" rows="3" class="form-control">{{ $training->training_why }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_how">Bagaimana training ini akan mendukung tugas atau tanggung jawab bawahan...</label>
                        <textarea name="training_how" id="training_how" rows="5" class="form-control">{{ $training->training_how }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt">Tanggal yang Diinginkan untuk Pelatihan... (agar tidak mengganggu operasional)</label>
                        <div class="row">
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_date_from">Dari</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="requested_date_from" name="requested_date_from" value="{{ $training->requested_date_from }}" class="form-control bg-white" readonly>
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_date_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="requested_date_to" name="requested_date_to" value="{{ $training->requested_date_to }}" class="form-control bg-white" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt">Waktu yang Diinginkan untuk Pelatihan... (agar tidak mengganggu operasional)</label>
                        <div class="row">
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_time_from">Dari</label>
                            </div>
                            <div class="col-5">
                                <input type="time" id="requested_time_from" name="requested_time_from" value="{{ $training->requested_time_from }}" class="form-control">
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_time_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="time" id="requested_time_to" name="requested_time_to" value="{{ $training->requested_time_to }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="note">Keterangan tambahan bila ada</label>
                        <textarea name="note" id="note" rows="5" class="form-control">{{ $training->note }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="requested_fee">Biaya</label>
                        <input type="text" id="requested_fee" name="requested_fee" class="form-control" value="{{ $training->requested_fee }}">
                    </div>
                </div>
                {{-- <div class="col-12">
                    <button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
                </div> --}}
            </div>
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	
</script>