<?php

/**
 * The ShortcodeTenderOverviewSelectionsController class
 *
 * @selection    MyHome
 * @subselection ControllersShortcodes
 * @since      1.6
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderOverviewSelectionsController'))
  return;

/**
 * The ShortcodeTenderOverviewSelectionsController class
 *
 * Controller for the TenderOverview shortcode
 *
 * @since 1.6
 */
class ShortcodeTenderOverviewSelectionsController extends ShortcodeTenderBaseController {
  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';

    // Ensure the view has, or throws an error for the global $tender object
    $tender = $this->tender();

    $this->loadView('shortcodeTenderOverviewSelections','MyHomeShortcodes',compact('tender', 'atts'));
  }
}
