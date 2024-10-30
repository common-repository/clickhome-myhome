jQuery(function ($) { // console.log('tenderSelection.js');
    if (!mh.hasOwnProperty('tenders')) mh.tenders = {};
    _.extend(mh.tenders, {
        selections: {

            data: {
                categories: []
            },

            init: function () {
            }

        }
    });

    var self = mh.tenders.selectionsEdit;
});
