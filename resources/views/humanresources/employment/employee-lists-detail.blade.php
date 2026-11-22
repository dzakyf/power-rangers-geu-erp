<?php
$empId = (string) ($_GET['ID'] ?? '');
$empIdJs = json_encode($empId);
?>
<div class="JudulContent" style="color:#699;background-image:url({{ asset('hdr_back.gif') }});">EMPLOYEE DETAIL</div>
<div id="a_EmployeeTab" style="width:100%; height:1500px;"></div>
<script>
var empDetailTaskId = {!! $empIdJs !!};

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
myTabbar.tabs("a1").attachURL("{{ url('employee-lists-detail-data') }}?ID=" + encodeURIComponent(empDetailTaskId));

var tabUrls = {
    "a1": "{{ url('employee-lists-detail-data') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a2": "{{ url('employee-lists-detail-other') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a3": "{{ url('employee-lists-detail-family') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a4": "{{ url('employee-lists-detail-skill') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a5": "{{ url('employee-lists-detail-education') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a6": "{{ url('employee-lists-detail-insurance') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a7": "{{ url('employee-lists-detail-kjk') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a8": "{{ url('employee-lists-detail-document') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a9": "{{ url('employee-lists-detail-history') }}?ID=" + encodeURIComponent(empDetailTaskId),
    "a10": "{{ url('employee-lists-detail-recruitment') }}?ID=" + encodeURIComponent(empDetailTaskId)
};

var loadedTabs = {
    "a1": true
};

myTabbar.attachEvent("onSelect", function(id, lastId){
    if (tabUrls[id] && !loadedTabs[id]) {
        myTabbar.tabs(id).attachURL(tabUrls[id]);
        loadedTabs[id] = true;
    }
    return true;
});
</script>
