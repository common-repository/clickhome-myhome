<?php

/**
 * The ShortcodeLogoffController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeLogoffController'))
  return;

/**
 * The ShortcodeLogoffController class
 *
 * Controller for the Logoff shortcode
 */
class ShortcodeLogoffController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    myHome()->session->logoff();
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
    $atts=shortcode_atts(['redirect'=>''],$atts);

    $attRedirect=$atts['redirect'];

    if($attRedirect&&!$this->validateUrl($attRedirect)){
      myHome()->handleError('Wrong Redirect attribute: '.$attRedirect);
      $attRedirect=home_url();
    }
    // If the redirect attribute is an empty string, use the site homepage as the redirection page (the main page is supposed to required authentication, so it is not considered here)
    else if(!$attRedirect)
      $attRedirect=home_url();

    $this->loadView('shortcodeLogoff','MyHomeShortcodes',compact('attRedirect'));
  }

  /**
   * Validates a URL
   *
   * @param string $url the URL to validate
   * @return bool whether the URL is valid
   */
  private function validateUrl($url){
    return preg_match('|^https?://[-a-z0-9+&@#/%?=~_\|!:,.;]*[-a-z0-9+&@#\/%=~_\|]$|i',$url);
  }
}
