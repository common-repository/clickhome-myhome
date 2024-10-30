<?php

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderSelectionsEditController'))
  return;

/**
 * The ShortcodeTenderSelectionsEditController class
 *
 * Controller for the TenderSelectionsEdit shortcode
 *
 * @since 1.5
 */
class ShortcodeTenderSelectionsEditController extends ShortcodeTenderBaseController {

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
	  //$atts=shortcode_atts(['content'=>'','showitemquantities'=>'false', 'showitemprices'=>'false', 'showrunningquantities'=>'false', 'showrunningprices'=>'false'],$atts);
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';
    $atts['showitemquantities'] = isset($atts['showitemquantities']) ? $atts['showitemquantities'] === 'true' : true;
    $atts['showitemprices'] = isset($atts['showitemprices']) ? $atts['showitemprices'] === 'true' : true;
    $atts['showrunningquantities'] = isset($atts['showrunningquantities']) ?  $atts['showrunningquantities'] === 'true' : true;
    $atts['showrunningprices'] = isset($atts['showrunningprices']) ? $atts['showrunningprices'] === 'true' : false;
    
    $tender = $this->tender($this->getParam('myHomeTenderId'), true); //var_dump($tender);

    /*$categories = $this->categories($tender->tenderid); //var_dump($categories);

    //$category = $this->category($tender->tenderid, $this->getParam('myHomeTenderSelectionCategoryId') ? $this->getParam('myHomeTenderSelectionCategoryId') : $categories[0]->primaryCategoryId);
    $category = $this->category($categories, $this->getParam('myHomeTenderSelectionCategoryId') ? $this->getParam('myHomeTenderSelectionCategoryId') : $categories[0]->subCategories[0]->primaryCategoryId);
    //echo('<pre>' . json_encode($category, JSON_PRETTY_PRINT) . '</pre>');*/

    $this->loadView('shortcodeTenderSelectionsEdit','MyHomeShortcodes', compact('tender', 'atts')); //categories','category','atts'));
  }
}
