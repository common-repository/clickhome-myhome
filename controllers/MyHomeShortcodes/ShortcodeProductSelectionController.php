<?php

/**
 * The ShortcodeProductSelectionController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeProductSelectionController'))
  return;

/**
 * The ShortcodeProductSelectionController class
 *
 * Controller for the Product Selection shortcode
 *
 * @since 1.3
 */
class ShortcodeProductSelectionController extends MyHomeShortcodesBaseController{
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
    $atts=shortcode_atts(['filterprice'=>'on',
      'filtersize'=>'on',
      'filterwidth'=>'on',
      'filterbedrooms'=>'on',
      'filterbathrooms'=>'on',
      'filtercarparks'=>'on',
      'displaylinks'=>'on'],$atts);

    $attFilterPrice=$atts['filterprice'];
    $attFilterSize=$atts['filtersize'];
    $attFilterWidth=$atts['filterwidth'];
    $attFilterBedrooms=$atts['filterbedrooms'];
    $attFilterBathrooms=$atts['filterbathrooms'];
    $attFilterCarparks=$atts['filtercarparks'];
    $attDisplayLinks=$atts['displaylinks'];

    if(!$this->verifyOption($attFilterPrice)){
      myHome()->handleError('Wrong Filter Price attribute: '.$attFilterPrice);
      $attFilterPrice='on';
    }
    if(!$this->verifyOption($attFilterSize)){
      myHome()->handleError('Wrong Filter Size attribute: '.$attFilterSize);
      $attFilterSize='on';
    }
    if(!$this->verifyOption($attFilterWidth)){
      myHome()->handleError('Wrong Filter Width attribute: '.$attFilterWidth);
      $attFilterWidth='on';
    }
    if(!$this->verifyOption($attFilterBedrooms)){
      myHome()->handleError('Wrong Filter Bedrooms attribute: '.$attFilterBedrooms);
      $attFilterBedrooms='on';
    }
    if(!$this->verifyOption($attFilterBathrooms)){
      myHome()->handleError('Wrong Filter Bathrooms attribute: '.$attFilterBathrooms);
      $attFilterBathrooms='on';
    }
    if(!$this->verifyOption($attFilterCarparks)){
      myHome()->handleError('Wrong Filter Carparks attribute: '.$attFilterCarparks);
      $attFilterCarparks='on';
    }
    if(!$this->verifyOption($attDisplayLinks)){
      myHome()->handleError('Wrong Display Links attribute: '.$attDisplayLinks);
      $attDisplayLinks='on';
    }

    $maxValues=MyHomeHouseType::maxValues();

    // Default values, used if not available
    $maxMinPrice=10000000;
    $maxSize=1000;
    $maxMinWidth=1000;
    $maxBedrooms=10;
    $maxBathrooms=10;
    $maxGarage=10;

    foreach(['maxMinPrice','maxSize','maxMinWidth','maxBedrooms','maxBathrooms','maxGarage'] as $variable)
      if(isset($maxValues->$variable))
        ${$variable}=$maxValues->$variable;

    $maxBathrooms=round($maxBathrooms);

    list($houseTypes,$houseTypesUrls)=$this->houseTypesList();
    list($displays,$displaysUrls)=$this->displaysList();

    $docsAttachments=myHome()->advertising->docFindAllAttachments();

    //myHome()->log->info(json_encode($houseTypes));
    //myHome()->log->info(json_encode($houseTypesUrls));
    //myHome()->log->info(json_encode($displays));
    //myHome()->log->info(json_encode($displaysUrls));
    //myHome()->log->info(json_encode($docsAttachments));

    $this->loadView('shortcodeProductSelection','MyHomeShortcodes',
      compact('attFilterPrice','attFilterSize','attFilterWidth','attFilterBedrooms','attFilterBathrooms',
        'attFilterCarparks','attDisplayLinks','maxMinPrice','maxSize','maxMinWidth','maxBedrooms','maxBathrooms',
        'maxGarage','houseTypes','houseTypesUrls','displays','displaysUrls','docsAttachments'));
  }

  /**
   * Returns the displays list
   *
   * @uses MyHomeDisplay::all()
   * @uses MyHomeDisplay::findAllPages()
   * @return mixed[] two arrays indexed by display ID:
   * <ul>
   * <li>Display objects (MyHomeDisplay[])</li>
   * <li>Display page URLs (string[])</li>
   * </ul>
   * @throws MyHomeException if the list of displays could not be retrieved (because of a database error)
   */
  private function displaysList(){
    $displayPages=MyHomeDisplay::findAllPages();

    // Both arrays contain arrays indexed by house ID
    $displaysList=[];
    $displaysUrls=[];

    $displays=MyHomeDisplay::all();
    if($displays===null)
      throw new MyHomeException('Could not retrieve the displays list - check the log for more details');

    foreach($displays as $display){
      $displayId=$display->displayid;

      if(!isset($displayPages[$displayId]))
        continue;
      $page=$displayPages[$displayId];

      $houseTypeId=$display->houseid;

      if(!isset($displaysList[$houseTypeId])){
        $displaysList[$houseTypeId]=[];
        $displaysUrls[$houseTypeId]=[];
      }

      $displaysList[$houseTypeId][$displayId]=$display;
      $displaysUrls[$houseTypeId][$displayId]=get_permalink($page->ID);
    }

    return [$displaysList,$displaysUrls];
  }

  /**
   * Returns the house types list
   *
   * @uses MyHomeHouseType::all()
   * @uses MyHomeHouseType::findAllPages()
   * @return mixed[] two arrays indexed by house type ID:
   * <ul>
   * <li>House type objects (MyHomeHouseType[])</li>
   * <li>House type page URLs (string[])</li>
   * </ul>
   * @throws MyHomeException if the list of house types could not be retrieved (because of a database error)
   */
  private function houseTypesList(){
    $houseTypesPages=MyHomeHouseType::findAllPages();

    $houseTypesList=[];
    $houseTypesUrls=[];

    $houseTypes=MyHomeHouseType::all();
    if($houseTypes===null)
      throw new MyHomeException('Could not retrieve the house types list - check the log for more details');

    foreach($houseTypes as $houseType){
      $houseTypeId=$houseType->houseid;

      if(!isset($houseTypesPages[$houseTypeId]))
        continue;
      $page=$houseTypesPages[$houseTypeId];
      
      $houseTypesList[$houseTypeId]=$houseType;
      $houseTypesUrls[$houseTypeId]=get_permalink($page->ID);
    }

    return [$houseTypesList,$houseTypesUrls];
  }

  /**
   * Verifies the value of any option attributes
   *
   * @param string $option the option attribute value to check
   * @return bool whether the attribute is valid or not (it must be "on" or "off")
   */
  private function verifyOption($option){
    return in_array($option,['on','off']);
  }
}
