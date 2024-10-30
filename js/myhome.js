// Globals
var mh;

// 
jQuery(function ($) { //console.log('myhome.js');
    mh = {
        // Each shortcode extends the global 'mh' object with its own namespace
        // ie: tenders: {
        //  overview: { init: function() {}}
        //}

        // Vars
        urls: {},
        isTouchEnabled: 'ontouchstart' in window || window.DocumentTouch && document instanceof window.DocumentTouch || navigator.maxTouchPoints > 0 || window.navigator.msMaxTouchPoints > 0,

        // Functions
        init: function () { //console.log('mh.init()');
            // Bind touch events & prevent bubbling
            if (mh.isTouchEnabled) {
                $('body').on('touchstart', function (event) {
                    //mh.events.touchstart = event;
                    mh.events.touchStartPos = { x: window.screenX + window.scrollX, y: window.screenY + window.scrollY };
                });
            }

            // Handle click events on touch devices
            $('[onclick]').each(function () {
                var $el = $(this);
                // This only allowed either click OR tap, which didnt support touchscreen laptops...
                /*if (mh.isTouchEnabled) {
                    $el.attr('data-onclick', $el.attr('onclick')).removeAttr('onclick');
                    $el.on('touchend', function (event) { console.log(event);
                        // Ensure event doesn't bubble/propagate
                        //mh.events.stopPropagation(event);
                        if (event.which != 0) return;

                        // Detect & Cancel if Scroll/Drag
                        var xDiff = mh.events.touchStartPos.x - (window.screenX + window.scrollX);
                        if (xDiff < 0) xDiff = -xDiff;
                        var yDiff = mh.events.touchStartPos.y - (window.screenY + window.scrollY);
                        if (yDiff < 0) yDiff = -yDiff;
                        //console.log(xDiff, yDiff);
                        if (xDiff > 25 || yDiff > 25) {
                            //console.log('scrolled');
                            return;
                        }

                        // If we get here, fire the onclick
                        eval($el.attr('data-onclick'));
                    });
                } else {*/
                    $el.attr('onclick', 'mh.events.stopPropagation(event); ' + $el.attr('onclick'));
                //}
            });

            // Handle smooth scrolling on in-page anchor links
            $('a[href*="#"]:not([href="#"])').click(function () {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        $('html, body').animate({
                            scrollTop: target.offset().top - 150
                        }, 1000);
                        return false;
                    }
                }
            });

            // ColorBox
            $(window).bind('cbox_open', function () {
                $('body').addClass('mh-colorbox-open');
            }).bind('resize', function () { // cbox_complete // console.log('resize');
                mh.colorbox.resize();
            }).bind('cbox_cleanup', function () {
                $('body').removeClass('mh-colorbox-open');
            });

            // Toastr
            toastr.options = {
                //"closeButton": true,
                "progressBar": true,
                "positionClass": "toast-bottom-left",
                "timeOut": "5000",
                "extendedTimeOut": "2500"
            }
        },

        events: {
            getTarget: function () {
                return event.target || event.srcElement;
            },

            stopPropagation: function (event) {
                //console.log('cancel', event.cancelBubble);
                if (event.stopPropagation) event.stopPropagation(); //IE9 & Other Browsers
                else event.cancelBubble = true; //IE8 and Lower
                event.preventDefault();
            }
        },

        colorbox: {
            options: {
                inline: true,
                open: true,
                scrolling: false,
                speed: 0,
                maxWidth: '100%',
                maxHeight: '100%',
               // reposition: true,
                onComplete: function () {
                    //console.log('onComplete real', $('#colorbox').outerHeight());
                }
            },

            // Custom resize function which respects defined max height/widths, because the plugin doesn't
            resize: function resizeDetailsColorbox() {
                mh.colorbox.timeout = window.clearTimeout(mh.colorbox.timeout);
                mh.colorbox.timeout = window.setTimeout(function () {
                    if(!$('body').hasClass('mh-colorbox-open')) return;  // console.log('mh.resizeColorbox');
                    var cBoxOpts = _.clone(mh.colorbox.options);
                    //console.log(cBoxOpts.maxHeight, cBoxOpts.maxWidth);
                    if (cBoxOpts.maxHeight.toString().indexOf('%')>0) cBoxOpts.maxHeight = cBoxOpts.maxHeight.substr(0, cBoxOpts.maxHeight.toString().indexOf('%')) / 100 * window.innerHeight;
                    if (cBoxOpts.maxWidth.toString().indexOf('%')>0) cBoxOpts.maxWidth = cBoxOpts.maxWidth.substr(0, cBoxOpts.maxWidth.toString().indexOf('%')) / 100 * window.innerWidth;
                    //console.log(cBoxOpts.maxHeight, cBoxOpts.maxWidth);

                    //$.colorbox.resize({ height: 0, width: 0 });
                    //window.setTimeout(function () { 
                        //console.log(window.innerHeight > cBoxOpts.maxHeight ? cBoxOpts.maxHeight : cBoxOpts.height || 0,
                        //    window.innerWidth > cBoxOpts.maxWidth ? cBoxOpts.maxWidth : cBoxOpts.width || 0);
                        var $cBox = $('#colorbox');
                        //console.log('$cBox.outerHeight(): ', $cBox.outerHeight(), '$cBox.outerWidth(): ', $cBox.outerWidth())

                        $.colorbox.resize({
                            height: $cBox.outerHeight() >= cBoxOpts.maxHeight ? cBoxOpts.maxHeight : cBoxOpts.height,
                            width: $cBox.outerWidth() >= cBoxOpts.maxWidt ? cBoxOpts.maxWidth : cBoxOpts.width
                        });
                    //});
                }, 500);
            },

            timeout: null
        }

    };

    mh.init();
});


// Helper Prototypes
Number.prototype.formatMoney = function (currency, decimals, d, t) {
    var n = this,
        decimals = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(decimals))),
        j = (j = i.length) > 3 ? j % 3 : 0;
    return currency + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (decimals ? d + Math.abs(n - i).toFixed(decimals).slice(2) : "");
};


get_if_exist = function (obj, path) {
    try { //console.log('get_if_exist', path);
        return _.reduce(path.split('.'), function(memo, path) { //console.log(memo);
            return eval('memo.' + path);
        }, obj)
    }
    catch(e) {
        return undefined
    }
};

