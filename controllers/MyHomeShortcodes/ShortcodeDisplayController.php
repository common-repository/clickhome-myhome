<?php

/**
 * The ShortcodeDisplayController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeDisplayController'))
  return;

/**
 * The ShortcodeDisplayController class
 *
 * Controller for the Display shortcode
 *
 * @since 1.3
 */
class ShortcodeDisplayController extends MyHomeShortcodesBaseController{
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
    $atts=shortcode_atts(['id'=>'0'],$atts);

    $display=MyHomeDisplay::find(['displayid'=>(int)$atts['id']]);

    if(!$display){
      myHome()->handleError('Wrong ID attribute (display not found): '.$atts['id']);

      return;
    }

    $display=$display[0];

    $houseType=$display->houseType();

    if(!$houseType){
      myHome()->handleError('Wrong ID attribute (house type for the display not found): '.$atts['id']);

      return;
    }

    $this->loadView('shortcodeDisplay','MyHomeShortcodes',compact('display','houseType'));
  }
}
