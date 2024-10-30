<?php

/**
 * The ShortcodePhotosController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodePhotosController'))
  return;

/**
 * The ShortcodePhotosController class
 *
 * Controller for the Photos shortcode
 */
class ShortcodePhotosController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
    list($documentId)=$this->extractParams(['myHomeDocumentId'],$params);

    if($documentId<=0)
      myHome()->abort(500,'Wrong document ID');

    $authentication=myHome()->session->getAuthenticationDocuments();
    $document=myHome()->api->download(['documents',$documentId],$authentication);

    if(myHome()->api->getLastErrorType()!==null)
      myHome()->abort(500,'API request error: '.myHome()->api->getLastErrorMessage()); // Internal error
    else if($document==='')
      myHome()->abort(500,'Empty document'); // Internal error

    $facebook=myHome()->facebook;

    try{
      $photoId=$facebook->uploadImage($document,__('Uploaded with ClickHome.MyHome plugin for WordPress.','myHome'));

      $graphNode=$facebook->photoDetails($photoId);

      $url=$facebook->photoUrl($graphNode);
      $pageUrl=$facebook->photoPageUrl($graphNode);

      echo json_encode(compact('url','pageUrl'));
    }
    catch(Exception $e){
      $className=(new ReflectionClass($e))->getShortName();
      myHome()->abort(500,sprintf('Error while trying to upload a picture (%s): %s',$className,$e->getMessage()));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts=shortcode_atts([
      'columns'=>'5',
      'limit'=>null,
      'facebook'=>'dialog',
      'slideshow'=>'false'
    ],$atts);

    $attColumns = $atts['columns'];
    $attLimit = isset($atts['limit']) ? (int) $atts['limit'] : null;
    $attFacebook = $atts['facebook'];
    $attSlideshow = $atts['slideshow'] == 'true' ? true : false;

    if(!$this->verifyColumns($attColumns)){
      myHome()->handleError('Wrong Columns attribute: '.$attColumns);
      $attColumns=5;
    }
    if(!$this->verifyFacebook($attFacebook)){
      myHome()->handleError('Wrong Facebook attribute: '.$attFacebook);
      $attFacebook='dialog';
    }

    $attColumns=(int)$attColumns;

    $photos=$this->photosList();
    if($photos===null)
      return;
    if($attLimit)
      $photos = array_slice($photos,0,$attLimit);

    // Disable the Share button if we're not under a Facebook session
    if($attFacebook!=='no'&&!myHome()->session->isFacebook())
       $attFacebook='no';

    if($attFacebook!=='no')
      $facebookAppId=myHome()->options->getFacebookAppId();
    else
      $facebookAppId='';

    $this->loadView('shortcodePhotos','MyHomeShortcodes',compact('photos','attColumns','attSlideshow','attFacebook','facebookAppId'));
  }

  /**
   * Checks whether a given file extension corresponds to an image
   *
   * @param string $extension the file extension
   * @return bool whether the extension corresponds to an image
   */
  private function checkImageExtension($extension){
    // This list must match the extensions at ShortcodeDocumentsController::documentType()
    $imageExtensions=['jpg',
      'jpeg',
      'png',
      'bmp',
      'gif'];

    return in_array(strtolower($extension),$imageExtensions);
  }

  /**
   * Returns the photos list after querying the API with the documents command
   *
   * @uses MyHomeApi::get()
   * @uses MyHomeBaseController::dateString()
   * @uses ShortcodePhotosController::checkImageExtension() to filter the complete documents list
   * @return mixed[]|null the photos list (null if not available) - each item is composed of:
   * <ul>
   * <li>title: photo title (title field)</li>
   * <li>date: formatted photo date (generated from the docdate field)</li>
   * <li>url: photo document ID (url field)</li>
   * </ul>
   */
  private function photosList(){
    $authentication=myHome()->session->getAuthentication();
    $documents=myHome()->api->get('documents',$authentication,true);
	  //myHome()->log->info(json_encode($documents, JSON_PRETTY_PRINT));

    if($documents===null)
      return null;

    $photosList=[];

    foreach($documents as $document){
	    //print_r($document);

	 // echo $document->byclient;
     // if(empty($document->byclient))
      //  continue;

      if(empty($document->docdate))
        continue;
      if(empty($document->title))
        continue;
      if(empty($document->url))
		  continue;
      if(empty($document->type))
		  continue;
      //if(!$document->current)
		  //continue;

      // Retrieve only image documents
      if(!$this->checkImageExtension($document->type))
        continue;

      $dt=new DateTime($document->docdate);

      $photosList[]=[
        'title'=>$document->title,
        'date'=>$this->dateString($dt),
        'url'=> $document->url // trailingslashit(myHome()->options->getEndpoint()) . 
      ];
    }

    return $photosList;
  }

  /**
   * Verifies the value of the columns shortcode attribute provided
   *
   * @param int $columns the columns attribute value to check
   * @return bool whether the attribute is valid or not (it must be between 1 and 10)
   */
  private function verifyColumns($columns){
    return $columns>=1&&$columns<=10;
  }

  /**
   * Verifies the value of the facebook shortcode attribute provided
   *
   * @since 1.4
   * @param string $facebook the facebook attribute value to check
   * @return bool whether the attribute is valid or not (it must be "dialog", "page" or "no")
   */
  private function verifyFacebook($facebook){
    return in_array($facebook,['dialog','page','no']);
  }
}
