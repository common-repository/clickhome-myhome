<?php

/**
 * The ShortcodeContractHeaderController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeContractHeaderController'))
  return;

/**
 * The ShortcodeContractHeaderController class
 *
 * Controller for the Contract Header shortcode
 */
class ShortcodeContractHeaderController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    // Get job details from the cached array in the MyHome session
    $jobDetails=
      clone myHome()->session->getJobDetails(); // Clone it, so it can be safely modified if needed within this request
    if($jobDetails===null)
      throw new MyHomeException('Job Details not found');

    $this->loadView('shortcodeContractHeader','MyHomeShortcodes',compact('jobDetails'));
  }
}
