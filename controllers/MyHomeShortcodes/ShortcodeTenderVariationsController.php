<?php

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderVariationsController'))
  return;

/**
 * The ShortcodeTenderVariationsController class
 *
 * Controller for the TenderVariations shortcode
 *
 * @since 1.6
 */
class ShortcodeTenderVariationsController extends ShortcodeTenderBaseController{

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
    //myHome()->log->info('ShortcodeTenderVariationController doPostXhr: ' . json_encode($params, JSON_PRETTY_PRINT));
    if(!isset($params['tenderId']))
      myHome()->abort(400,'tenderId not provided'); // Bad-Request
    else if(!isset($params['variationId']))
      myHome()->abort(400,'variationId not provided'); // Bad-Request
    //else if(strlen($params['data']) < 200)
    //  myHome()->abort(400,'Signature too short: ' . strlen($params['data']) . ' : ' . $params['data']); // Bad-Request

    //myHome()->log->info('switch: ' . $params['myHomeAction']);
    switch($params['myHomeAction']) {
      case 'variationApprove':
        if(!isset($params['data'])) myHome()->abort(400,'Signature not provided'); // Bad-Request
        $response = myHome()->api->post('tenders/' . $params['tenderId'] . '/variations/' . $params['variationId'] . '/clientApprove', $params, myHome()->session->getAuthentication());
        break;
      case 'variationReject':
        $response = myHome()->api->post('tenders/' . $params['tenderId'] . '/variations/' . $params['variationId'] . '/clientReject', $params, myHome()->session->getAuthentication());
        break;
    }

    myHome()->log->info('ShortcodeTenderVariationController doPostXhr response: ' . json_encode($response));
    //if($response) 
    echo json_encode($response);
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
	  //$atts=shortcode_atts(['showitemquantities'=>'true'],$atts);
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';
    //$attShowItemQuantities=$atts['showitemquantities']==='true';

    // Set & return tender details
    $tender = $this->tender($this->getParam('myHomeTenderId'), true); // Removed as takes too long
    /*global $tender;
    $tender = (object) [
      'tenderid' => $this->getParam('myHomeTenderId'),
      'housetypename' => $this->getParam('myHomeHouseType'),
      'urls' => (object) []
    ];
    $tenderPages = myHome()->options->getTenderPages();
    if(isset($tenderPages['overview'])) 
      $tender->urls->overview = add_query_arg(['myHomeTenderId' => $tender->tenderId], get_permalink($tenderPages['overview']));
    if(isset($tenderPages['selections'])) 
      $tender->urls->selections = add_query_arg(['myHomeTenderId' => $tender->tenderId], myHome()->options->isTenderSkipSelectionOverview() ? get_permalink($tenderPages['selectionsEdit']) : get_permalink($tenderPages['selections']));
    if(isset($tenderPages['packages'])) 
      $tender->urls->packages = add_query_arg(['myHomeTenderId' => $tender->tenderId], get_permalink($tenderPages['packages']));
    if(isset($tenderPages['variations'])) 
      $tender->urls->variations = add_query_arg(['myHomeTenderId' => $tender->tenderId], get_permalink($tenderPages['variations']));
    */
    //echo json_encode($tender, JSON_PRETTY_PRINT);

    $variations = myHome()->api->get(sprintf('tenders/%u/variations', $tender->tenderid), myHome()->session->getAuthentication());
    if(is_array($variations)) $variations = array_reverse($variations);

    /* Disabled until API exists
    if($packages===null)
      throw new MyHomeException(sprintf('Tender %u not available', $tenderId));
    else if(!$packages)
		  throw new MyHomeException(sprintf('Tender %u has no available packages', $tenderId));*/
    //$selectedVariation=$this->selectedOption($packageCategories);

    $declaration = myHome()->options->getTenderVariationDeclaration();

    $this->loadView('shortcodeTenderVariations','MyHomeShortcodes',compact('tender', 'variations', 'declaration', 'atts'));
  }

}
