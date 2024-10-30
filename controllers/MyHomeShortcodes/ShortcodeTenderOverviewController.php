<?php

/**
 * The ShortcodeTenderOverviewController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderOverviewController'))
  return;

/**
 * The ShortcodeTenderOverviewController class
 *
 * Controller for the TenderOverview shortcode
 *
 * @since 1.5
 */
class ShortcodeTenderOverviewController extends ShortcodeTenderBaseController{
  /**
   * Field names as they are shown on the shortcode
   */
  protected static $FIELD_NAMES=['houseDesign'=>'House Design',
    'facade'=>'Facade',
    'size'=>'Size',
    'orientation'=>'Orientation',
    'bedrooms'=>'Bedrooms',
    'bathrooms'=>'Bathrooms',
    'livingAreas'=>'Living areas',
    'stories'=>'Stories',
    'parking'=>'Parking',
    'description'=>'Description'];

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    //$atts=shortcode_atts(['showselections'=>'true', 'showpackages'=>'false'],$atts);
    $atts['content'] = isset($atts['content']) ? $atts['content'] : '';

    //$tenderId = $this->getParam('myHomeTenderId');
    //if($tenderId===null)
    //  throw new MyHomeException('Tender ID not provided');
    $tender = $this->tender($this->getParam('myHomeTenderId')); //$tenderId);

    //var_dump($tender);
    //if($tender===null) throw new MyHomeException(sprintf('Tender %u not available',$tenderId));

    $fieldNames=static::$FIELD_NAMES;

    $this->loadView('shortcodeTenderOverview','MyHomeShortcodes',compact('tender', 'atts','fieldNames'));
  }
}
