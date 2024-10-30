<?php
/**
 * The maintenanceHeader view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeMaintenanceHeaderController))
  die;

/**
 * @var ShortcodeMaintenanceHeaderController $this
 * @var stdClass                             $jobDetails
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
<div class="mh-wrapper mh-wrapper-maintenance-header">
  <div class="mh-row mh-row-maintenance-header-job-number"><?php printf(_x('Job Number: %s','Maintenance Header',
      'myHome'),esc_html($jobDetails->job)); ?></div>
  <div class="mh-row mh-row-maintenance-header-status"><?php printf(_x('Status: %s','Maintenance Header','myHome'),
      esc_html($status)); ?></div>
  <div class="mh-row mh-row-maintenance-header-type"><?php printf(_x('Type: %s','Maintenance Header','myHome'),
      esc_html($jobDetails->type)); ?></div>
</div>
