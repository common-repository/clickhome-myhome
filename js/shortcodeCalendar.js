jQuery(function ($) {
    _.extend(mh, {
        calendar: {
            vars: {
                loading: false,

                lastMonth: null,

                lastYear: null
            },

            init: function () {

                for (var row = 0; row < 4; row++)
                    $(".mh-row-calendar-calendar[data-row=" + row + "]").show();

                self.loadEvents();

                $("#buttonMyHomePrevious").click(function () {
                    self.loadEvents(-1);
                });
                $("#buttonMyHomeNext").click(function () {
                    self.loadEvents(1);
                });

                $(".mh-block-calendar-list .mh-calendar-list-event-date a").click(function () {
                    self.loadEvents(null, $(this).data("month"), $(this).data("year"));
                });
            },

            loadEvents: function(increment,month,year) {
                if (self.vars.loading) return;

                self.vars.loading = true;
                $("#divMyHomeLoadingCalendar").css("display","inline-block");

                //var params=<?php //echo json_encode($xhrAttributes['params']); ?>;
                var xhrParams = self.xhr.actions[0];

                if(increment!==undefined&&increment!==null)
                { // increment is null when setting a specific month/year
                    xhrParams.myHomeMonth = ~~self.vars.lastMonth + increment;
                    xhrParams.myHomeYear = ~~self.vars.lastYear;

                    if(xhrParams.myHomeMonth<1)
                    {
                        xhrParams.myHomeMonth=12;
                        xhrParams.myHomeYear--;
                    }
                    else if(xhrParams.myHomeMonth>12)
                    {
                        xhrParams.myHomeMonth=1;
                        xhrParams.myHomeYear++;
                    }
                }
                else if(month!==undefined&&year!==undefined)
                {
                    xhrParams.myHomeMonth=month;
                    xhrParams.myHomeYear=year;
                }

                $.post(self.xhr.url , xhrParams, function(data){
                    self.vars.lastMonth = data.month;
                    self.vars.lastYear = data.year;

                    $("#spanMyHomeMonth").empty().append(data.date);

                    self.loadMonth(data.firstDayOfWeek, data.numDays, data.numDaysPrevious, data.events);

                },"json").always(function(){
                    $("#divMyHomeLoadingCalendar").hide();
                    self.vars.loading = false;
                });
            },

            loadMonth: function (firstDayOfWeek, numDays, numDaysPrevious, events) {
                var calendar=$(".mh-cell-calendar-calendar");

                calendar.removeClass("has-events other-month");
                $(".mh-cell-calendar-calendar .mh-calendar-calendar-events ul").empty();
                $(".mh-cell-calendar-calendar .mh-calendar-calendar-day").empty();

                var firstDayPrevious=numDaysPrevious+2-firstDayOfWeek;
                var previous=firstDayPrevious<=numDaysPrevious;
                var next=false;

                var totalDays=numDays;
                if(previous)
                totalDays+=numDaysPrevious-firstDayPrevious+1;

                $(".mh-row-calendar-calendar[data-row=4]").toggle(totalDays>28);
                $(".mh-row-calendar-calendar[data-row=5]").toggle(totalDays>35);

                var day=1;
                if(previous)
                day=firstDayPrevious;

                calendar.each(function(){ // The iteration will go from the the first to the last in the same order
                $(this).find(".mh-calendar-calendar-day").append(day);

                if(previous||next)
                    $(this).addClass("other-month");
                else if(day in events)
                {
                    $(this).addClass("has-events");

                    var ul=$(this).find(".mh-calendar-calendar-events ul");
                    var dayEvents=events[day];

                    for(var i=0; i<dayEvents.length; i++)
                    {
                    var event=dayEvents[i];
                    //console.log(event);

                    var divs=[$("<div class=\"mh-calendar-calendar-event-name\">"+event.name+"</div>")];
                    if(self.vars.showResource && event.resourceName!=="")
                        divs[1]=$("<div class=\"mh-calendar-calendar-event-resource\">"+event.resourceName+"</div>");
                    else
                        divs[1]=$("");

                    var li=$("<li/>").append(divs[0],divs[1]);
                    $(ul).append(li);
                    }
                }

                day++;
                if(previous&&day>numDaysPrevious)
                {
                    previous=false;
                    day=1;
                }
                else if(!previous&&day>numDays)
                {
                    next=true;
                    day=1;
                }
                });
            }
        }
    });

    var self = mh.calendar;
});
