<?php

/**
 * The MyHomeAdmin class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeAdmin'))
  return;


/**
 * The MyHomeAdmin class
 *
 * <p>Creates and handles the Settings, Debug Console, and other tabs in the WordPress admin panel</p>
 * <p>Displays admin notices</p>
 */
class MyHomeAdmin{
  public function __construct() { //myHome()->log->info('construct MyHomeAdmin');
    //register_activation_hook(__FILE__, array($this, 'setupHealthCheck' ));
    //register_deactivation_hook(__FILE__, array($this, 'removeHealthCheck' ));
  }

  /**
   * Loads the admin CSS file
   *
   * Triggered by the admin_enqueue_scripts action
   */
  public function onAdminEnqueueScripts(){
    wp_enqueue_style('myhome-admin',MH_URL_STYLES.'/myhome-admin.css');
  }

  /**
   * Checks for a MyHome enabled theme
   *
   * Triggered by the admin_init action
   *
   * @uses current_theme_supports() to check for the "myhome" theme feature
   */
  public function onAdminInit(){
    //if(!current_theme_supports('myhome'))
	if( get_template() != 'astrid' &&
		get_template() != 'clickhome-myhome')
      $this->errors[]=
        __('The current theme does not provide support for ClickHome.MyHome shortcodes. Please install the bundled parent/child theme Astrid/Astrid-MyHome.',
          'myHome');

	/* Body Classes */
	function my_admin_body_class( $c ) {
		return 'theme-' . rawurlencode( get_template() );
	}
	add_filter( 'admin_body_class', 'my_admin_body_class' );
  }

  /**
   * Adds the MyHome options to the admin menu
   *
   * Triggered by the admin_menu action
   */
  public function onAdminMenu(){
    add_menu_page
    (__('MyHome - Settings','myHome'), // Page title
      __('MyHome','myHome'), // Menu title
      'manage_options', // Capability
      'MyHomeSettings', // Menu slug
      [$this,'showAdminPageSettings'], // Callback function
      MH_URL_IMAGES.'/icon.png'); // Icon URL

    add_submenu_page
    ('MyHomeSettings', // Parent menu slug
      __('MyHome - Settings','myHome'), // Page title
      __('Settings','myHome'), // Menu title
      'manage_options', // Capability
      'MyHomeSettings', // Menu slug
      [$this,'showAdminPageSettings']); // Callback function

    add_submenu_page
    ('MyHomeSettings',
      __('MyHome - Debug Console','myHome'),
      __('Debug Console','myHome'),
      'manage_options',
      'MyHomeDebugConsole',
      [$this,'showAdminPageDebugConsole']);

    add_submenu_page
    ('MyHomeSettings',
      __('MyHome - Contact Form','myHome'),
      __('Contact Form','myHome'),
      'manage_options',
      'MyHomeContactForm',
      [$this,'showAdminPageContactForm']);

    add_submenu_page
    ('MyHomeSettings',
      __('MyHome - Maintenance','myHome'),
      __('Maintenance','myHome'),
      'manage_options',
      'MyHomeMaintenance',
      [$this,'showAdminPageMaintenance']);

    add_submenu_page
    ('MyHomeSettings',
      __('MyHome - Advertising','myHome'),
      __('Advertising','myHome'),
      'manage_options',
      'MyHomeAdvertising',
      [$this,'showAdminPageAdvertising']);

    add_submenu_page
    ('MyHomeSettings',
      __('MyHome - Facebook','myHome'),
      __('Facebook','myHome'),
      'manage_options',
      'MyHomeFacebook',
      [$this,'showAdminPageFacebook']);

    add_submenu_page
    ('MyHomeSettings',
      __('MyHome - Tender','myHome'),
      __('Tender','myHome'),
      'manage_options',
      'MyHomeTender',
      [$this,'showAdminPageTender']);

    try{
      if(myHome()->options->isLogEnabled())
        add_submenu_page
        ('MyHomeSettings',
          __('MyHome - Log','myHome'),
          __('Log','myHome'),
          'manage_options',
          'MyHomeLog',
          [$this,'showAdminPageLog']);
    }
    catch(MyHomeException $e){
      myHome()->handleError($e);
    }
  }

  /**
   * Displays any error message set by onAdminInit()
   *
   * Triggered by the admin_notices action
   *
   * @see MyHomeAdmin::onAdminInit()
   */
  public function onAdminNotices(){
    if(!$this->errors)
      return;

    echo "<div id=\"message\" class=\"updated fade\">\n";

    foreach($this->errors as $error)
      printf("  <p>%s</p>\n",$error);

    echo "</div>\n";
  }

