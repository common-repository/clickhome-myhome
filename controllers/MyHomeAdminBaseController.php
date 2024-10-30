<?php

/**
 * The MyHomeAdminBaseController class
 *
 * @package    MyHome
 * @subpackage Controllers
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeAdminBaseController'))
  return;

/**
 * The MyHomeAdminBaseController class
 *
 * Abstract class for admin view controllers
 */
abstract class MyHomeAdminBaseController extends MyHomeBaseController{
  protected static $ACTIVE_PAGE=null;

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
  }

  protected function writeHeaderTabs(){
    $tabs=['MyHomeSettings'=>__('Settings','myHome'),
      'MyHomeDebugConsole'=>__('Debug Console','myHome'),
      'MyHomeContactForm'=>__('Contact Form','myHome'),
      'MyHomeMaintenance'=>__('Maintenance','myHome'),
      'MyHomeAdvertising'=>__('Advertising','myHome'),
      'MyHomeFacebook'=>__('Facebook','myHome'),
      'MyHomeTender'=>__('Tender','myHome'),
      'MyHomeLog'=>__('Log','myHome')];

    if(!myHome()->options->isLogEnabled())
      unset($tabs['MyHomeLog']);

    echo "  <h2 class=\"nav-tab-wrapper\">\n";

    foreach($tabs as $page=>$name)
      /** @noinspection HtmlUnknownTarget */
      printf("    <a href=\"%s\" class=\"nav-tab%s\">%s</a>\n",esc_url(myHome()->admin->pageUrl($page)),
        $page===static::$ACTIVE_PAGE?' nav-tab-active':'',$name);

    echo "  </h2>\n";
  }
}
