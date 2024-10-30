<?php

/**
 * The ShortcodeLoginHelpController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.5 before this version, password recovery was displayed using [MyHome.Login lostpassword=recovery]
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeLoginHelpController'))
  return;

/**
 * The ShortcodeLoginHelpController class
 *
 * Controller for the Login shortcode
 *
 * @since 1.5 before this version, password recovery was displayed using [MyHome.Login lostpassword=recovery]
 */
class ShortcodeLoginHelpController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    return $this->recovery($params);
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
    $this->loadView('shortcodeLoginHelp','MyHomeShortcodes');
  }

  /**
   * Allows the client to recover his/her access details by quering the API with the clientrecovery command
   *
   * @since 1.2 added a return value
   * @uses  MyHomeApi::post()
   * @param string[] POST parameters received
   * @return bool whether the recovery was successful
   */
  private function recovery(array $params){
    list($username,$jobNumber,$surname,$streetNumber,$streetName,$suburb,$postcode,$name,$phone,$email,$captcha)=
      $this->extractParams(['myHomeUsername',
        'myHomeJobNumber',
        'myHomeSurname',
        'myHomeStreetNumber',
        'myHomeStreetName',
        'myHomeSuburb',
        'myHomePostcode',
        'myHomeName',
        'myHomePhone',
        'myHomeEmail',
        'myHomeCaptcha'],$params);

    // Keep the variable name to recoveryError - this would avoid conflicts with a login view in the same page
    if(empty($_SESSION['captcha'])||$captcha!==$_SESSION['captcha']){
      $this->flashVar('recoveryError',__('Please enter the correct Captcha Word','myHome'));

      return false;
    }

    $params=['username'=>$username,
      'job'=>$jobNumber,
      'surname'=>$surname,
      'streetno'=>$streetNumber,
      'streetname'=>$streetName,
      'suburb'=>$suburb,
      'postcode'=>$postcode,
      'contactname'=>$name,
      'contactphone'=>$phone,
      'contactemail'=>$email];

    $recoveryResponse=myHome()->api->post('clientrecovery',$params,null,false);

    $successMessage='';
    $errorMessage='';

    if($recoveryResponse!==null)
      // If status=OK, the attempt is successful
      if(!empty($recoveryResponse->status)&&$recoveryResponse->status==200&&!empty($recoveryResponse->message))
        $successMessage=$recoveryResponse->message;
      else if(!empty($recoveryResponse->error))
        $errorMessage=$recoveryResponse->error;

    // If no error message was received, return a default message
    if(!$successMessage&&!$errorMessage)
      $errorMessage=__('Recovery unsuccessful','myHome');

    if($successMessage)
      $this->flashVar('recoveryMessage',$successMessage);
    else if($errorMessage){
      $this->flashVar('recoveryError',$errorMessage);

      return false;
    }

    return true;
  }
}
