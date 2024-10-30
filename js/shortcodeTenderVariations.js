jQuery(function ($) {
    if (!mh.hasOwnProperty('tenders')) mh.tenders = {};
    _.extend(mh.tenders, {
        variations: {
            data: {
                tender: null,

                variations: [],

                currentVariation: null
            },

            init: function () {
                //self.colorbox.instances.details.show();
            
                //$packageDetails.html(selectedPackage.name + ', ' + JSON.stringify(selectedPackage));
                //$('.mh-tender-variations .mh-variation a.mh-approve').on(mh.isTouchEnabled ? 'touchend' : 'mouseup', function (e) {
                //    console.log($(this).closest('.mh-variation').data('package'));
                //showVariationDetails($(this).data('variationId')); //.closest('.mh-category-package').data('package'));
                //});
                /*packageDetailEls.packageId.on('change', function () {
                    console.log('package select: ' + packageDetailEls.packageId.data('package-id'));
                    $('#package-' + packageDetailEls.packageId.data('package-id')).prop('checked', packageDetailEls.packageId.prop('checked'));
                });*/

                $(window).resize(self.colorbox.resize);
            },

            colorbox: {
                options: {
                    className: 'mh-colorbox-variation-details',
                    inline: true,
                    open: true,
                    scrolling: false,
                    speed: 0,
                    width: '80%',
                    height: 'auto',
                    maxWidth: '960px',
                    reposition: true
                },
            
                resize: function resizeDetailsColorbox() {
                    self.colorbox.timeout = window.clearTimeout(self.colorbox.timeout);
                    self.colorbox.timeout = window.setTimeout(function () { // console.log('resizeColorbox');
                        var cBoxOpts = self.colorbox.options;
                        $.colorbox.resize({ width: window.innerWidth > parseInt(cBoxOpts.maxWidth) ? cBoxOpts.maxWidth : cBoxOpts.width });
                        //$('.mh-slideshow-main')[0].slick.setPosition();
                        //$.colorbox.resize({ width: window.innerWidth > parseInt(cBoxOpts.maxWidth) ? cBoxOpts.maxWidth : cBoxOpts.width });
                    }, 500);
                },

                timeout: null
            },

            viewSigModal: {
                $el: $('#mh-variation-sig-view'),

                open: function (variationId) {
                    console.log('viewSigModal.open', variationId);

                    self.data.currentVariation = _.findWhere(self.data.variations, { variationId: variationId }); console.log('currentVariation', self.data.currentVariation);
                    
                    // Refresh selection data
                    this.$el.find('[data-title]').text('Signatures for ' + self.data.currentVariation.variationName);

                    // Build Slideshow
                    var $slideshow = this.$el.find('[data-slideshow-images]').empty();
                    if (self.data.currentVariation.signatureDocuments.length) {
                        /*var photoParams = _.findWhere(self.xhr.actions, { myHomeAction: 'clientDocument' }); //console.log('photoParams', photoParams);
                        if (!photoParams) console.error('No params for MyHomeAction \'document\' loaded into js');
                        $.extend(photoParams, {
                            myHomeInline: 1,
                            myHomeThumb: 0,
                            myHomeCache: 0//,
                            //myHomeAuth: 'client'
                        });*/  //console.log($.param(photoParams));
                        _.each(self.data.currentVariation.signatureDocuments, function (el, i) { //console.log(photoParams, $);
                            //$slideshow.append($('<div><img src="' + self.xhr.url.replace('ajax', 'post') + '/?myHomeDocumentId=' + el.key + '&' + $.param(photoParams) + '" /></div>'));
                            $slideshow.append($('<div><img src="' + mh.urls.api + el.url + '" /></div>'));
                        });
                    } else {
                        $slideshow.append($('<div><div class="mh-no-photo"><i class="fa fa-picture-o"></i>No Photo<br/>Available</div></div>'));
                    }

                    // Open it
                    $.colorbox($.extend(self.colorbox.options, {
                        href: '#' + this.$el.attr('id'), //'#mh-variation-details',
                        onComplete: function () {
                            if ($slideshow.hasClass('slick-initialized')) {
                                $slideshow.slick('removeSlide', null, null, true);
                                $slideshow.slick('unslick');
                            }
                            $slideshow.slick({
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                arrows: true,
                                dots: true
                            });
                        }
                    }));
                },
            },

            addSigModal: {
                $el: $('#mh-variation-approve'),

                open: function (variationId) {
                    self.data.currentVariation = _.findWhere(self.data.variations, { variationId: variationId }); console.log('currentVariation', self.data.currentVariation);

                    //console.log(packageDetailEls.title);
                    //$packageDetails.html(selectedPackage.name + ', ' + JSON.stringify(selectedPackage));

                    this.$el.find('[data-name]').text(self.data.currentVariation.variationName);

                    $.colorbox($.extend(self.colorbox.options, {
                        href: '#' + this.$el.attr('id'),
                        onComplete: function () {
                            //self.colorbox.resize();
                            self.addSigModal.sigPad.init();
                        },
                        onCleanup: function() {
                            self.addSigModal.sigPad.api.off(); // Unbind Events
                            self.addSigModal.sigPad.api = null;
                            //delete self.data.currentVariation;
                        }
                    }));
                },

                send: function () {
                    if (self.addSigModal.sigPad.api.isEmpty()) {
                        alert('You must sign before submitting approval.');
                        return;
                    }
                    //console.log('send approval', self.addSigModal.sigPad.api.toDataURL('image/svg+xml'));

                    // Grab signature as base64
                    var base64data = self.addSigModal.sigPad.api.toDataURL(); // IE can't handle svg 'image/svg+xml'); // alert(base64data.length);
                
                    // Find 'variationApprove' action in xhr.actions & extend params
                    var xhrParams = _.extend(_.findWhere(self.xhr.actions, { myHomeAction: 'variationApprove' }), {
                        //action: 'approve',
                        tenderId: self.data.currentVariation.tenderid,
                        variationId: self.data.currentVariation.variationId,
                        title: 'ClientSignature.png',
                        data: base64data.substr(base64data.indexOf(',') + 1)
                    });
                    //console.log(xhrParams.data);

                    self.addSigModal.sigPad.api.off();
                    var sendButton = this.$el.find('a.mh-approve-variation');
                    var sendButtonText = sendButton.text();
                    sendButton.text('Sending...').attr('disabled', true);

                    $.post(self.xhr.url, xhrParams).always(function (response) { //console.log(response);                  
                        if (response.status != 200) {
                            alert(response.exception);
                            return;
                        }

                        //sendButton.text(sendButtonText).attr('disabled', false);
                        self.addSigModal.sigPad.api.on();
                        //console.log('Updated success', response);
                        //alert('Your approval has been received successfully.');
                        //$.colorbox.close();
                        window.location.reload(true);
                    });
                },

                sigPad: {
                    $el: null,

                    api: null,

                    init: function () { console.log('mh.tenders.variations.addSigModal.sigPad.init()');
                        self.addSigModal.sigPad.$el = self.addSigModal.$el.find('canvas');

                        // Adjust canvas coordinate space taking into account pixel ratio,
                        // to make it look crisp on mobile devices.
                        // This also causes canvas to be cleared.
                        window.onresize = self.addSigModal.sigPad.resize;
                        self.addSigModal.sigPad.resize();

                        self.addSigModal.sigPad.api = new SignaturePad(self.addSigModal.sigPad.$el[0], {
                            //backgroundColor: 'rgba(255, 255, 255, 0)',
                            penColor: '#1c408d'
                        });
                    },

                    resize: function () {
                        // When zoomed out to less than 100%, for some very strange reason,
                        // some browsers report devicePixelRatio as less than 1
                        // and only part of the canvas is cleared then.
                        var ratio = Math.max(window.devicePixelRatio || 1, 1);
                        var canvas = self.addSigModal.sigPad.$el[0];
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext("2d").scale(ratio, ratio);
                    }
                }
            },

            rejectModal: {
                $el: $('#mh-variation-reject'),

                open: function (variationId) {
                    self.data.currentVariation = _.findWhere(self.data.variations, { variationId: variationId }); console.log('currentVariation', self.data.currentVariation);

                    //console.log(packageDetailEls.title);
                    //$packageDetails.html(selectedPackage.name + ', ' + JSON.stringify(selectedPackage));

                    this.$el.find('[data-name]').text(self.data.currentVariation.variationName);

                    $.colorbox({
                        inline: true,
                        href: '#' + this.$el.attr('id')
                    });
                },
                
                send: function () {
                    // Find 'variationApprove' action in xhr.actions & extend params
                    var xhrParams = _.extend(_.findWhere(self.xhr.actions, { myHomeAction: 'variationReject' }), {
                        //action: 'reject',
                        tenderId: self.data.currentVariation.tenderid,
                        variationId: self.data.currentVariation.variationId
                    });
                    //console.log(xhrParams.data);

                    var sendButton = this.$el.find('a.mh-reject-variation');
                    var sendButtonText = sendButton.text();
                    sendButton.text('Rejecting...').attr('disabled', true);

                    $.post(self.xhr.url, xhrParams).always(function (response) { //console.log(response);                  
                        if (response.status != 200) {
                            alert(response.exception);
                            return;
                        }
                        //sendButton.text(sendButtonText).attr('disabled', false);
                        window.location.reload(true);
                    });
                }
            },
        }
    });

    var self = mh.tenders.variations;
});