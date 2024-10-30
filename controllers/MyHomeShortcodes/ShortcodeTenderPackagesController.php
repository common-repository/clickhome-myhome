<?php

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderPackagesController'))
  return;

/**
 * The ShortcodeTenderPackagesController class
 *
 * Controller for the TenderPackage shortcode
 *
 * @since 1.6
 */
class ShortcodeTenderPackagesController extends ShortcodeTenderBaseController {

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';
    $atts['showitemprices'] = isset($atts['showitemprices']) ? $atts['showitemprices']==='true' : true;

    $tender = $this->tender($this->getParam('myHomeTenderId'), true);

    if($tender===null)
      throw new MyHomeException(sprintf('Tender %u not available', $tenderId));

    $this->loadView('shortcodeTenderPackages','MyHomeShortcodes',compact('tender', 'atts')); //'categories','category','atts'));
  }
  
}
