<?php

/**
 * The ShortcodeTenderOverviewPackagesController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.5.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderOverviewPackagesController'))
  return;

/**
 * The ShortcodeTenderOverviewPackagesController class
 *
 * Controller for the TenderOverview shortcode
 *
 * @since 1.5.5
 */
class ShortcodeTenderOverviewPackagesController extends ShortcodeTenderBaseController {

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]) {
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';

    // Ensure the view has, or throws an error for the global $tender object
    $tender = $this->tender();

    $this->loadView('shortcodeTenderOverviewPackages','MyHomeShortcodes',compact('tender', 'atts'));
  }
}
