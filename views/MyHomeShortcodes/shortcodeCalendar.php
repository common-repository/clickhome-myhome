<?php
/**
 * The calendar view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeCalendarController))
  die;

/**
 * @var ShortcodeCalendarController $this
 * @var string                      $attList
 * @var mixed[]|null                $events
 */
?>

<div class="mh-wrapper mh-wrapper-calendar">
  <?php if($atts['list']): ?>
    <div class="mh-section mh-section-calendar-list">
      <?php foreach($events as $event): ?>
        <div class="mh-block mh-block-calendar-list">
          <div class="mh-calendar-list-event-name"><?php echo esc_html($event['name']); ?></div>
          <div class="mh-calendar-list-event-date"><a class="mh-link mh-link-calendar-list-event-date"
              data-month="<?php echo esc_attr($event['month']); ?>" data-year="<?php echo esc_attr($event['year']); ?>"
              href="javascript:void(0);"><?php echo esc_html($event['date']); ?></a></div>
          <?php if($event['resourceName'] && $atts['resource']): ?>
            <div class="mh-calendar-list-event-resource"><?php echo esc_html($event['resourceName']); ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div class="mh-section mh-section-calendar-calendar">
    <div class="mh-header mh-header-calendar-calendar clearfix">
      <button class="mh-button mh-button-sub mh-button-calendar-calendar-previous" id="buttonMyHomePrevious"
        type="button"><?php _e('Previous Month','myHome'); ?></button>
      <button class="mh-button mh-button-sub mh-button-calendar-calendar-next" id="buttonMyHomeNext"
        type="button"><?php _e('Next Month','myHome'); ?></button>
      <div class="mh-block mh-block-calendar-calendar-month">
        <span id="spanMyHomeMonth">&nbsp;</span>

        <div class="mh-loading mh-loading-calendar-month" id="divMyHomeLoadingCalendar"></div>
      </div>
    </div>
    <div class="mh-body mh-body-calendar-calendar">
      <table class="mh-table mh-table-calendar-calendar">
        <tbody>
        <?php for($row=0;$row<6;$row++): ?>
          <tr class="mh-row mh-row-calendar-calendar" data-row="<?php echo $row; ?>" data-sunday="false">
            <?php for($cell=0;$cell<5;$cell++): ?>
              <td class="mh-cell mh-cell-calendar-calendar" data-cell="<?php echo $cell; ?>" rowspan="2">
                <div class="mh-calendar-calendar-day">&nbsp;</div>
                <div class="mh-calendar-calendar-events">
                  <ul></ul>
                </div>
              </td>
            <?php endfor; ?>
            <td class="mh-cell mh-cell-calendar-calendar" data-cell="<?php echo $cell++; ?>">
              <div class="mh-calendar-calendar-day">&nbsp;</div>
              <div class="mh-calendar-calendar-events">
                <ul></ul>
              </div>
            </td>
          </tr>
          <tr class="mh-row mh-row-calendar-calendar" data-row="<?php echo $row; ?>" data-sunday="true">
            <td class="mh-cell mh-cell-calendar-calendar" data-cell="<?php echo $cell++; ?>">
              <div class="mh-calendar-calendar-day">&nbsp;</div>
              <div class="mh-calendar-calendar-events">
                <ul></ul>
              </div>
            </td>
          </tr>
        <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeCalendar.js" type="text/javascript"></script>
<script>
  <?php $xhrAttributes = $this->xhrAttributes('calendar'); ?>
  jQuery(function ($) {
      _.extend(mh.calendar, {
        vars: {
          showResource: <?php echo $atts['resource'] ? 'true' : 'false'; ?>
        },
  	    xhr: {
          url: '<?php echo $xhrAttributes['url']; ?>',
          actions: <?php echo json_encode($xhrAttributes['actions']); ?>
        }
      });
      mh.calendar.init();
  });
</script>