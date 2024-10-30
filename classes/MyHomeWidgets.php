<?php

/**
 * The MyHomeWidgets class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeWidgets'))
  return;

/**
 * The MyHomeWidgets class
 *
 * Handles MyHome widgets inserted anywhere in the site
 *
 * @since 1.3 registerWidget() function removed
 */
class MyHomeWidgets{
  /**
   * Widgets definitions: widget class names
   */
  private static $WIDGETS=['ContractHeaderWidget',
    'LogoffWidget'];

  /**
   * Registers the available widgets
   *
   * Triggered by the widgets_init action
   *
   * @uses MyHomeWidgets::$WIDGETS
   */
  public function registerWidgets(){
    try{
      foreach(self::$WIDGETS as $widget)
        register_widget($widget);
    }
    catch(MyHomeException $e){
      myHome()->handleError($e);
    }
  }

  /**
   * Registers the appropriate WordPress hooks
   *
   * The registerWidgets() method is attached to the widgets_init hook, in order to register every available widget
   * when that action is triggered
   */
  public function setupHooks(){
    add_filter('widgets_init',[$this,'registerWidgets'],1);
  }
}
