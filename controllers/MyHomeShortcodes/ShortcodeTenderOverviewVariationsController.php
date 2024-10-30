<?php

/**
 * The ShortcodeTenderOverviewVariationsController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.5.7
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderOverviewVariationsController'))
  return;

/**
 * The ShortcodeTenderOverviewVariationsController class
 *
 * Controller for the TenderOverview shortcode
 *
 * @since 1.5.5
 */
class ShortcodeTenderOverviewVariationsController extends ShortcodeTenderBaseController {

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';

    // Ensure the view has, or throws an error for the global $tender object
    $tender = $this->tender();

    $this->loadView('shortcodeTenderOverviewVariations','MyHomeShortcodes',compact('tender', 'atts'));
  }
}
