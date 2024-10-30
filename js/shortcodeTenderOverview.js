jQuery(function ($) {
    if (!mh.hasOwnProperty('tenders')) mh.tenders = {};
    _.extend(mh.tenders, {
        overview: {

            init: function () { //console.log('tenderOverview.init()');
            },

            slideshows: {
                main: $('.mh-slideshow-main').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: true,
                    //fade: true,
                    dots: true,
                    asNavFor: '.mh-slideshow-carousel'
                }),

                carousel: $('.mh-slideshow-carousel').slick({
                    // slidesToShow: 7,
                    slidesToScroll: 1,
                    asNavFor: '.mh-slideshow-main',
                    arrows: false,
                    infinite: false,
                    //dots: true,
                    centerMode: false,
                    focusOnSelect: true,
                    variableWidth: true
                }),

                fullscreen: function(slideId) { // console.log('fullscreen');
                    $.colorbox(_.extend({}, mh.colorbox.options, {
                        className: 'theatre-mode',
                        width: '100%',
                        height: '100%',
                        href: '#' + $('#mh-photo-slideshow').attr('id'),
                        //width: 900,
                        onComplete: function () { console.log('onComplete photo', $('#colorbox').outerHeight());
                            $('#mh-photo-slideshow').slick({
                                initialSlide: slideId,
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                arrows: true,
                                //dots: true,
                                fade: true
                            });
                        }.bind(this),
                        onClosed: function () { console.log('onClosed');
                            $('#mh-photo-slideshow').slick('unslick');
                        }.bind(this)
                    })); //.on('afterChange', mh.colorbox.resize());
                }
            }

        }
    });
    var self = mh.tenders.overview;
});