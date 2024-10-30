jQuery(function ($) {
    _.extend(mh, {
        notes: {
            vars: {
                loading: false
            },

            init: function () { // console.log($('.mh-note .mh-compose [name=myHomeSubject]'));
                $('.mh-note .mh-compose [name=myHomeSubject]').on('keyup change', function (e) {
                    //console.log('keyup');
                    var $compose = $(this).closest('.mh-compose');
                    var subject = $compose.find('[name=myHomeSubject]').val(); //console.log('subject', $compose)
                    $compose.find('textarea, input[type=file]').prop('disabled', !subject);
                    if (subject) self.showCompose(this);
                    else self.hideCompose(this);
                });
                $('.mh-note .mh-compose textarea').keyup(function (e) {
                    var $compose = $(this).closest('.mh-compose');
                    $compose.find('button[type=submit]').prop('disabled', !($compose.find('textarea').val()));
                });
                $('.mh-note .mh-note-reply-btn a').on('click', function (e) {
                    var $note = $(this).closest('.mh-note'); //console.log('$note', $note);
                    $note.find('> .mh-note-reply-btn').hide();
                    $note.find('> .mh-note-reply').addClass('mh-on');
                    $note.find('textarea, input[type=file]').prop('disabled', false);
                });
                $('.mh-note .mh-compose').on('submit', function (e) {
                    var $compose = $(this);
                    $compose.addClass('is-loading');
                });
            },

            showCompose: function (input) { // console.log('showCompose');
                $(input).closest('.mh-compose').addClass('mh-show');
            },

            hideCompose: function (input) { // console.log('hideCompose');
                if(!input.value)  $(input).closest('.mh-compose').removeClass('mh-show');
            }
        }
    });

    var self = mh.notes;
});
