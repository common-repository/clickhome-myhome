<?php

/**
 * The ShortcodeMaintenanceHeaderController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeMaintenanceHeaderController'))
  return;

/**
 * The ShortcodeMaintenanceHeaderController class
 *
 * Controller for the Maintenance Header shortcode
 *
 * @since 1.2
 */
class ShortcodeMaintenanceHeaderController extends ShortcodeMaintenanceBaseController{
  /**
   * {@inheritDoc}
   */
  protected function doPostMaintenance(array $params){
  }

  /**
   * {@inheritDoc}
   */
  protected function doShortcodeMaintenance(array $atts){
    // Retrieve the job ID from the PARAM_JOB_ID GET parameter - to be used in the same page as the issues, review, and confirmed shortcodes
    $jobId=$this->getParam(self::$PARAM_JOB_ID);
    if($jobId===null)
      throw new MyHomeException('Job ID not provided');

    $jobDetails=$this->retrieveJobDetails($jobId);

    if(!$jobDetails)
      throw new MyHomeException('Job not found: '.$jobId);

    $this->loadView('shortcodeMaintenanceHeader','MyHomeShortcodes',compact('jobDetails'));
  }
}
