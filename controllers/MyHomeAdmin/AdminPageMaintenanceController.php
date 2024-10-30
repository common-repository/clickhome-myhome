<?php

/**
 * The AdminPageMaintenanceController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageMaintenanceController'))
  return;

/**
 * The AdminPageMaintenanceController class
 *
 * Controller for the Maintenance admin page view
 *
 * @since 1.2
 */
class AdminPageMaintenanceController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeMaintenance';

  /**
   * Maintenance pages shortcodes and names
   *
   * <p>
   * <ul>
   * <li>Array key: page code</li>
   * <li>shortcode: page shortcode</li>
   * <li>name: page name (used in the admin page)</li>
   * </ul>
   * </p>
   * <p>This array is filled up later in the constructor, as it needs to use gettext's __() method</p>
   */
  protected static $MAINTENANCE_PAGES=[];

  public function __construct(){
    parent::__construct();

    self::$MAINTENANCE_PAGES=['confirmation'=>['shortcode'=>'MaintenanceConfirmation',
      'name'=>_x('Confirmation','Maintenance admin page','myHome'),
      'required'=>false],
      'request'=>['shortcode'=>'MaintenanceRequest',
        'name'=>_x('Request','Maintenance admin page','myHome'),
        'required'=>true],
      'issues'=>['shortcode'=>'MaintenanceIssues',
        'name'=>_x('Issues','Maintenance admin page','myHome'),
        'required'=>true],
      'review'=>['shortcode'=>'MaintenanceReview',
        'name'=>_x('Review','Maintenance admin page','myHome'),
        'required'=>false],
      'confirmed'=>['shortcode'=>'MaintenanceConfirmed',
        'name'=>_x('Confirmed','Maintenance admin page','myHome'),
        'required'=>true]];
  }

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $maintenancePages=myHome()->options->getMaintenancePages();
    $suggestedMaintenancePages=$this->detectMaintenancePages();

    $maintenanceMaxFileSize=myHome()->options->getMaintenanceMaxFileSize();

    $maintenancePagesDetails=static::$MAINTENANCE_PAGES;

    $this->loadView('adminPageMaintenance','MyHomeAdmin',
      compact('maintenancePages','suggestedMaintenancePages','maintenanceMaxFileSize','maintenancePagesDetails'));
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    list($maintenancePages,$maintenanceMaxFileSize)=$this->extractParams(['myHomeMaintenancePages',
      'myHomeMaintenanceMaxFileSize'],$params);

    // Filter and typecast the settings received as needed
    $maintenancePages=array_map('intval',$maintenancePages);
    $maintenanceMaxFileSize=(float)$maintenanceMaxFileSize;

    $error=false;

    foreach(self::$MAINTENANCE_PAGES as $page=>$pageSettings)
      if($pageSettings['required']&&empty($maintenancePages[$page])){
        $this->flashVar('error',__('All the required Maintenance Pages must be provided','myHome'));
        $error=true;

        break;
      }

    if(!$error&&!$maintenanceMaxFileSize){
      $this->flashVar('error',__('The maximum file size provided is invalid','myHome'));
      $error=true;
    }

    // If no errors are found, save the options set by the user
    if(!$error){
      $options=myHome()->options;

      $options->setMaintenancePages($maintenancePages);
      $options->setMaintenanceMaxFileSize($maintenanceMaxFileSize);

      $options->saveAll();

      // Remember that the settings have been successfully saved
      $this->flashVar('saved',true);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  /**
   * Searches for pages containing the different Maintenance shortcodes
   *
   * If more than one page contains a shortcode, it returns the first according to the default get_posts() order (post
   * date descending)
   *
   * @uses MyHomeShortcodes::detect()
   * @return int[] for each page, its ID, if found, or 0 otherwise
   */
  private function detectMaintenancePages(){
    $suggestedMaintenancePages=
      array_combine(array_keys(self::$MAINTENANCE_PAGES),array_fill(0,count(self::$MAINTENANCE_PAGES),0));

    $pages=get_posts(['posts_per_page'=>-1,
      'post_type'=>'page',
      'post_status'=>'publish']);

    // Detect each shortcode in each page
    foreach(self::$MAINTENANCE_PAGES as $maintenancePage=>$pageSettings)
      foreach($pages as $page)
        if(myHome()->shortcodes->detect($page->post_content,$pageSettings['shortcode'])){
          $suggestedMaintenancePages[$maintenancePage]=$page->ID;
          break; // Don't look any further
        }

    return $suggestedMaintenancePages;
  }
}
