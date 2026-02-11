<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EMPLOYEE DETAIL</div>
<div id="a_EmployeeTab" style="width:100%; height:1500px;"></div>
<script>

var myTabbar = new dhtmlXTabBar({
	
	
    parent:             "a_EmployeeTab",    // id/object, container for tabbar 
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
        }, {
            id:      "a2",      // tab id
            text:    "Other Information",    // tab text
            width:   "200",      // numeric for tab width or null for auto, optional
            index:   2,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a3",      // tab id
            text:    "Family & Relation Information",    // tab text
            width:   "300",      // numeric for tab width or null for auto, optional
            index:   3,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a4",      // tab id
            text:    "Skill and Experience",    // tab text
            width:   "200",      // numeric for tab width or null for auto, optional
            index:   4,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        },  {
            id:      "a5",      // tab id
            text:    "Education and Training",    // tab text
            width:   "200",      // numeric for tab width or null for auto, optional
            index:   5,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a6",      // tab id
            text:    "Insurances",    // tab text
            width:   "100",      // numeric for tab width or null for auto, optional
            index:   6,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a7",      // tab id
            text:    "Leave and Overtime/KJK",    // tab text
            width:   "200",      // numeric for tab width or null for auto, optional
            index:   7,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a8",      // tab id
            text:    "Documents",    // tab text
            width:   "120",      // numeric for tab width or null for auto, optional
            index:   8,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a9",      // tab id
            text:    "Employee History",    // tab text
            width:   "220",      // numeric for tab width or null for auto, optional
            index:   9,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a10",      // tab id
            text:    "Recruitment Archive",    // tab text
            width:   "220",      // numeric for tab width or null for auto, optional
            index:   10,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }
		]
		
});
myTabbar.tabs("a1").attachURL("employee-lists-detail-data?ID=<?php echo $_GET['ID']?>");

setTimeout(function(){
	myTabbar.tabs("a2").attachURL("employee-lists-detail-other?ID=<?php echo $_GET['ID']?>");
},1000);
setTimeout(function(){
	myTabbar.tabs("a3").attachURL("employee-lists-detail-family?ID=<?php echo $_GET['ID']?>");
},2000);
setTimeout(function(){
	myTabbar.tabs("a5").attachURL("employee-lists-detail-education?ID=<?php echo $_GET['ID']?>");
},3000);
setTimeout(function(){
	myTabbar.tabs("a4").attachURL("employee-lists-detail-skill?ID=<?php echo $_GET['ID']?>");
},4000);
setTimeout(function(){
	myTabbar.tabs("a6").attachURL("employee-lists-detail-insurance?ID=<?php echo $_GET['ID']?>");
},5000);
setTimeout(function(){
	myTabbar.tabs("a7").attachURL("employee-lists-detail-kjk?ID=<?php echo $_GET['ID']?>");
},6000);
setTimeout(function(){
	myTabbar.tabs("a8").attachURL("employee-lists-detail-document?ID=<?php echo $_GET['ID']?>");
},7000);
setTimeout(function(){
	myTabbar.tabs("a9").attachURL("employee-lists-detail-history?ID=<?php echo $_GET['ID']?>");
},8000);
setTimeout(function(){
	myTabbar.tabs("a10").attachURL("employee-lists-detail-recruitment?ID=<?php echo $_GET['ID']?>");
},9000);

</script>
