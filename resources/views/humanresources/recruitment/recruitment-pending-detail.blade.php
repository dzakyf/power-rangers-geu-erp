<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PENDING DETAIL</div>
<div id="a_recruitmentTab" style="width:100%; height:1500px;"></div>
<script>

var myTabbar = new dhtmlXTabBar({
	
	
    parent:             "a_recruitmentTab",    // id/object, container for tabbar 
    skin:               "dhx_skyblue",  // string, optional, tabbar skin
    mode:               "top",          // string, optional, top or bottom tabs mode
    align:              "left",         // string, optional, left or right tabs align
    close_button:       true,           // boolean, opt., render Close button on tabs
    content_zone:       true,           // boolean, opt., enable/disable content zone
	arrows_mode:        "auto" ,         // mode of showing tabs arrows (auto, always)
 
    tabs: [ // tabs config
 
        {
            id:      "a1",      // tab id
            text:    "Detail Data",    // tab text
            width:   "100",      // numeric for tab width or null for auto, optional
            index:   1,      // numeric for tab index or null for last position,opt.
            active:  true,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }
		]
		
});
myTabbar.tabs("a1").attachURL("recruitment-pending-detail2?ID=<?php echo $_GET['ID']?>");


</script>
