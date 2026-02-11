<nav class="pcoded-navbar">
    <div class="navbar-wrapper">
        <div class="navbar-brand header-logo">

            <img alt="Logo MKT" src="{{ asset('logo.png') }}" onclick="document.location.href='./'" style="cursor:pointer;width:200px;" />


            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
        </div>
        @php
         // get logged-in user
        $user = auth()->user();
		$urln = "";
        // get all inherited permissions for that user
        $permissions = $user->getAllPermissions();   
        $permissionName=array();
        // for ($insertPermissionName=0; $insertPermissionName < $permissions.length; $insertPermissionName++) { 
        //     array_push($permissionName,$value);
            
        // }
        foreach($permissions as $key=>$value){
            array_push($permissionName,$value->name);
        }
        $rolex=Auth::user()->roles()->get()->pluck('name')[0]

        // dd($permissionName);
        @endphp
        {{-- @dd(Auth::user()->roles()->get()->pluck('name')[0]); --}}

        @if ($rolex == "Super-Admin")

        <div class="navbar-content scroll-div">
            {{-- @dd(App\Menu::orderBy('order','asc')->get()->children) --}}
            <ul class="nav pcoded-inner-navbar">
                {{-- @role('Super-Admin')
                    <li class="pcoded-hasmenu"><a href="#!" class="">User Management</a>
                        <ul class="pcoded-submenu">
                            <li class=""><a href="#" onclick="loadmodule('{{route('users.index') }}')">User</a></li>
                <li class=""><a href="#"  onclick="loadmodule('{{route('users.roles_permission') }}')">Roles</a></li>
            </ul>
            </li>
            @endrole --}}
            {{-- @dd($result->get()); --}}
            {{-- <li data-username="ERP - WAREHOUSE MANAGEMENT SYSTEM" class="nav-item pcoded-hasmenu active pcoded-trigger"> --}}
            @foreach(App\menu::orderBy('order_menu','asc')->whereNull('parent_id')->get() as $menuItem)

            @if( $menuItem->parent_id == null )
            {{-- <td class="nowrap">
                        <button class="btn btn-{{($order->status=='0') ? 'danger' : 'success'}}">{{($order->status=='0') ? 'Inactive' : 'Active'}}</button>
            </td> --}}
            {{-- <li {{ $menuItem->url ? '' : "class=nav-item pcoded-hasmenu active pcoded-trigger" }}> --}}
            {{-- @dd(App\menu::orderBy('order_menu','asc')->get()->toArray()) --}}
            <li class="{{($menuItem->url != null) ? '' : 'nav-item pcoded-hasmenu '}}">
				<?php if ($menuItem->children->isEmpty()) $urlnX ="loadmodule('".$menuItem->url."')" ; else $urlnX = "#"; ?>	
				<a href="#" onclick="<?php echo $urlnX ?>"
                    class="{{ $menuItem->children->isEmpty() ? '' : 'nav-link data-toggle=dropdown role=button aria-expanded=false' }}">
                    <span class="pcoded-micon">
                        <i class="{{$menuItem->icon}}"></i>
                    </span>
                    <span class="pcoded-mtext">
                        {{ $menuItem->name }}
                    </span>
                </a>
                @endif
                @if( ! $menuItem->children->isEmpty() )
                <ul class="pcoded-submenu">

                    @foreach($menuItem->children as $subMenuItem)
                    <li class="pcoded-hasmenu">
                        {{-- <a href="{{ $subMenuItem->url }}">{{ $subMenuItem->name }}</a> --}}
							<?php if ($subMenuItem->descendants->isEmpty()) $urln ="loadmodule('".$subMenuItem->url."')" ; else $urln = "#"; ?>	
						<a href="#" onclick="<?php echo $urln ?>"
                            class="{{  $subMenuItem->descendants->isEmpty()? '' : 'nav-link' }}">
                            <span class="pcoded-mtext">
                                {{ $subMenuItem->name }}
                            </span>
                        </a>
                        @if(! $subMenuItem->descendants->isEmpty())
                        <ul class="pcoded-submenu">
                            @foreach ($subMenuItem->descendants as $sub2Menu)
                            <li class="pcoded-hasmenu">

                              <a href="#" onclick="loadmodule('{{$sub2Menu->url}}')"> {{ $sub2Menu->name }}</a>
							 
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>

                    @endforeach
                </ul>
                @endif
            </li>
            @if( $menuItem->name =='MASTER DATA')
            <li class="nav-item pcoded-menu-caption">
                <label>DELIVERY MANAGEMENT SYSTEM</label>
            </li>
            @endif
            @endforeach
            </li>
            </ul>
        </div>
        
        @else


        {{-- @role('Marketing') --}}
        <div class="navbar-content scroll-div">
            <ul class="nav pcoded-inner-navbar">
            @foreach(App\menu::orderBy('order_menu','asc')->whereNull('parent_id')->where('role','like','%' . $rolex . '%')->get() as $menuItem)
            @if( $menuItem->parent_id == null )
            <li class="{{($menuItem->url != null) ? '' : 'nav-item pcoded-hasmenu '}}">
                <a href="{{ $menuItem->children->isEmpty() ? $menuItem->url : "#" }}"x
                    class="{{ $menuItem->children->isEmpty() ? '' : 'nav-link data-toggle=dropdown role=button aria-expanded=false' }}">
                    <span class="pcoded-micon">
                        <i class="{{$menuItem->icon}}"></i>
                    </span>
                    <span class="pcoded-mtext">
                        {{ $menuItem->name }}
                    </span>
                </a>
                @endif
                @if( ! $menuItem->children->isEmpty() )
                <ul class="pcoded-submenu">

                    @foreach($menuItem->children as $subMenuItem)
                    <li class="pcoded-hasmenu">
                        <a href="{{ $subMenuItem->descendants->isEmpty() ? $subMenuItem->url : "#" }}"
                            class="{{  $subMenuItem->descendants->isEmpty()? '' : 'nav-link' }}">
                            <span class="pcoded-mtext">
                                {{ $subMenuItem->name }}
                            </span>
                        </a>
                        @if(! $subMenuItem->descendants->isEmpty())
                        <ul class="pcoded-submenu">
                            @foreach ($subMenuItem->descendants as $sub2Menu)
                            <li class="pcoded-hasmenu">
								
								<a href="#" onclick="loadmodule('{{$sub2Menu->url}}')"> {{ $sub2Menu->name }}</a>
							 
                                
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>

                    @endforeach
                </ul>
                @endif
            </li>
            @if( $menuItem->name =='MASTER DATA')
            <li class="nav-item pcoded-menu-caption">
                <label>DELIVERY MANAGEMENT SYSTEM</label>
            </li>
            @endif
            @endforeach
            </li>
            </ul>
        </div>
        
        @endif

        {{-- @dd(App\Menu::orderBy('order','asc')->get()); --}}


        {{-- <div class="navbar-content scroll-div">
            <ul class="nav pcoded-inner-navbar">
                <li data-username="ERP - WAREHOUSE MANAGEMENT SYSTEM" class="nav-item pcoded-hasmenu active pcoded-trigger">
                    <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-home"></i></span><span class="pcoded-mtext">DASHBOARD REPORT</span></a>
                    <ul class="pcoded-submenu">
                        @role('Super-Admin')
                        <li class="pcoded-hasmenu"><a href="#!" class="">User Management</a>
                            <ul class="pcoded-submenu">
                                <li class=""><a href="{{ route('users.index') }}">User</a></li>
						<li class=""><a href="{{ route('users.roles_permission') }}">Roles</a></li>
        </ul>
        </li>
        @endrole
        <li class="pcoded-hasmenu"><a href="#!" class="">Inbound Receiving</a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!">Receiving Report</a></li>
                <li class=""><a href="#!">Summary Report</a></li>
                <li class=""><a href="#!">by Stock Status</a></li>
                <li class=""><a href="#!">By Product</a></li>
                <li class=""><a href="#!">Receipt Report</a>
                <li class=""><a href="#!">Blind Receiving Report</a>
                <li class=""><a href="#!">Inbound Register Report</a>
                <li class=""><a href="#!">Inbound Serial Number Report</a>
            </ul>
        </li>
        </ul>
        </li>
        <li data-username="Inbound Process" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-layers"></i></span><span
                    class="pcoded-mtext">INBOUND</span></a>
            <ul class="pcoded-submenu">
                <li class="pcoded-hasmenu">
                    <a href="#!" class="">Advanced Shipment Notice(ASN)</a>
                    <ul class="pcoded-submenu">
                        <li class=""><a href="#!" class="">ASN by Upload</a></li>
                        <li class=""><a href="#!" class="">Manual Entry</a></li>
                        <li class=""><a href="#!" class="">API Request</a></li>

                    </ul>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="#!" class="">Task Lists</a>
                    <ul class="pcoded-submenu">
                        <li class=""><a href="#!" class="">Data Adjustment</a></li>
                        <li class=""><a href="#!" class="">Verification</a></li>
                        <li class=""><a href="#!" class="">Truck Incoming</a></li>

                    </ul>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="#!" class="">Inbound Process</a>
                    <ul class="pcoded-submenu">
                        <li class=""><a href="#!" class="">Tally Method</a></li>
                        <li class=""><a href="#!" class="">Batch Process</a></li>
                        <li class=""><a href="#!" class="">From Return</a></li>
                        <li class=""><a href="#!" class="">Hold Product</a></li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="#!" class="">Putaway</a>
                    <ul class="pcoded-submenu">
                        <li class=""><a href="#!" class="">Auto Putaway</a></li>
                        <li class=""><a href="#!" class="">by Scan Gun</a></li>
                        <li class=""><a href="#!" class="">Manual</a></li>
                    </ul>
                </li>
            </ul>
        </li>


        <li class="nav-item pcoded-menu-caption">
            <label>DELIVERY MANAGEMENT SYSTEM</label>
        </li>
        <li data-username="Self Pickup" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-file-text"></i></span><span
                    class="pcoded-mtext">SELF PICKUP</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Self Pickup</a></li>

            </ul>
        </li>
        <li data-username="POD Process" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-server"></i></span><span
                    class="pcoded-mtext">POD PROCESS</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">POD PRocess</a></li>
            </ul>
        </li>
        <li data-username="Advance Request" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-server"></i></span><span
                    class="pcoded-mtext">ADVANCE REQUEST</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Advance Request</a></li>
            </ul>
        </li>
        <li data-username="Delivery Process" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-pie-chart"></i></span><span
                    class="pcoded-mtext">DELIVERY PROCESS</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Delivery Process</a></li>
            </ul>
        </li>
        <li data-username="Checkpoint" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-map"></i></span><span
                    class="pcoded-mtext">CHECKPOINT</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Checkpoint</a></li>
            </ul>
        </li>
        <li data-username="Intransit" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-lock"></i></span><span
                    class="pcoded-mtext">INTRANSIT</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Intransit</a></li>
            </ul>
        </li>
        <li data-username="Last Mile" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-sliders"></i></span><span
                    class="pcoded-mtext">LAST MILE</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Last Mile</a></li>
            </ul>
        </li>

        <li data-username="Courier Assignment" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-clipboard"></i></span><span
                    class="pcoded-mtext">COURIER ASSIGNMENT</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">COurir Assignment</a></li>
            </ul>
        </li>
        <li data-username="Customer Receive" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i
                        class="feather icon-check-square"></i></span><span class="pcoded-mtext">CUSTOMER
                    RECEIVE</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Customer Receive</a></li>
            </ul>
        </li>
        <li data-username="Gallery Grid Masonry Advance" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-image"></i></span><span
                    class="pcoded-mtext">RETURN PROCESS</span></a>
            <ul class="pcoded-submenu">
                <li class=""><a href="#!" class="">Return</a></li>
            </ul>
        </li>
        <li class="nav-item pcoded-menu-caption">
            <label>ACCESS AND PRIVILEGE</label>
        </li>
        <li data-username="Editor CK-Editor Tinemce" class="nav-item pcoded-hasmenu">
            <a href="#!" class="nav-link"><span class="pcoded-micon"><i class="feather icon-file-plus"></i></span><span
                    class="pcoded-mtext">Privilege</span></a>
            <ul class="pcoded-submenu">
                <li class="pcoded-hasmenu">
                    <a href="#!" class="">Access Privilege</a>
                    <ul class="pcoded-submenu">
                        <li class=""><a href="editor-classic.html" class="">Menu Privilege</a></li>
                        <li class=""><a href="editor-balloon.html" class="">Approval Privilege</a></li>
                        <li class=""><a href="editor-inline.html" class="">Workflow</a></li>

                    </ul>
                </li>
                <li class=""><a href="editor-tinymce.html" class="">User Management</a></li>
            </ul>
        </li>

        </ul>
    </div>
    --}}
    </div>
</nav>
