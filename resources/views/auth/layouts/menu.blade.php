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

            foreach($permissions as $key=>$value){
                array_push($permissionName,$value->name);
            }
            $rolex=Auth::user()->roles()->get()->pluck('name')[0]
    
            @endphp
    
            @if ($rolex == "Super-Admin")
    
            <div class="navbar-content scroll-div">
                <ul class="nav pcoded-inner-navbar">
                @foreach(App\menu::orderBy('order_menu','asc')->whereNull('parent_id')->get() as $menuItem)
                
    
                @if( $menuItem->parent_id == null )
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
                        @if($subMenuItem->role == $rolex)
                        <li class="pcoded-hasmenu">
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
                                      <a href="#" onclick="loadmodule('{{$sub2Menu->url}}')"> {{ $sub2Menu->name }} </a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                        @endif
                        @if($rolex=="Super-Admin")
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
                        @endif
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

            <div class="navbar-content scroll-div">
                <ul class="nav pcoded-inner-navbar">
                @foreach(App\menu::orderBy('order_menu','asc')->whereNull('parent_id')->where('role','like','%' . $rolex . '%')->get() as $menuItem)
                {{-- @dd($menuItem->children); --}}
                @if( $menuItem->parent_id == null )
                <li class="{{($menuItem->url != null) ? '' : 'nav-item pcoded-hasmenu '}}">
                        <?php if ($menuItem->children->isEmpty()) $urlnX ="loadmodule('".$menuItem->url."')" ; else $urlnX = "#"; ?>	

                    {{-- <a href="{{ $menuItem->children->isEmpty() ? $menuItem->url : "#" }}"x
                        class="{{ $menuItem->children->isEmpty() ? '' : 'nav-link data-toggle=dropdown role=button aria-expanded=false' }}"> --}}
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
                        @if($rolex == "Super-Admin")
                            <li class="pcoded-hasmenu">
                                    <?php if ($subMenuItem->descendants->isEmpty()) $urln ="loadmodule('".$subMenuItem->url."')" ; else $urln = "#"; ?>	
                                    <a href="#" onclick="<?php echo $urln ?>"
                                        class="{{  $subMenuItem->descendants->isEmpty()? '' : 'nav-link' }}">
                                {{-- <a href="{{ $subMenuItem->descendants->isEmpty() ? $subMenuItem->url : "#" }}"
                                    class="{{  $subMenuItem->descendants->isEmpty()? '' : 'nav-link' }}"> --}}
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
                        @endif
                        @php
                         $expSubMenuItem=   explode(',',$subMenuItem->role)
                        @endphp
                        @if(isset($expSubMenuItem))

                            <li class="pcoded-hasmenu">
                                @foreach ($expSubMenuItem as $item)
                                    @if($item==$rolex)
                                    {{-- <a href="{{ $subMenuItem->descendants->isEmpty() ? $subMenuItem->url : "#" }}"
                                            class="{{  $subMenuItem->descendants->isEmpty()? '' : 'nav-link' }}"> --}}
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
                                    @endif
                                @endforeach
                            </li>
                        @endif   
                        {{-- @if($subMenuItem->role = $rolex)
                        @foreach ($subMenuItem->descendants as $sub2Menu)
                        {{$sub2Menu->name}}
                        @endforeach
                        @endif --}}

                        
                        {{-- @if($subMenuItem->role == $rolex)
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
                        @endif --}}
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

        </div>
    </nav>
    
