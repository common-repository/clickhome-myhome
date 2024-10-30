<?php

/**
 * The ShortcodeHouseDetailsController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeHouseDetailsController'))
  return;

/**
 * The ShortcodeHouseDetailsController class
 *
 * Controller for the House Details shortcode
 */
class ShortcodeHouseDetailsController extends MyHomeShortcodesBaseController{
  /**
   * Field names as they are shown on the shortcode
   */
  protected static $FIELD_NAMES=['houseName'=>'House Type',
    'size'=>'Size',
    'facade'=>'Facade',
    'description'=>'Description'];

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
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts=shortcode_atts(['mode'=>'full'],$atts);

    $attMode=$atts['mode'];

    if(!$this->verifyMode($attMode)){
      myHome()->handleError('Wrong Mode attribute: '.$attMode);
      $attMode='full';
    }

    $houseDetails=$this->houseDetails();
    if($houseDetails===null)
      return;
    // An empty array means the API call was successful, but the required information is not available
    else if($houseDetails===[])
      return;

    $fieldNames=static::$FIELD_NAMES;

    $this->loadView('shortcodeHouseDetails','MyHomeShortcodes',compact('attMode','houseDetails','fieldNames'));
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
   * Returns the house details after querying the API with the house command
   *
   * @uses MyHomeApi::get()
   * @return mixed[]|null the house details array (null if not available, and an empty array if a required field is
   *                      missing or empty):
   * <ul>
   * <li>details: the house details fields - composed of:</li>
   * <ul>
   * <li>houseName: the house type (housename field)</li>
   * <li>size: the size (size field)</li>
   * <li>facade: the facade (facade field)</li>
   * <li>description: the description (description field)</li>
   * </ul>
   * <li>photos: photos array (generated from the housedocs field) - each item is the photo document ID (url
   * field)</li>
   * </ul>
   */
  private function houseDetails(){
    $authentication=myHome()->session->getAuthentication();
    $houseDetails=myHome()->api->get('house',$authentication,false);

    if($houseDetails===null)
      return null;

    // The details should be returned as an object, but they are usually included in a one-element array instead
    if(is_array($houseDetails))
      if(!empty($houseDetails))
        $houseDetails=$houseDetails[0];
      else
        return null;

    if(empty($houseDetails->housename))
      return [];
    if(empty($houseDetails->size))
      return [];
    if(empty($houseDetails->facade))
      return [];
    if(empty($houseDetails->description))
      return [];

    $photos=[];

    // Check for images in the housedocs field
    if(!empty($houseDetails->housedocs))
      foreach($houseDetails->housedocs as $document){
        if(empty($document->title))
          continue;
        if(empty($document->type))
          continue;
        if(empty($document->url))
          continue;

        // Skip any document which is not an image (this should never happen)
        if(!$this->checkImageExtension($document->type))
          continue;

        $photos[]=$document->url;
      }

    $details=['details'=>['houseName'=>$houseDetails->housename,
      'size'=>$houseDetails->size,
      'facade'=>$houseDetails->facade,
      'description'=>$houseDetails->description],
      'photos'=>$photos];

    return $details;
  }

  /**
   * Verifies the value of the mode shortcode attribute provided
   *
   * @param string $mode the mode attribute value to check
   * @return bool whether the attribute is valid or not (it must be "full" or "simple")
   */
  private function verifyMode($mode){
    return in_array($mode,['full','simple']);
  }
}
