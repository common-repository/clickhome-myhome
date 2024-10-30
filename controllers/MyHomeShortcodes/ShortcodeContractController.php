<?php

/**
 * The ShortcodeContractController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeContractController'))
  return;

/**
 * The ShortcodeContractController class
 *
 * Controller for the Contract shortcode
 */
class ShortcodeContractController extends MyHomeShortcodesBaseController{
  /**
   * Field names as they are shown on the shortcode
   */
  protected static $FIELD_NAMES=[
    //'salesperson'=>'Sales Contact',
    'salesContact'=>'Sales Contact',
    'clientliaison'=>'Client Liaison',
    'supervisor'=>'Supervisor',
    'job'=>'Job Number',
    'lotaddress'=>'Site Address',
    'directions'=>'Site Directions',
    'housetype'=>'House Type',
    'facade'=>'Facade',
    'businessunit'=>'Business',
    'brand'=>'Brand',
    'clienttitle'=>'Client',
    'contactname'=>'Contact',
    'contactphone'=>'Phone',
    'contactemail'=>'Email'
  ];

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
    $atts=shortcode_atts([
      'mode'=>'full',
      'hidefields'=>''
    ],$atts);

    $attMode = $atts['mode'];
    $attHideFields = explode(',',$atts['hidefields']);
    $attHideFields = array_map('trim',$attHideFields);
    $attHideFields = array_filter($attHideFields,'strlen');

    if(!$this->verifyMode($attMode)){
      myHome()->handleError('Wrong Mode attribute: '.$attMode);
      $attMode='full';
    }
    if(!$this->verifyHideFields($attHideFields)){
      myHome()->handleError('Wrong Hide Fields attribute: '.implode(',',$attHideFields));
      $attHideFields=[];
    }
    /*if($attMode==='simple'&&$attHideFields){
      myHome()->handleError('The simple mode is not compatible with providing hidden fields'); // why? stop me
      $attMode='full';
    }*/

    // Get job details from the cached array in the MyHome session
    //if(!myHome()->session->getJobDetails()) return;
    $jobDetails = clone (object) myHome()->session->getJobDetails(); // Clone it, so it can be safely modified if needed within this request
    if($jobDetails===null)
      throw new MyHomeException('Job Details not found');
    // myHome()->log->info('jobDetails: ' . json_encode($jobDetails));

    $fieldNames=static::$FIELD_NAMES;

    $this->loadView('shortcodeContract','MyHomeShortcodes',compact('attMode','attHideFields','jobDetails','fieldNames'));
  }

  /**
   * Verifies the value of the hidefields shortcode attribute provided
   *
   * @see ShortcodeContractController::$FIELD_NAMES to see the complete list of allowed fields (array keys are the
   *      possible values)
   * @param string[] $hideFields the hidefields attribute value to check
   * @return bool whether the attribute is valid or not (it must not contain fields other than "job", "clienttitle",
   *                             etc.)
   */
  private function verifyHideFields(array $hideFields){
    foreach($hideFields as $field)
      if(!isset(self::$FIELD_NAMES[$field]))
        return false;

    return true;
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
