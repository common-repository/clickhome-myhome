<?php

/**
 * The ShortcodeHouseTypeController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeHouseTypeController'))
  return;

/**
 * The ShortcodeHouseTypeController class
 *
 * Controller for the House Type shortcode
 *
 * @since 1.3
 */
class ShortcodeHouseTypeController extends MyHomeShortcodesBaseController{
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

    if($atts['id'] == '0'){
		myHome()->handleError('No ID attribute provided');
		return;
    }

    $houseType=MyHomeHouseType::find(['houseid'=>(int)$atts['id']]);
	//var_dump($houseType);
	if(!$houseType){
		myHome()->handleError('Wrong ID attribute (house type not found): '.$atts['id']);
		return;
    }

    $houseType=$houseType[0];

    $this->loadView('shortcodeHouseType','MyHomeShortcodes',compact('houseType'));
  }
}
