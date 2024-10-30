jQuery(function ($) {
    mh.displayHome = {

        address: null,

        init: function () { console.log('displayHome.init()');

            if (typeof $.fn.slick === "function")
                $("#divMyHomeDisplayImages").slick(
                  {
                      infinite: true,
                      slidesToShow: 1,
                      slidesToScroll: 1
                  });
            else
                $("#divMyHomeDisplayImages").addClass("no-carousel");

            var geocoder;
            var map;

            if (typeof google === "undefined" || typeof google.maps === "undefined") {
                var script = $("<script/>")
                  .attr("type", "text/javascript")
                  .attr("src", "https://maps.googleapis.com/maps/api/js?v=3.exp&callback=mh.displayHome.initMap");

                $("body").append(script);
            }
            else
                google.maps.event.addDomListener(window, "load", self.initMap);
        },

        initMap: function () { console.log('displayHome.initMap()', self.address);
            geocoder = new google.maps.Geocoder();
            map = new google.maps.Map(document.getElementById("divMyHomeDisplayMap"), { zoom: 8 });

            geocoder.geocode({ "address": self.address },
            function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    $("#divMyHomeDisplayMap").show();
                    map.setCenter(results[0].geometry.location);
                    map.setZoom(16);

                    new google.maps.Marker(
                        {
                            map: map,
                            position: results[0].geometry.location
                        });
                }
                //else $("#divMyHomeDisplayMap").hide();
            });
        }

    };
    var self = mh.displayHome;
});
