<?php

/**
 * The AdminPageFacebookController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 * @since      1.4
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageFacebookController'))
  return;

/**
 * The AdminPageFacebookController class
 *
 * Controller for the Facebook admin page view
 *
 * @since 1.4
 */
class AdminPageFacebookController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeFacebook';

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $options=myHome()->options;

    $appId=$options->getFacebookAppId();
    $appSecret=$options->getFacebookAppSecret();

    if(myHome()->facebook->appSetUp())
      $appDetails=myHome()->facebook->testApp();
    else
      $appDetails=null;

    $this->loadView('adminPageFacebook','MyHomeAdmin',compact('appId','appSecret','appDetails'));
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    list($appId,$appSecret)=$this->extractParams(['myHomeAppId','myHomeAppSecret'],$params);

    // Filter and typecast the settings received as needed
    $appId=$this->filterText($appId,50);
    $appSecret=$this->filterText($appSecret,100);

    $options=myHome()->options;

    $options->setFacebookAppId($appId);
    $options->setFacebookAppSecret($appSecret);

    $options->saveAll();

    // Remember the fact that the settings were successfully saved
    $this->flashVar('saved',true);
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }
}
