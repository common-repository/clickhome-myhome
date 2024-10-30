<?php

/**
 * The AdminPageContactFormController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 * @since      1.1
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageContactFormController'))
  return;

/**
 * The AdminPageContactFormController class
 *
 * Controller for the Contact Form admin page view
 *
 * @since 1.1
 */
class AdminPageContactFormController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeContactForm';

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $this->loadView('adminPageContactForm','MyHomeAdmin');
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
}
