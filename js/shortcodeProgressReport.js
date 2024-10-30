jQuery(function ($) {
    _.extend(mh, {
        progressReport: {

            data: null,

            init: function () {
                $.get({
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/jobSteps',
                    headers: mh.auth,
                    dataType: 'json',
                    contentType: 'application/json'
                }).done(function (response) {
                    console.log(response);
                    /* Map self.data as follows:
                        { 
                            phaseName: string = 'PreSite',
                            percentageComplete: number = 50, // Percentage of Stages within Phase where Status == Completed
                            stages: { // Sub-Grouped from 'stageId'
                                stageName: string = 'Site Works',
                                percentageComplete: number = 50, // Percentage of Tasks within Stage where Status == Completed
                                tasks: Task[]
                            }[]
                        }[] // Grouped from 'phasecode'
                    */
                    this.data = _.chain(response).groupBy('phasecode').map(function(tasks, phaseCode) { 
                        return { 
                            phaseName: phaseCode,
                            stages: _.chain(tasks).groupBy('stageName').map(function(tasks, stageName) {
                                return {
                                    stageName: stageName,
                                    percentComplete: _.reduce(tasks, function(mem, task) {
                                        return mem + (task.status == 'Completed' ? 1 : 0);
                                    }, 0) / tasks.length * 100,
                                    tasks: tasks
                                }
                            }).value()
                        }
                    }).map(function(phase) {
                        phase.percentComplete = _.reduce(phase.stages, function(mem, stage) {
                            return mem + stage.percentComplete;
                        }, 0) / phase.stages.length;
                        return phase;
                    }).value(); 
                    console.log(this.data);
                });
            }

        }
    });

    var self = mh.progressReport;
});
