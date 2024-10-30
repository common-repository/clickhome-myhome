jQuery(function ($) {
    mh.photos = {

        init: function () { //console.log('photos.init()');
            if (self.facebook.exists) {
                window.fbAsyncInit = function () {
                    FB.init({
                        appId: self.facebook.appId,
                        xfbml: true,
                        version: "v2.10"
                    });
                };

                (function () {
                    var script = document.createElement("script");
                    script.async = true;
                    script.src = document.location.protocol + "//connect.facebook.net/en_US/all.js";

                    document.getElementById("fb-root").appendChild(script);
                }());

                $(document).bind("cbox_complete", function () {
                    var button = $("#buttonMyHomeShare");

                    if (button.length === 0) {
                        button = $("<button/>")
                          .append("Share")
                          .attr("id", "buttonMyHomeShare")
                          .css(
                          {
                              position: "absolute",
                              bottom: "0px",
                              left: "0px"
                          });

                        $("#cboxContent").append(button);

                        button.click(function () {
                            if (window.FB === undefined)
                                return;

                            button.prop("disabled", true);
                            var xhrParams = mh.xhr.actions[0];
                            xhrParams.myHomeDocumentId = $.colorbox.element().data("documentId") || "";

                            $.post(mh.xhr.url, xhrParams, function (data) {
                                openShareDialog(data, self.facebook.page);
                            }, "json")
                              .always(function () {
                                  button.prop("disabled", false);
                              })
                        });
                    }
                });

                function openShareDialog(data, page) {
                    var photoUrl = data.url || "";
                    var pageUrl = data.pageUrl || "";

                    if (!photoUrl || !pageUrl)
                        return;

                    var display;
                    // For mobile devices
                    if ($(window).width() <= 760) {
                        page = true;
                        display = "touch";
                    }
                    else
                        display = page ? "popup" : "iframe";

                    if (!page)
                        FB.getLoginStatus(function (res) {
                            if (res.status === "connected")
                                FB.ui(
                                  {
                                      method: "feed",
                                      name: "Share the photo",
                                      link: pageUrl,
                                      display: "iframe"
                                  },
                                  function (response) {
                                      if (response && response.post_id)
                                          alert("The photo was published successfully.");
                                      else
                                          alert("The photo was not published.");
                                  }
                                );
                        });
                    else {
                        var url = "https://www.facebook.com/dialog/feed";

                        var params =
                          [
                            "app_id=" + self.facebook.appId,
                            "display=" + display,
                            "link=" + encodeURIComponent(pageUrl),
                            "redirect_uri=" + self.url
                          ];

                        url = url + "?" + params.join("&");

                        window.open(url, "_blank");
                    }
                }

            }
        },

        slideshowModal: {
            $el: null, //$('#mh-photo-slideshow'),

            open: function (slideId) { // console.log('slideshowModal.open', slideId);
                var self = this;

                // Draw slideshow (prevent hi-res images being loaded earlier)
                if(!this.$el) {
                    this.$el = $('<div>').attr('id', 'mh-photo-slideshow').addClass('mh-slideshow');
                    _.each(mh.photos.data, function(photo) {
                        this.$el.append($('<div>').append($('<img>').attr('data-src', this.buildUrl(photo.url))));
                        //this.$el.append($('<div>').append($('<div>').css('content', 'url(' + this.buildUrl(photo.url) + ')')));
                    }.bind(this));
                    $('body').append($('<div>').append(this.$el).hide());
                }

                // Open it
                $.colorbox(_.extend({}, mh.colorbox.options, {
                    className: 'theatre-mode',
                    width: '100%',
                    height: '100%',
                    href: '#' + this.$el.attr('id'),
                    //width: 900,
                    onComplete: function () { // console.log('onComplete photo', $('#colorbox').outerHeight());
                        var slider = this.$el.on('init', function() {
                            self.showImage(slideId);
                        }).slick({
                            initialSlide: slideId,
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            arrows: true,
                            //dots: true,
                            fade: true
                        }).on('beforeChange', function(event, slick, currentSlide, nextSlide) {
                            self.showImage(nextSlide);
                        });
                    }.bind(this),
                    onClosed: function () { // console.log('onClosed');
                        this.$el.slick('unslick');
                    }.bind(this)
                }));
            },

            showImage: function(imgId) {
                var self = this;
                // Pre-load both this, & the next 2 images
                _.each([imgId+1, imgId+2, imgId+3], function(imgId) {
                    var $img = self.$el.find('.slick-slide:nth-child(' + imgId + ') img');
                    if($img) var dataSrc = $img.attr('data-src');
                    if(dataSrc) $img.attr('src', dataSrc).removeAttr('data-src');
                });
            },

            buildUrl: function(photoUrl) {
                photoUrl = mh.urls.api + photoUrl + '&cache=true';
                return photoUrl;
            },

            play: function () {
                $slideshow = this.$el.find('.mh-slideshow');
                $.colorbox(_.extend({}, mh.colorbox.options, {
                    className: 'theatre-mode',
                    width: '100%',
                    height: '100%',
                    href: '#' + this.$el.attr('id'),
                    onComplete: function () { // console.log('onComplete photo', $('#colorbox').outerHeight());
                        this.$el.slick({
                            initialSlide: 0,
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            //arrows: true,
                            fade: true,
                            //dots: true,
                            autoplay: true,
                            autoplaySpeed: 5000,
                            //width: '100%',
                            //height: '100%'
                        });
                    }.bind(this),
                    onClosed: function () {
                        console.log('onClosed');
                        this.$el.slick('unslick');
                    }.bind(this)
                })); //.on('afterChange', mh.colorbox.resize());
            }
        },
    };
    var self = mh.photos;
});
