<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">RECRUITMENT DETAIL</div>
<div id="a_recruitmentTab" style="width:100%; height:1700px;"></div>
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
            id:      "a8",      // tab id
            text:    "Documents",    // tab text
            width:   "120",      // numeric for tab width or null for auto, optional
            index:   8,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }, {
            id:      "a9",      // tab id
            text:    "Progress Recruitment",    // tab text
            width:   "200",      // numeric for tab width or null for auto, optional
            index:   9,      // numeric for tab index or null for last position,opt.
            active:  false,      // boolean, make tab active after adding, optional
            enabled: true,     // boolean, false to disable tab on init
            close:   false       // boolean, render close button on tab, optional
        }
		]
		
});
myTabbar.tabs("a1").attachURL("recruitment-lists-detail-data?ID=<?php echo $_GET['ID']?>");

var tabUrls = {
    "a1": "recruitment-lists-detail-data?ID=<?php echo $_GET['ID']?>",
    "a2": "recruitment-lists-detail-other?ID=<?php echo $_GET['ID']?>",
    "a3": "recruitment-lists-detail-family?ID=<?php echo $_GET['ID']?>",
    "a4": "recruitment-lists-detail-skill?ID=<?php echo $_GET['ID']?>",
    "a5": "recruitment-lists-detail-education?ID=<?php echo $_GET['ID']?>",
    "a6": "recruitment-lists-detail-insurance?ID=<?php echo $_GET['ID']?>",
    "a8": "recruitment-lists-detail-document?ID=<?php echo $_GET['ID']?>",
    "a9": "recruitment-lists-detail-progress?ID=<?php echo $_GET['ID']?>"
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
