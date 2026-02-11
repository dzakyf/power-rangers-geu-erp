<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("project_organization")->where("Task_ID",$_GET['ID'])->get();
	
	


?>
<div id="CPOPR">
    <div class="FormInput_Form_Content">

   		<div class="FormInput_left">
        		
                Add New
        
        </div>
		<div class="FormInput_right">
        	
				<input type="text" id="NIK" size="15" />        	
         
         </div>
         <div class="FormInput_left">
        		
                Name
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Name" size="35" />        	
         
         </div>
		
		   <div class="FormInput_left">
        		
                Position
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" id="Position" size="50" />        	
         
         </div>  
			<div class="FormInput_left" style="height:50px">
        		
                Phone
        
        </div>
		<div class="FormInput_right" style="height:50px">
        
				<input type="text" id="Phone" size="20" />        	
         		<input type="button" value="Add To Org" id="Addnew" onclick="addnews();" class="btn btn-info mt-2 mr-2"  />        	
<Br>
         </div>		 
	</div>
	
	<section class="dhx_sample-container">
			<div class="dhx_sample-container__widget" id="diagram"></div>
		</section>
	
	
	
	
	<div class="FormInput_right" style="height:175px;vertical-align:bottom;">

		<input type="button" value="Update Organization" id="UpdatePO" onclick="UpdatePO();" class="btn btn-info mt-2 mr-2"  />        	

	</div>		

<script>
var orgChartData = [
	{
		id: "1",
		text: "Yogie",
		title: "Project Manager",
		img: "../common/img/avatar-1.png"
	},
	{
		id: "2",
		text: "Irawan",
		title: "Finance SPV",
		img: "../common/img/avatar-2.png"
	},
	{
		id: "3",
		text: "Surya",
		title: "Admin SPV",
		img: "../common/img/avatar-3.png"
	},
	{
		id: "2.1",
		text: "Amira",
		title: "IT SPV",
		img: "../common/img/avatar-4.png"
	},
	{
		id: "3.1",
		text: "Didik",
		title: "Jonathan Lane",
		img: "../common/img/avatar-5.png"
	},
	{ id: "1-2", from: "1", to: "2", type: "line", points: [{ x: 100, y: 100 }] },
	{ id: "1-3", from: "1", to: "3", type: "line" },
	{ id: "2-2.1", from: "2", to: "2.1", type: "line" },
	{ id: "3-3.1", from: "3", to: "3.1", type: "line" },
];


	var diagram = new dhx.Diagram("diagram", { type: "org" });
			diagram.data.parse(orgChartData);

var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["PO_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

 	
</script>
