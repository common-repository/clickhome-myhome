<?php

/**
 * The ShortcodeClientNameController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.5.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeClientNameController'))
  return;

/**
 * The ShortcodeContactController class
 *
 * Controller for the Contact shortcode
 *
 * @since 1.5.5
 */
class ShortcodeClientNameController extends MyHomeBaseController {
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }
  /**
   * {@inheritDoc}
   */
  public function doPostXHR(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $jobDetails = myHome()->session->getJobDetails();
    if(is_object($jobDetails)) echo($jobDetails->clienttitle);
  }
}
