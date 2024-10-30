jQuery(function ($) {
    mh.login = {
        forgotPassword: {
            open: function() {
                $.colorbox(_.extend({}, mh.colorbox.options, {
                    href: '#mh-help-logging-in',
                    width: '450px'
                }));
            },

            submit: function() {
                var submit = {
                    $btn: $(event.target),
                    label: $(event.target).text()
                };
                var $form = $('#mh-help-logging-in form');
                var formData = $form.serializeArray();
                formData = _.object(_.pluck(formData, 'name'), _.pluck(formData, 'value'));

                // Validate
                $('#mh-help-logging-in .error-text').html('').hide();
                var isValid = true;
                _.each(formData, function(val, key) { //console.log(key, val);
                    var $group = $form.find('[name=' + key + '][required]').closest('.form-group');
                    if(!isValid || !$group.length) return false;

                    /*if(key == 'email') {
                        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        isValid = re.test(val);
                    } else*/ if(!val) {
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
                submit.$btn.attr('disabled', true).text('Requesting...');
                $.ajax({
                    type: 'POST',
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/clientRecovery2',
                    // headers: mh.auth,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(formData)
                }).always(function(response) {
                    submit.$btn.attr('disabled', false).text(submit.label);
                    $form.find('.mh-loading').fadeOut();
                }).success(function(response) { // console.log('response', response);
                    $form.hide();
                    $('#mh-help-logging-in .mh-response').show().find('p').text(response.message);
                    submit.$btn.hide();
                    //$.colorbox.close();
                    //toastr['success']('Your interest has been registered');
                    $form[0].reset();
                }).error(function(error, message) { console.log('error', error, message);
                    $('#mh-help-logging-in .error-text').html(function() {
                        try { return error.responseJSON.message }
                        catch(err) { return 'No matching account could be found.' }
                    }()).show();
                });
            }
        }
    }
});