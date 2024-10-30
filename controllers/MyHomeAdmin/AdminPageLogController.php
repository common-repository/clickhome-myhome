<?php

/**
 * The AdminPageLogController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageLogController'))
  return;

/**
 * The AdminPageLogController class
 *
 * Controller for the Settings admin page view
 */
class AdminPageLogController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeLog';

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $logStatus=myHome()->log->isWorking();
    $logContents=myHome()->log->read();

    $this->loadView('adminPageLog','MyHomeAdmin',compact('logStatus','logContents'));
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    if(myHome()->log->clear())
      $this->flashVar('cleared',true);
    else{
      $this->flashVar('cleared',false);
      $this->flashVar('error','The log file could not be cleared due to insufficient permissions or non existing file');
    }
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }
}
