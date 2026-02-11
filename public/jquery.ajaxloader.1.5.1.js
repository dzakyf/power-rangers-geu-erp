/**
 * Ajaxloader plugin 1.5.1
 *
 * Copyright (c) 2011 Blokhin Yuriy (ds@inbox.ru)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
 /**
 * Ajaxloader plugin using for disable content while loading data and display preload image. 
 */
/**
 * @version 1.5.1
 *
 * @example  
 *              <div id='some-content'> Some content </div>
                <button>Some button</button>
                $(function(){
                    $('button').click(function(){
                        $('#some-content').ajaxloader();
                        $('#some-content').load('some_page.html'); 
                    });
                });
                
                
                $('#some-content').ajaxloader('page.html');
                
 * @param 
 *      mode:
 *              hide, enable - hide preload image, enable content
 *              show/null - show preload image, disable content
 *              disable - disable content without animation
 *      or
 *      'link to page'  - link to page
 * 
 *      options:
 *      'tata-ajax-loader-img' - class of preload image ( .tata-ajax-loader-img { background-image: url('ajax-loader.gif');} )
 *         or
 *      options = {
 *       imageclass : 'tata-ajax-loader-img', - class of preload image
 *       fade: false , - fade enable
 *       fadespeed: 'fast' -fade speed
 *       fadecallback: null  - callback function on fade
 *       load: null  - load with fade inout
 *       loadcallback: null - callback function (load complete)
 *       readycallback: null - callback function (load complete but not complited animation) 
 *       opacity: 80 - opacity front preloadimage
 *       }
 *              
 * @return nothing
 * @cat Plugins/Ajax
 * @author Blokhin Yury/ds@inbox.ru
 */
jQuery.fn.ajaxloader = function(mode, options) {
    
   var obj=this; //"#"+this.attr('id');
   var objloader;
  
   if (typeof(options)!='object'){
        param = options;
        if (typeof(options)=='string' && mode!='load'){ image = options;}else {image = 'tata-ajax-loader-img';};
        options = { 
            imageclass : image,
            opacity: 80,
            fade:   false,
            fadespeed:   'fast',
            load: null,
            fadecallback: null,
            loadcallback: null,
            readycallback: null
        };
        if (mode=='load') options.load = param; 
   }else{
        if (typeof(options.imageclass)=='undefined') options.imageclass = 'tata-ajax-loader-img';
        if (typeof(options.fade)=='undefined') options.fade = false;
        if (typeof(options.fadespeed)=='undefined') options.fadespeed = 'fast';
        if (typeof(options.fadecallback)=='undefined') options.fadecallback = null;
        if (typeof(options.loadcallback)=='undefined') options.loadcallback = null;
        if (typeof(options.readycallback)=='undefined') options.readycallback = null;
        if (typeof(options.load)=='undefined') options.load = null;
        if (typeof(options.opacity)=='undefined') options.opacity = 80;
        image = options.imageclass;
   }
   
    if ( mode != 'load' && mode!='hide' && mode!='enable' && mode!=null && mode!='show' && mode!='disable'){
            options.load  = mode;
            mode = 'load';
   }
   if ( !(jQuery(obj).children('.tata-ajax-loader').length)  ){
         jQuery(obj).prepend("<div class='tata-ajax-loader' style='display: none;position: absolute;opacity: 0."+options.opacity+";background-color: #fff;z-index: 1001;filter:alpha(opacity="+options.opacity+");'><div style='background-position: center;background-repeat: no-repeat;height:100%;width:100%;background-color: transparent;'></div></div>");
         jQuery(obj).children('.tata-ajax-loader').children('div').addClass(options.imageclass);
   }
   objloader = jQuery(obj).children('.tata-ajax-loader');
   
   jQuery(objloader).width(jQuery(obj).width());
   jQuery(objloader).height(jQuery(obj).height());
   if ( mode == null || mode == 'show' || mode == 'disable'){
        if (mode == 'disable'){
            jQuery(objloader).children('div').removeClass(jQuery(objloader).children('div').attr('class'));
        }else{
            if (jQuery(obj).children('.tata-ajax-loader').children('div').attr('class')!=options.imageclass){
                 jQuery(obj).children('.tata-ajax-loader').children('div').removeClass(jQuery(obj).children('.tata-ajax-loader').children('div').attr('class'));
                 jQuery(obj).children('.tata-ajax-loader').children('div').addClass(image);
            
            } 
        }
        if (jQuery(obj).children('.tata-ajax-loader').css('opacity')!="0."+options.opacity){
                  jQuery(obj).children('.tata-ajax-loader').css('opacity',"0."+options.opacity);
                  jQuery(obj).children('.tata-ajax-loader').css('filter',"alpha(opacity="+options.opacity+")");
           }
         if (options.fade)
            if (options.fadecallback)
                jQuery(objloader).fadeIn(options.fadespeed, options.fadecallback);
            else
                jQuery(objloader).fadeIn(options.fadespeed);
         else
               jQuery(objloader).show();
   }else{
        if ( mode == 'hide' || mode == 'enable' ){
            if (options.fade){
                if (options.fadecallback)
                    jQuery(objloader).fadeOut(options.fadespeed, options.fadecallback );
                else
                    jQuery(objloader).fadeOut(options.fadespeed);
            }else{
                jQuery(objloader).hide();
            }
        }
        if ( mode == 'load'){
                jQuery(obj).ajaxloader('show', { 
                        imageclass : options.imageclass,
                        fade:   true,
                        fadespeed:   options.fadespeed,
                        load: options.load,
                        opacity: 90,
                        readycallback:options.readycallback,
                        loadcallback: options.loadcallback,
                        fadecallback: function(){
                            jQuery(obj).load(options.load, function(){
                                jQuery(obj).ajaxloader('show',{opacity: 90});
                                if (options.loadcallback)
                                    jQuery(obj).ajaxloader('hide',{fade: true, fadespeed: options.fadespeed, opacity: 90, fadecallback: options.loadcallback});
                                else
                                    jQuery(obj).ajaxloader('hide',{fade: true, fadespeed: options.fadespeed, opacity: 90});
                                if (options.readycallback) options.readycallback();
                           }); 
                        }
               });
            
        }
   }
};
