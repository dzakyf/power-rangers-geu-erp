(function($) {
    "use strict";
    jQuery(document).on('ready', function() {
		// Navbar active
        var url = window.location;
        var menuItem = $('.sidemenu .navbar-nav a, .top-menu .navbar-nav a').filter(function() {
            return this.href == url;
        })
        menuItem.addClass('active');
		
        // Side Menu Hide Show JS
        $(".burger-menu").on('click', function(){
            $(".burger-menu").toggleClass("toggle-menu");
            // $(".navbar-brand").toggleClass("navbar-logo");
            $(".sidemenu-area").toggleClass("sidemenu-toggle");
            $(".sidemenu").toggleClass("hide-nav-title");
            $(".main-content").toggleClass("hide-sidemenu");
        }); 

        // Burger menu click show toggle x class
        $(".burger-menu").on('click', function(){
            $(".burger-menu").toggleClass("x");
        }); 

        // Tooltip JS
        $('[data-toggle="tooltip"]').tooltip()
  
        // Feather Icon Js
        feather.replace()    

        // FAQ Accordion Js
        $('.accordion').find('.accordion-title').on('click', function(){
            // Adds Active Class
            $(this).toggleClass('active');
            // Expand or Collapse This Panel
            $(this).next().slideToggle('fast');
            // Hide The Other Panels
            $('.accordion-content').not($(this).next()).slideUp('fast');
            // Removes Active Class From Other Titles
            $('.accordion-title').not($(this)).removeClass('active');		
        });
      
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
        'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
 
        // Back To Top Button JS
        $('body').append('<div id="toTop"><i class="lni lni-chevron-up"></i></div>');
        $(window).scroll(function () {
            if ($(this).scrollTop() != 0) {
             $('#toTop').fadeIn();
            } else {
                $('#toTop').fadeOut();
            }
        }); 
        $('#toTop').click(function(){
            $("html, body").animate({ scrollTop: 0 }, 650);
            return false;
        });
 
        // Gallery Viewer JS
        var console = window.console || { log: function () {} };
        var $images = $('.gallery-content');
        var $toggles = $('.gallery-toggles');
        var $buttons = $('.gallery-buttons');
        var options = {
            // inline: true,
            url: 'data-original',
            ready: function (e) {
                console.log(e.type);
            },
            show: function (e) {
                console.log(e.type);
            },
            shown: function (e) {
                console.log(e.type);
            },
            hide: function (e) {
                console.log(e.type);
            },
            hidden: function (e) {
                console.log(e.type);
            },
            view: function (e) {
                console.log(e.type);
            },
            viewed: function (e) {
                console.log(e.type);
            }
        };
        function toggleButtons(mode) {
            if (/modal|inline|none/.test(mode)) {
            $buttons
                .find('button[data-enable]')
                .prop('disabled', true)
                .filter('[data-enable*="' + mode + '"]')
                .prop('disabled', false);
            }
        }
        $images.on({
            ready:  function (e) {
                console.log(e.type);
            },
            show:  function (e) {
                console.log(e.type);
            },
            shown:  function (e) {
                console.log(e.type);
            },
            hide:  function (e) {
                console.log(e.type);
            },
            hidden: function (e) {
                console.log(e.type);
            },
            view:  function (e) {
                console.log(e.type);
            },
            viewed: function (e) {
                console.log(e.type);
            }
        }).viewer(options);
        toggleButtons(options.inline ? 'inline' : 'modal');
        $toggles.on('change', 'input', function () {
            var $input = $(this);
            var name = $input.attr('name');
            options[name] = name === 'inline' ? $input.data('value') : $input.prop('checked');
            $images.viewer('destroy').viewer(options);
            toggleButtons(options.inline ? 'inline' : 'modal');
        });
        $buttons.on('click', 'button', function () {
            var data = $(this).data();
            var args = data.arguments || [];
            if (data.method) {
                if (data.target) {
                    $images.viewer(data.method, $(data.target).val());
                } else {
                    $images.viewer(data.method, args[0], args[1]);
                }
                switch (data.method) {
                    case 'scaleX':
                    case 'scaleY':
                    args[0] = -args[0];
                    break;
                    case 'destroy':
                    toggleButtons('none');
                    break;
                }
            }
        });
 
        // Left Sidemenu BG Color Switcher
        var el;
        el = document.getElementById('BGPrimary'); if (el) el.onclick = switchPrimary;
        el = document.getElementById('BGSuccess'); if (el) el.onclick = switchSuccess;
        el = document.getElementById('BGSecondary'); if (el) el.onclick = switchSecondary;
        el = document.getElementById('BGPurple'); if (el) el.onclick = switchPurple;
        el = document.getElementById('BGDanger'); if (el) el.onclick = switchDanger;
        el = document.getElementById('BGWarning'); if (el) el.onclick = switchWarning;
        el = document.getElementById('BGPurpleIndigo'); if (el) el.onclick = switchPurpleIndigo;
        el = document.getElementById('BGPink'); if (el) el.onclick = switchPink;
        el = document.getElementById('BGIndigo'); if (el) el.onclick = switchIndigo;
        el = document.getElementById('BGnNightBlue'); if (el) el.onclick = switchNightBlue;
        el = document.getElementById('BGGray'); if (el) el.onclick = switchGray;
        el = document.getElementById('BGGreen'); if (el) el.onclick = switchGreen;
        el = document.getElementById('BGDeepPurple'); if (el) el.onclick = switchDeepPurple;
        
        function switchPrimary() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-primary'; 
        }
        function switchSuccess() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-success'; 
        }
        function switchSecondary() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-secondary'; 
        }
        function switchPurple() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-purple'; 
        }
        function switchDanger() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-danger'; 
        }
        function switchWarning() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-warning'; 
        }
        function switchPurpleIndigo() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-purple-indigo'; 
        }
        function switchPink() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-pink'; 
        }
        function switchIndigo() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-indigo'; 
        }
        function switchNightBlue() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-nightblue'; 
        }
        function switchGray() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-gray'; 
        }
        function switchGrayBlue() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-gray-blue'; 
        }
        function switchGreen() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-green'; 
        }
        function switchDeepPurple() {
            document.getElementsByTagName('body')[0].className = 'sidemenu-bg-deep-purple'; 
        }

        // Folded Menu
        $(".folded-menu").on('click', function(){
            $(".main-content").toggleClass("hide-sidemenu");
            $(".sidemenu-area").toggleClass("sidemenu-toggle");
            $(".sidemenu").toggleClass("hide-nav-title");
        }); 

        // Folded Menu
        $(".card-shadow").on('click', function(){
            $(".card").toggleClass("hide-card-shadow");
            $(".stats-card").toggleClass("hide-card-shadow");
            $(".stats-card-one, .stats-card-two, .stats-card-three").toggleClass("hide-card-shadow");
        }); 

    });

	// Preloader JS
    jQuery(window).on('load', function() {
        $('.preloader').fadeOut()
    })
}(jQuery))





