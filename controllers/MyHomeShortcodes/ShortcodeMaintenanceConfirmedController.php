<?php

/**
 * The ShortcodeMaintenanceConfirmedController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeMaintenanceConfirmedController'))
  return;

/**
 * The ShortcodeMaintenanceConfirmedController class
 *
 * Controller for the Maintenance Confirmed shortcode
 *
 * @since 1.2
 */
class ShortcodeMaintenanceConfirmedController extends ShortcodeMaintenanceBaseController{
  /**
   * {@inheritDoc}
   */
  protected function doPostMaintenance(array $params){
  }

  /**
   * {@inheritDoc}
   */
  protected function doShortcodeMaintenance(array $atts){
    // The job ID must be present as a GET parameter
    $jobId=$this->getParam(self::$PARAM_JOB_ID);
    if($jobId===null)
      throw new MyHomeException('Job ID not provided');

    // Even if this screen is supposed to be the last step of a maintenance request, cache invalidation should not take place here - instead, it must be carried out just after the status may have changed (when submitting the job)
    $this->deleteJobDetails($jobId);    
    $jobDetails = $this->retrieveJobDetails($jobId);
    // myHome()->log->info('jobDetails: ' . json_encode($jobDetails, JSON_PRETTY_PRINT));

    if(!$jobDetails)
      throw new MyHomeException('Job not found: '.$jobId);

    $issues=[];

    if(!empty($jobDetails->issues))
      foreach($jobDetails->issues as $issue){
        if(empty($issue->name))
          continue;

        $issue=['title'=>$issue->name];

        $issues[]=$issue;
      }

    $this->loadView('shortcodeMaintenanceConfirmed','MyHomeShortcodes',compact('jobDetails','issues'));
  }
}
