<?php

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderSelectionsController'))
  return;

/**
 * The ShortcodeTenderSelectionsController class
 *
 * Controller for the TenderSelection shortcode
 *
 * @since 1.5
 */
class ShortcodeTenderSelectionsController extends ShortcodeTenderBaseController{
  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
	  //$atts = shortcode_atts(['content'=>'', 'showitemquantities'=>'true', 'showitemprices'=>'true', 'showrunningquantities'=>'false', 'showrunningprices'=>'false'],$atts);
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';
    $atts['showitemquantities'] = isset($atts['showitemquantities']) ? $atts['showitemquantities'] === 'true' : true;
    $atts['showitemprices'] = isset($atts['showitemprices']) ? $atts['showitemprices'] === 'true' : true;
    $atts['showrunningquantities'] = isset($atts['showrunningquantities']) ?  $atts['showrunningquantities'] === 'true' : true;
    $atts['showrunningprices'] = isset($atts['showrunningprices']) ? $atts['showrunningprices'] === 'true' : false;
    $atts['showsummaries'] = isset($atts['showsummaries']) ? $atts['showsummaries'] === 'true' : true;

    // Set & return global tender object
    $tender = $this->tender($this->getParam('myHomeTenderId'), true); //var_dump($tender);
    $categories = myHome()->api->get(sprintf('tenders/%u/selections/primaryCategories',$tender->tenderid), myHome()->session->getAuthentication()); //$this->categories($tender->tenderid, $atts['showsummaries']);
    
    // Only for testing
    //$categories = array_merge($categories, $categories);
    //$categories[0]->subCategories[0]->selections = array_merge($categories[0]->subCategories[0]->selections, $categories[0]->subCategories[0]->selections);

    $editUrl = add_query_arg(['myHomeTenderId' => $tender->tenderid], get_permalink(myHome()->options->getTenderPages()['selectionsEdit']));

    $this->loadView('shortcodeTenderSelections','MyHomeShortcodes',compact('tender', 'categories','editUrl','atts'));
  }
}
