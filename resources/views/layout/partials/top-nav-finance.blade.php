<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$Priv = Auth::user()->Priv_ID;
$DOP = Auth::user()->DOP_Class;
$dsess =  Auth::user()->Sub_Menu;

?>
<nav class="navbar navbar-expand fixed-top top-menu">
    <a class="navbar-brand" href="{{ url('/') }}">
        <!-- Large logo -->
        <img src="{{ asset('/assets/images/large-logo.png') }}" alt="Logo" class="large-logo" height="55" align="center">
        <!-- Small logo -->
        <img src="{{ asset('/assets/images/small-logo.png') }}" alt="Small Logo" class="small-logo">
    </a>

    <!-- Burger menu -->
    <div class="burger-menu toggle-menu">
        <span class="top-bar"></span>
        <span class="middle-bar"></span>
        <span class="bottom-bar"></span>
    </div>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Mega Menu -->
		
	<?php
		$submenu = DB::table("menu_lists")->where("Modules","FINANCE")->where("Position",2)->orderBy("Urutan","ASC")->get();
		
		if (count($submenu) > 0 ){
		
			foreach($submenu as $dm){
				
					 
				if (strpos($dsess,$dm->Menu_ID)!== false) { 
				
					echo "
							<ul class=\"left-nav d-none d-md-block navbar-nav\">
								<li class=\"nav-item dropdown mega-menu\">
									<a class=\"nav-link dropdown-toggle\" href=\"$dm->Path_Loc\" role=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
										<div class=\"mega-menu-btn\">
											$dm->Name
											<i data-feather=\"chevron-down\" class=\"icon\"></i>
										</div>
									</a>

									<div class=\"dropdown-menu\">
										<div class=\"container\">
											<div class=\"row\">
												";
										$submenu2 = DB::table("menu_lists")->where("Modules","FINANCE")->where("Parent_Menu",$dm->Menu_ID)->where("Position","3")->orderBy("Urutan","ASC")->get();
		
										if (count($submenu2) > 0 ){
										
											foreach($submenu2 as $sb){
												if (strpos($dsess,$sb->Menu_ID)!== false) { 
													echo"<div class=\"col-lg-3 col-md-6\">
														
															<h5 class=\"title\">$sb->Name</h5>";
															$submenu3 = DB::table("menu_lists")->where("Modules","FINANCE")->where("Parent_Menu",$sb->Menu_ID)->where("Position","4")->orderBy("Urutan","ASC")->get();
				
															if (count($submenu3) > 0 ){
																
																foreach($submenu3 as $sm){
																	
																	if (strpos($dsess,$sm->Menu_ID)!== false) { 

																		echo "<a class=\"dropdown-item\" href=\"$sm->Path_Loc\">$sm->Name</a>";
																	
																	}
															
																	
																}
															}
														
													echo"</div>";
												} //end of main 2
											}
										}
											echo "
											</div>
										</div>
									</div>
								</li> 
							</ul>
						";	
				} //end of main 1	
				
			}
			
		}
		
 ?>		
         @include("layout.partials.rightmenu");

        <!-- Right nav -->
      
    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>