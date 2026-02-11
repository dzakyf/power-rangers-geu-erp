<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
$project  = DB::table("project_mgt")->where("Task_ID",$_GET['ID'])->first();
$cek  = DB::table("project_activity")->where("Task_ID",$_GET['ID'])->orderBy("Activity_Date")->get();
$addendum  = DB::table("opportunity_detail_addendum")->where("Task_ID",$project->Source_Reference)->orderBy("PO_Date")->get();
?>
<link rel="stylesheet" type="text/css"  href="{{ asset('css/timeline.min.css') }}" />

<div class="rows">

<main style="margin-top:150px">
    <section class="page-section">
      <div class="container">
        <div class="timeline">
          <div class="timeline__wrap">
            <div class="timeline__items">
			
              <div class="timeline__item">
                <div class="timeline__content" style="background-color:grey">
                  <h2><?php echo date("d M Y",strtotime($project->Project_Date))?></h2>
                  <h3>Rp.<?php echo $project->Estimated_Revenue?></h3>
                  <p><?php echo $project->Project_Name?></p>
                </div>
              </div>
             
			<?php
			 foreach($cek as $data){ ?>
              <div class="timeline__item">
                <div class="timeline__content">
                  <h2><?php echo date("d M Y",strtotime($data->Activity_Date))?></h2>
                  <h3><?php echo $data->Activity_Type?></h3>
                  <p><?php echo $data->Activity_Description?></p>
                </div>
              </div>
              <?php
			 }
			 
			 ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

</div>
  <script>
    timeline(document.querySelectorAll('.timeline'), {
      forceVerticalMode: 700,
      mode: 'horizontal',
      verticalStartPosition: 'left',
      visibleItems: 4
    });
  </script>
