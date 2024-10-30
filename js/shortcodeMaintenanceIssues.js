jQuery(function ($) {
    if (!mh.hasOwnProperty('maintenance')) mh.maintenance = {};
    if (!mh.maintenance.hasOwnProperty('issues')) mh.maintenance.issues = {};

    // This needs to be moved within mh.maintenance.issues
    if(typeof($.fn.validate==="function"))
        $(".mh-wrapper-maintenance-issues").validate({
            message:"<?php _e('Please fill in all required fields','myHome'); ?>",
            feedbackClass:"mh-error"
        });

    var thumbnailLink=$(".mh-maintenance-issues-thumbnail-link");
    var image=$("#imgMyHomeMaintenanceIssuesImage");
    var addFile=$(".mh-link-maintenance-issues-add-file");
    var issues = $(".mh-wrapper-maintenance-issues");

    var firstImage=thumbnailLink.first().attr("href");
    if(firstImage!==undefined) {
        image.attr("src",firstImage);
        $("#divMyHomeLoadingMaintenanceIssues").show();
    }

    thumbnailLink.click(function(){
        image.attr("src",$(this).attr("href"));
        $("#divMyHomeLoadingMaintenanceIssues").show();

        return false;
    });

    image.on("load",function(){
        $("#divMyHomeLoadingMaintenanceIssues").hide();
    });

    $(".mh-link-maintenance-issues-delete").click(function(){
        if(!confirm("<?php _e('Are you sure you want to delete this issue?'); ?>"))
            return;

        $(this).parent().parent().parent().remove();
    });

    addFile.each(function(){
        showAddMoreFiles($(this).parent());
    });

    addFile.click(function(){
        var parent=$(this).parent();
        var container=parent.children(".mh-maintenance-issues-files-new");

        if(!showAddMoreFiles(parent))
            return;

        var div=parent.children(".mh-maintenance-issues-file-base")
          .clone()
          .addClass("mh-maintenance-issues-file")
          .removeClass("mh-maintenance-issues-file-base");

        var input=div.find("input");
        input.attr("name",input.data("name"));
        input.removeAttr("data-name");

        container.append(div);

        showAddMoreFiles(parent);
    });

    issues.on("change",".mh-button-maintenance-issues-select-file input[type=file]", function() {
        var filename=$(this).val();

        var parts=filename.match(/\\([^\\]+)$/);

        if(parts!==null)
            filename=parts[1];
        else
        {
            parts=filename.match(/\/([^\/]+)$/);

            if(parts!==null)
                filename=parts[1];
        }

        $(this).parent().parent().children(".mh-file-name")
        .empty()
        .append(filename);
    });

    issues.on("click",".mh-link-maintenance-issues-delete-file", function(){
        var container=$(this).parent().parent().parent();
        $(this).parent().remove();

        showAddMoreFiles(container);
    });

    function showAddMoreFiles(filesContainer){
        var link=filesContainer.find(".mh-link-maintenance-issues-add-file");

        if(filesContainer.find(".mh-maintenance-issues-file").get().length >= mh.maintenance.issues.maxFiles) {
            link.hide();
            return false;
        } else {
            link.show();
            return true;
        }
    }


    ///// Refactor the above into here:
    if (!mh.hasOwnProperty('maintenance')) mh.maintenance = {};
    _.extend(mh.maintenance, {
        issues: {

        }
    });
    var self = mh.maintenance.issues;
});