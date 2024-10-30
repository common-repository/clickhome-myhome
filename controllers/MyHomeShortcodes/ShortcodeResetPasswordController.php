<?php

/**
 * The ShortcodeResetPasswordController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.5 before this version, password recovery was displayed using [MyHome.Login lostpassword=recovery]
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeResetPasswordController'))
  return;

/**
 * The ShortcodeResetPasswordController class
 *
 * Controller for the Login shortcode
 *
 * @since 1.5 before this version, password recovery was displayed using [MyHome.Login lostpassword=recovery]
 */
class ShortcodeResetPasswordController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    //return $this->recovery($params);
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
    $this->loadView('shortcodeResetPassword','MyHomeShortcodes');
  }

}
