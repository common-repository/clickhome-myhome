jQuery(function ($) {
    _.extend(mh, {
        resetPassword: {
            $form: $('form.mh-reset-password'),

            data: {
                token: null
            },

            isMatch: function () {
                var formData = self.$form.serializeArray().reduce(function(m,o){ m[o.name] = o.value; return m;}, {});
                var isMatch = formData.myHomePassword == formData.myHomePassword2;
                var $password2 = self.$form.find('[name=myHomePassword2]');
                $password2.find('+ i.fa').removeClass('fa-times fa-check').hide();

                if($password2.val().length > 0) {
                    if(isMatch) 
                        $password2.find('+ i.fa').addClass('fa-check').show();
                    else
                        $password2.find('+ i.fa').addClass('fa-times').show();
                }

                return isMatch;
            },

            submit: function () {
                var formData = self.$form.serializeArray().reduce(function(m,o){ m[o.name] = o.value; return m;}, {});

                if(self.isMatch()) {
                    $('.mh-login-loading').show();
                    $.ajax({
                        type: 'POST',
                        url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/client/password/reset',
                        //headers: mh.auth,
                        dataType: 'json',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            key: self.data.token,
                            newPassword: formData.myHomePassword
                        })
                    }).always(function(response) {
                        $('.mh-login-loading').hide();
                    }).success(function(response) { // console.log('response', response);
                        toastr['success']('Password Successfully Changed');

                        var $loginForm = $('form#mh-login');
                        $loginForm.find('[name=myHomeJobNumber]').val(response.jobNumber);
                        $loginForm.find('[name=myHomeUsername]').val(response.username);
                        $loginForm.find('[name=myHomePassword]').val(formData.myHomePassword);
                        $loginForm.submit();
                    });
                }

                return false;
            }
        }
    });

    var self = mh.resetPassword;
});
