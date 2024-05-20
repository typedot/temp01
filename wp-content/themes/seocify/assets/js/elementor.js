( function ($, elementor) {
    "use strict";

    var Seocify = {

        init: function () {
            
            var widgets = {
                'xs-maps.default': Seocify.Map,
                'xs-testimonial.default': Seocify.Testimonial,
                'xs-pricing-table.default': Seocify.Pricing,
                'xs-case-studies.default': Seocify.Case_Studies,
                'xs-funfact.default': Seocify.Funfact,
                'xs-doodle-parallax.default': Seocify.DoodleParallax,
            };
            $.each(widgets, function (widget, callback) {
                elementor.hooks.addAction('frontend/element_ready/' + widget, callback);
            });
           
        },
        Map: function ($scope) {

            var $container = $scope.find('.seocify-maps'),
                map,
                init,
                pins;
            if (!window.google) {
                return;
            }

            init = $container.data('init');
            pins = $container.data('pins');
            map = new google.maps.Map($container[0], init);

            if (pins) {
                $.each(pins, function (index, pin) {

                    var marker,
                        infowindow,
                        pinData = {
                            position: pin.position,
                            map: map
                        };

                    if ('' !== pin.image) {
                        pinData.icon = pin.image;
                    }

                    marker = new google.maps.Marker(pinData);

                    if ('' !== pin.desc) {
                        infowindow = new google.maps.InfoWindow({
                            content: pin.desc
                        });
                    }

                    marker.addListener('click', function () {
                        infowindow.open(map, marker);
                    });

                    if ('visible' === pin.state && '' !== pin.desc) {
                        infowindow.open(map, marker);
                    }

                });
            }
        },

        Funfact: function ($scope) {

            var $number_percentage = $scope.find('.number-percentage');
            $number_percentage.each(function() {
                $(this).animateNumbers($(this).attr("data-value"), true, parseInt($(this).attr	("data-animation-duration"), 10));
            });
        },

        DoodleParallax: function ($scope) {
            var $doodle_parallax = $scope.find('.elementor-top-section');
            $doodle_parallax.each(function () {
                if ($(this).find('.doodle-parallax').hasClass('doodle-parallax')) {
                    $(this).attr('data-scrollax-parent', 'true');
                } else {
                    $(this).removeAttr('data-scrollax-parent');
                }	
            });
            var a = {
                Android: function() {
                    return navigator.userAgent.match(/Android/i);
                },
                BlackBerry: function() {
                    return navigator.userAgent.match(/BlackBerry/i);
                },
                iOS: function() {
                    return navigator.userAgent.match(/iPhone|iPad|iPod/i);
                },
                Opera: function() {
                    return navigator.userAgent.match(/Opera Mini/i);
                },
                Windows: function() {    
                    return navigator.userAgent.match(/IEMobile/i);
                },
                any: function() {
                    return a.Android() || a.BlackBerry() || a.iOS() || a.Opera() || a.Windows();
                }
            };	
                var trueMobile = a.any();
                if (null == trueMobile) {
                    var b = new Scrollax();
                    b.reload();
                    b.init();
            }
        },

        Case_Studies: function ($scope) {

            var $container = $scope.find('.case-study-slider');
            $container.myOwl({
                items: 3,
                dots: true,
                margin: 30,
                stagePadding: 15,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    1024: {
                        items: 3
                    }
                }
            });
        },


        Testimonial: function ($scope) {

            if ($('.testimonial-slider').length > 0) {
                let sync1 = $scope.find(".testimonial-slider");
                sync1.myOwl({
                    dots: true
                });
            }


            if (($('#sync1') && $('#sync2')).length > 0) {
                let sync1 = $scope.find("#sync1");
                let sync2 = $scope.find("#sync2");
                let slidesPerPage = 3; //globaly define number of elements per page
                let syncedSecondary = true;
            
                sync1.owlCarousel({
                items : 1,
                slideSpeed : 2000,
                nav: false,
                autoplay: true,
                dots: true,
                loop: true,
                responsiveRefreshRate : 200,
                }).on('changed.owl.carousel', syncPosition);
            
                sync2
                .on('initialized.owl.carousel', function () {
                    sync2.find(".owl-item").eq(0).addClass("current");
                })
                .owlCarousel({
                items : slidesPerPage,
                dots: true,
                nav: false,
                autoplay: true,
                smartSpeed: 200,
                slideSpeed : 500,
                slideBy: slidesPerPage, //alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
                responsiveRefreshRate : 100,
                responsive: {
                    0 : {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    1024: {
                        items: slidesPerPage
                    }
                }
                }).on('changed.owl.carousel', syncPosition2);
            
                function syncPosition(el) {
                //if you set loop to false, you have to restore this next line
                //let current = el.item.index;
                
                //if you disable loop you have to comment this block
                let count = el.item.count-1;
                let current = Math.round(el.item.index - (el.item.count/2) - .5);
                
                if(current < 0) {
                    current = count;
                }
                if(current > count) { 
                    current = 0;
                }
                
                //end block
            
                sync2
                    .find(".owl-item")
                    .removeClass("current")
                    .eq(current)
                    .addClass("current");
                let onscreen = sync2.find('.owl-item.active').length - 1;
                let start = sync2.find('.owl-item.active').first().index();
                let end = sync2.find('.owl-item.active').last().index();
                
                if (current > end) {
                    sync2.data('owl.carousel').to(current, 100, true);
                }
                if (current < start) {
                    sync2.data('owl.carousel').to(current - onscreen, 100, true);
                }
                }
                
                function syncPosition2(el) {
                if(syncedSecondary) {
                    let number = el.item.index;
                    sync1.data('owl.carousel').to(number, 100, true);
                }
                }
                
                sync2.on("click", ".owl-item", function(e){
                e.preventDefault();
                let number = $(this).index();
                sync1.data('owl.carousel').to(number, 300, true);
                });
            }
        },

        Pricing: function(e){
            var xs_pricing_table = e.find('.pricing-matrix-slider');
            
            if(!xs_pricing_table){
                return;
            }
                
            xs_pricing_table.on( 'initialized.owl.carousel translated.owl.carousel', function() {
                var $this = $(this);
                $this.find( '.owl-item.last-child' ).each( function() {
                    $(this).removeClass( 'last-child' );
                });
                $(this).find( '.owl-item.active' ).last().addClass( 'last-child' );
            });
            xs_pricing_table.myOwl({
                items: 3,
                mouseDrag: false,
                autoplay: false,
                nav: true,
                navText: ['<i class="icon icon-arrow-left"></i>', '<i class="icon icon-arrow-right"></i>'],
                responsive: {
                    0: {
                        items: 1,
                        mouseDrag: true,
                        loop: true,
                    },
                    768: {
                        items: 2,
                        mouseDrag: true
                    },
                    1024: {
                        items: 3,
                        mouseDrag: false,
                        loop: false
                    }
                }
            });
            equalHeight();
            function equalHeight(){
                
                let pricingImage = e.find('.pricing-image'),
                    pricingFeature = e.find('.pricing-feature-group');
                if ($(window).width() > 991) {
                    pricingImage.css('height', pricingFeature.outerHeight());
                } else {
                    pricingImage.css('height', 100+'%');
                }
            }
        },
    };
    $(window).on('elementor/frontend/init', Seocify.init);
}(jQuery, window.elementorFrontend) ); 