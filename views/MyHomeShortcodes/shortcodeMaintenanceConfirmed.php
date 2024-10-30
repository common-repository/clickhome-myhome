<?php
/**
 * The maintenanceConfirmed view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeMaintenanceConfirmedController))
  die;

/**
 * @var ShortcodeMaintenanceConfirmedController $this
 * @var stdClass                                $jobDetails
 * @var mixed[]                                 $issues
 */

$statuses=['A'=>__('New','myHome'),
  'P'=>__('Pending','myHome'),
  'M'=>__('Maintenance','myHome'),
  'F'=>__('Finishing','myHome'),
  'C'=>__('Closed','myHome'),
  'X'=>__('Cancelled','myHome')];

if(isset($statuses[$jobDetails->status]))
  $status=$statuses[$jobDetails->status];
else
  $status='';
?>
<div class="mh-wrapper mh-wrapper-maintenance-confirmed">
  <div class="mh-row mh-row-maintenance-confirmed-job-number"><?php printf(_x('Job Number: %s','Maintenance Confirmed',
      'myHome'),esc_html($jobDetails->job)); ?></div>
  <div class="mh-row mh-row-maintenance-confirmed-status"><?php printf(_x('Status: %s','Maintenance Confirmed',
      'myHome'),esc_html($status)); ?></div>
  <div class="mh-row mh-row-maintenance-confirmed-type"><?php printf(_x('Type: %s','Maintenance Confirmed','myHome'),
      esc_html($jobDetails->type)); ?></div>
  <div class="mh-row mh-row-maintenance-confirmed-issues">
    <?php _ex('Issues:','Maintenance Header','myHome'); ?>
    <ul>
      <?php foreach($issues as $issue): ?>
        <li><?php echo $issue['title']; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
