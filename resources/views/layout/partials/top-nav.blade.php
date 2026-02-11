<?php

if (!auth()->check()) {
        $way[0] = 'non-login';  
}else {
        if(isset($_COOKIE['topnav'])) {      
        $value = $_COOKIE['topnav'];
                $way=explode(";",$value);
        } else {
             $way[0] = 'mymenu';  
        }
}

?>
        @include("layout.partials.top-nav-$way[0]");
