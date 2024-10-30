<?php
/**
 * The maintenanceReview view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeMaintenanceReviewController))
  die;

/**
 * @var ShortcodeMaintenanceReviewController $this
 * @var string                               $attDailyLimit
 * @var string                               $attExcludeComingDays
 * @var int                                  $jobId
 * @var string                               $appointmentExclusions
 * @var string                               $redirectUrl
 * @var string                               $redirectUrlError
 * @var string                               $paramJobId
 * @var string                               $paramPostId
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('maintenanceReview','POST',$redirectUrl,$redirectUrlError);
$formAttributes['params'][$paramJobId]=$jobId;
// Post ID is used by doPostMaintenance() to retrieve the cached attributes for this post, and then, check if the user must provide a time frame
$formAttributes['params'][$paramPostId]=get_the_ID();

$error=$this->restoreVar('error');

// For each DateTime object, get the date formatted as "YYYY-M-D" (both months and days without leading zeros)
$appointmentExclusions=array_map(function (DateTime $dt){
  return $dt->format('Y-n-j');
},$appointmentExclusions);

?>
<form action="<?php $this->appendFormUrl($formAttributes); ?>" class="mh-wrapper mh-wrapper-maintenance-review" method="POST">
  <?php $this->appendFormParams($formAttributes,2); ?>
  <?php if($error): ?>
    <div class="mh-error mh-error-maintenance-review"><?php echo esc_html($error); ?></div>
  <?php endif; ?>
  <div class="mh-body mh-body-maintenance-review">
    <?php if($attDailyLimit==2): ?>
      <div class="mh-row mh-row-maintenance-review-time-frame">
        <div class="mh-cell mh-cell-maintenance-review-field"><?php _e('Time Frame','myHome'); ?></div>
        <div class="mh-cell mh-cell-maintenance-review-input">
          <label><input name="myHomeTimeFrame" required type="radio" value="m"> <?php _e('Morning','myHome'); ?></label>
          <label><input name="myHomeTimeFrame" required type="radio" value="a"> <?php _e('Afternoon','myHome'); ?>
          </label>
        </div>
      </div>
    <?php endif; ?>
    <div class="mh-row mh-row-maintenance-review-date">
      <div class="mh-cell mh-cell-maintenance-review-field"><?php _e('Date (dd/mm/yyyy)','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-review-input"><input class="datepicker" maxlength="10" name="myHomeDate" type="text" required></div>
    </div>
  </div>
  <div class="mh-footer mh-footer-maintenance-review">
    <div class="mh-row mh-row-maintenance-review-button">
      <div class="mh-cell mh-cell-maintenance-review-button">
        <button class="mh-button mh-button-maintenance-review-submit" type="submit"><?php _ex('Submit',
            'Maintenance Review Form','myHome'); ?></button>
      </div>
    </div>
  </div>
</form>
<script type="text/javascript">
   var AAAappointmentExclusions=<?php echo json_encode($appointmentExclusions); ?>;
   var AAAcomingDaysExcluded=<?php echo (int)$attExcludeComingDays; ?>;

  jQuery(function($){
    if(typeof $.fn.datepicker==="function")
    {
      function dateTimestamp(date){
        var dateReset=new Date(date.getTime());

        dateReset.setHours(0);
        dateReset.setMinutes(0);
        dateReset.setSeconds(0);
        dateReset.setSeconds(0);
        dateReset.setMilliseconds(0);

        return dateReset.getTime();
      }

      var appointmentExclusions=<?php echo json_encode($appointmentExclusions); ?>;

      var comingDaysExcluded=<?php echo (int)$attExcludeComingDays; ?>;
      var minDate=new Date();
      minDate.setTime(minDate.getTime()+86400000*comingDaysExcluded);
      minDate=dateTimestamp(minDate);

      function checkAppointmentExclusion(date){
        var dateString=[date.getFullYear(),date.getMonth()+1,date.getDate()].join("-");
        var dateComparison=dateTimestamp(date);

        var excluded=false;

        if($.inArray(dateString,appointmentExclusions)!== -1)
          excluded=true;
        else if(dateComparison<minDate)
          excluded=true;

        return [!excluded,""];
      }

      $(".datepicker")
        .prop("readonly",true)
        .datepicker(
        {
          beforeShowDay:checkAppointmentExclusion,
          dateFormat:"dd/mm/yy", // Australian format
          minDate:0
        });
    }

    if(typeof($.fn.validate==="function"))
      $(".mh-wrapper-maintenance-review").validate(
        {
          message:"<?php _e('Please fill in all required fields','myHome'); ?>",
          feedbackClass:"mh-error"
        });
  });
</script>
