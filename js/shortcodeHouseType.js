jQuery(function ($) {
    mh.houseType = {

        init: function () { //console.log('houseType.init()');
            //this.expressInterest.open();
            $(window).ready(function() {
                $('.plans-lightbox').colorbox({rel:'plans-lightbox', transition: 'none'});
                $('.facade-lightbox').colorbox({rel:'facade-lightbox', transition: 'none'});
            });
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
                focusOnSelect: true,
                //dots: true,
                centerMode: false,
                variableWidth: true
            }),

            fullscreen: function(slideId) { // console.log('fullscreen');
                $.colorbox(_.extend({}, mh.colorbox.options, {
                    className: 'theatre-mode',
                    width: '100%',
                    height: '100%',
                    href: '#' + $('#mh-photo-slideshow').attr('id'),
                    //width: 900,
                    onComplete: function () { // console.log('onComplete photo', $('#colorbox').outerHeight());
                    //    mh.colorbox.options.onComplete();
                        $('#mh-photo-slideshow').slick({
                            initialSlide: slideId,
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            arrows: true,
                            //dots: true,
                            fade: true
                        });
                    }.bind(this),
                    onClosed: function () { // console.log('onClosed');
                        $('#mh-photo-slideshow').slick('unslick');
                    }.bind(this)
                })); //.on('afterChange', mh.colorbox.resize());
            }
        },

        expressInterest: {
            open: function() {
                $.colorbox(_.extend({}, mh.colorbox.options, {
                    href: '#mh-register-interest'
                }));
            },

            submit: function() {
                var submit = {
                    $btn: $(event.target),
                    label: $(event.target).text()
                };
                var $form = $('#mh-register-interest form');
                var formData = $form.serializeArray();
                formData = _.object(_.pluck(formData, 'name'), _.pluck(formData, 'value'));

                // Validate
                var isValid = true;
                _.each(formData, function(val, key) { //console.log(key, val);
                    var $group = $form.find('[name=' + key + '][required]').closest('.form-group');
                    if(!isValid || !$group.length) return false;

                    if(key == 'email') {
                        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        isValid = re.test(val);
                    } else if(!val) {
                        isValid = false;
                    }

                    if(isValid) {
                        $group.find('.form-control').removeClass('is-invalid');
                        $group.find('.invalid-feedback').hide();
                    } else {
                        $group.find('.form-control').addClass('is-invalid');
                        $group.find('.invalid-feedback').show();
                        return false;
                    }
                });
                if(!isValid) return;
                
                // Submit
                $form.find('.mh-loading').fadeIn();
                submit.$btn.attr('disabled', true).text('Registering...');
                $.ajax({
                    type: 'POST',
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/' + 'job/create',
                    headers: mh.auth,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(formData)
                }).always(function(response) {
                    submit.$btn.attr('disabled', false).text(submit.label);
                    $form.find('.mh-loading').fadeOut();
                }).error(function(error, message) { console.log('error', error, message);
                    //$('#mh-register-interest .error-text').html(error.responseJSON.exception.split(',').join('<br/>'));
                }).success(function(response) { // console.log('response', response);
                    $form.hide();
                    $('#mh-register-interest .mh-response').show().find('p').text(response.message);
                    submit.$btn.hide();
                    //$.colorbox.close();
                    //toastr['success']('Your interest has been registered');
                    $form[0].reset();
                });
            }
        }
    };
    var self = mh.houseType;
});