  /**
   * Generates the URL for an admin page (eg "http://website.com.au/wp-admin/admin.php?page=MyHomeDebugConsole" for
   * "MyHomeDebugConsole")
   *
   * @uses MyHomeAdmin::url()
   * @param string $page the page parameter value
   * @return string the admin page URL
   */
  public function pageUrl($page){
    return $this->url(add_query_arg('page',$page,'admin.php'));
  }

  /**
   * Registers the appropriate WordPress hooks
   *
   * The following hooks are registered:
   * <ul>
   * <li>admin_menu: appends the MyHome options into the menu</li>
   * <li>admin_enqueue_scripts: enqueues the CSS file used by the admin pages</li>
   * <li>admin_init: performs certain checks when accessing the admin area</li>
   * <li>admin_notices: displays admin notices based on the checks done at onAdminInit</li>
   * </ul>
   */
  public function setupHooks(){
    add_action('admin_menu',[$this,'onAdminMenu']);
    add_action('admin_enqueue_scripts',[$this,'onAdminEnqueueScripts']);
    add_action('admin_init',[$this,'onAdminInit']);
    add_action('admin_notices',[$this,'onAdminNotices']);

    // HealthCheck Hooks
    //add_action('MyHome_HealthCheck', array($this, 'healthCheck'));
  }

  /**
  * Send MyHome Instance info to ClickHome
  
  public static function healthCheck() { myHome()->log->info('Do HealthCheck: ' . get_option('myhome_guid'));
    return myHome()->api->post('healthCheck', array(
      'version' => MH_VERSION,
      'guid' => get_option('myhome_guid')
    ), myHome()->api->authenticationHeadersApiKey(myHome()->options->getAdvertisingApiKey()));
  }

  public static function setupHealthCheck() { myHome()->log->info('setupHealthCheck ' . get_option('myhome_guid'));
    if(!get_option('myhome_guid')) {
      $guid = myHome()->helpers->create_guid(); myHome()->log->info('GUID: ' . $guid);
      add_option('myhome_guid', $guid); // add_option won't overwrite if it already exists
    }

    if (! wp_next_scheduled ( 'MyHome_HealthCheck' )) { myHome()->log->info('scheduleHealthCheck for guid:' . get_option('myhome_guid'));
	    wp_schedule_event(time(), 'hourly', 'MyHome_HealthCheck');
    }
  }

  public static function removeHealthCheck() { myHome()->log->info('removeHealthCheck');
	  wp_clear_scheduled_hook('MyHome_HealthCheck');
  //  remove_action('MyHome_HealthCheck');
  }*/

  /**
   * Shows the Advertising page
   *
   * @since 1.3
   */
  public function showAdminPageAdvertising(){
    $this->showAdminPage('AdminPageAdvertisingController');
  }

  /**
   * Shows the Contact Form page
   *
   * @since 1.1
   */
  public function showAdminPageContactForm(){
    $this->showAdminPage('AdminPageContactFormController');
  }

  /**
   * Shows the Debug Console page
   */
  public function showAdminPageDebugConsole(){
    $this->showAdminPage('AdminPageDebugConsoleController');
  }

  /**
   * Shows the Facebook page
   *
   * @since 1.4
   */
  public function showAdminPageFacebook(){
    $this->showAdminPage('AdminPageFacebookController');
  }

  /**
   * Shows the Log page
   */
  public function showAdminPageLog(){
    $this->showAdminPage('AdminPageLogController');
  }

  /**
   * Shows the Maintenace page
   *
   * @since 1.2
   */
  public function showAdminPageMaintenance(){
    $this->showAdminPage('AdminPageMaintenanceController');
  }

  /**
   * Shows the Settings page
   */
  public function showAdminPageSettings(){
    $this->showAdminPage('AdminPageSettingsController');
  }

  /**
   * Shows the Tender page
   *
   * @since 1.5
   */
  public function showAdminPageTender(){
    $this->showAdminPage('AdminPageTenderController');
  }

  /**
   * Generates the appropriate admin URL for a given path
   *
   * @param string $path the path to generate the URL for
   * @return string the admin URL
   */
  public function url($path){
    return admin_url($path,'admin');
  }

  /**
   * Shows an admin page
   *
   * POST forms in these pages are handled by MyHomeAdminPostHandler::handleAction(), which redirects the browser back
   * to the same page
   *
   * @uses MyHome::runController()
   * @param string $controller the controller class name within the MyHomeAdmin subdirectory
   */
  private function showAdminPage($controller){
    try{
      // These requests do not expect any parameters
      myHome()->runController($controller,'GET');
    }
    catch(MyHomeException $e){
      myHome()->handleError($e);
    }
  }

  /**
   * Errors to be displayed as admin notices
   *
   * @var string[]
   */
  private $errors=[];
}

//register_activation_hook(__FILE__, array('MyHomeAdmin', 'setupHealthCheck'));