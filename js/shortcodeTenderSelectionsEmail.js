jQuery(function ($) { // console.log('tenderSelectionEdit.js');
    if (!mh.hasOwnProperty('tenders')) mh.tenders = {};
    _.extend(mh.tenders, {
        selectionsEmail: {

            init: function () {

            },

            confirmModal: {
                $el: $('#mh-tender-selections-email-confirm'),

                open: function () {
                    $.colorbox(_.extend({}, mh.colorbox.options, {
                        href: '#' + this.$el.attr('id'),
                        width: 450,
                        onComplete: function () {
                            //console.log('onComplete');
                            mh.colorbox.options.onComplete();
                        }.bind(this),
                        onClosed: function () {
                            //console.log('onClosed');
                        }.bind(this)
                    }));
                }
            },

            sendReport: function () {
                $(document).one('cbox_closed', function () { // console.log('cbox_closed');
                    self.responseModal.open();
                });
                $.colorbox.close();

                $.post(self.xhr.url, _.extend({}, self.xhr.actions[0], {
                    myHomeTenderId: self.vars.tenderId
                }), function () {
                    console.log('Success!');

                    //window.setTimeout(function () {
                    self.responseModal.$el.find('.mh-waiting').addClass('mh-hide');
                    self.responseModal.$el.find('.mh-success').removeClass('mh-hide');
                    //}, 2000);
                }, "json")
                .fail(function () {
                    //alert("<?php _e('The selection update has failed','myHome'); ?>");
                    self.responseModal.$el.find('.mh-waiting').addClass('mh-hide');
                    self.responseModal.$el.find('.mh-error').removeClass('mh-hide');
                });
            },

            responseModal: {
                $el: $('#mh-tender-selections-email-response'),

                open: function () {
                    $.colorbox(_.extend({}, mh.colorbox.options, {
                        href: '#' + this.$el.attr('id'),
                        width: 450
                    }));
                }
            }
        }
    });

    var self = mh.tenders.selectionsEmail;
});
