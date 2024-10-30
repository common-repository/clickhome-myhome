<?php

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderSelectionsEmailController'))
  return;

/**
 * The ShortcodeTenderSelectionsEmailController class
 *
 * Controller for the TenderSelectionsEmail shortcode
 *
 * @since 1.5
 */
class ShortcodeTenderSelectionsEmailController extends ShortcodeTenderBaseController{
  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){ // myHome()->log->info('emailselections doPostXhr()');
    list($tenderId)=$this->extractParams(['myHomeTenderId'],$params);

    if($tenderId<=0)
      myHome()->abort(500,'Wrong tender ID');

    $dataParams = ['tenderId'=>$tenderId];

    try{
      $response = myHome()->api->post(sprintf('tenders/%u/emailselections',$tenderId), $dataParams, myHome()->session->getAuthentication());

      //myHome()->log->info('emailselections api response: ' . json_encode($emailselections));
      
      if(strtolower($response->status) != 200)
        myHome()->abort(400,'Email selection report failed'); // Bad request

      echo json_encode(['ok'=>1]);
    }
    catch(Exception $e){
      $className=(new ReflectionClass($e))->getShortName();
      myHome()->abort(500,sprintf('Error while trying to email selections report (%s): %s',$className,$e->getMessage()));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';

    $tenderId = $this->getParam('myHomeTenderId');

    $this->loadView('shortcodeTenderSelectionsEmail','MyHomeShortcodes',compact('tenderId','atts'));
  }

}
