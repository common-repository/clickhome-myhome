<?php

/**
 * The MyHomeOptions class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeOptions'))
  return;

/**
 * The MyHomeOptions class
 *
 * Reads and modifies MyHome options
 */
class MyHomeOptions{
  /**
   * Error logging level
   */
  public static $LOG_LEVEL_ERROR=1;

  /**
   * Info logging level
   */
  public static $LOG_LEVEL_INFO=2;

  /**
   * File logging method
   *
   * @since 1.3
   */
  public static $LOG_METHOD_FILE=1;

  /**
   * Option logging method
   *
   * @since 1.3
   */
  public static $LOG_METHOD_OPTION=2;

  /**
   * Prefix used before all the options (eg myhome_endpoint)
   */
  private static $PREFIX='myhome_';

  /**
   * Option name for the API endpoint base URL
   */
  private static $OPTION_ENDPOINT='endpoint';

  /**
   * Option name for the Login page
   */
  private static $OPTION_LOGIN_PAGE='login_page';

  /**
   * Option name for the Login Help page
   */
  private static $OPTION_LOGIN_HELP_PAGE='login_help_page';
  
  /**
   * Option name for the Reset Password
   */
  private static $OPTION_RESET_PASSWORD_PAGE='reset_password';

  /**
   * Option name for the Logoff menu location
   */
  private static $OPTION_LOGOFF_MENU_LOCATION='logoff_menu_location';

  /**
   * Option name for the Logoff option name
   */
  private static $OPTION_LOGOFF_OPTION_NAME='logoff_option_name';

  /**
   * Option name for the Main page
   */
  private static $OPTION_MAIN_PAGE='main_page';

  /**
   * Option name for the Main logo
   */
  private static $OPTION_MAIN_LOGO='main_logo';

  /**
   * Option name for the BG Image
   */
  private static $OPTION_BG_IMAGE='bg_image';

  /**
   * Option name for the Logging enabled status
   */
  private static $OPTION_LOG_ENABLED='log_enabled';

  /**
   * Option name for the Logging enabled status
   */
  private static $OPTION_LOG_RESPONSES='log_responses';

  /**
   * Option name for the Logging level
   */
  private static $OPTION_LOG_LEVEL='log_level';

  /**
   * Option name for the Contact form API key
   *
   * @since 1.1
   */
  private static $OPTION_CONTACT_API_KEY='contact_api_key';

  /**
   * Option name for the Maintenance pages
   *
   * @since 1.2
   */
  private static $OPTION_MAINTENANCE_PAGES='maintenance_pages';

  /**
   * Option name for the Tender pages
   *
   * @since 1.5
   */
  private static $OPTION_TENDER_PAGES='tender_pages';

  /**
   * Option name for the Maintenance maximum file size
   *
   * @since 1.2
   */
  private static $OPTION_MAINTENANCE_MAX_FILE_SIZE='maintenance_max_file_size';

  /**
   * Option name for the Advertising API key
   *
   * @since 1.3
   */
  private static $OPTION_ADVERTISING_API_KEY='advertising_api_key';

  /**
   * Option name for the Logging method
   *
   * @since 1.3
   */
  private static $OPTION_LOG_METHOD='log_method';

  /**
   * Option name for the Advertising default template for house types
   *
   * @since 1.3
   */
  private static $OPTION_ADVERTISING_DEFAULT_TEMPLATE_HOUSE_TYPES='advertising_default_template_house_types';

  /**
   * Option name for the Advertising default template for displays
   *
   * @since 1.3
   */
  private static $OPTION_ADVERTISING_DEFAULT_TEMPLATE_DISPLAYS='advertising_default_template_displays';

  /**
   * Option name for the Facebook App ID
   *
   * @since 1.4
   */
  private static $OPTION_FACEBOOK_APP_ID='facebook_app_id';

  /**
   * Option name for the Facebook App secret
   *
   * @since 1.4
   */
  private static $OPTION_FACEBOOK_APP_SECRET='facebook_app_secret';

  /**
   * Option name for the "Skip tender list if only one tender" setting
   *
   * @since 1.5
   */
  private static $OPTION_TENDER_SKIP_LIST='tender_skip_list';

  /**
   * Option name for the "Skip selection overview" setting
   *
   * @since 1.5
   */
  private static $OPTION_TENDER_SKIP_SELECTION_OVERVIEW='tender_skip_selection_overview';

  /**
   * Option name for "Tender Variation Declaration" setting
   *
   * @since 1.6
   */
  private static $OPTION_TENDER_VARIARTION_DECLARATION='tender_variation_declaration';

  /**
   * Constructor method
   *
   * It loads all the options
   */
  public function __construct(){
    $this->loadAll();
  }

  /**
   * Returns the Advertising API key
   *
   * @since 1.3
   * @return string the Advertising API key
   */
  public function getAdvertisingApiKey(){
    return $this->advertisingApiKey;
  }

  /**
   * Sets the Advertising API key
   *
   * @since 1.3
   * @param string $advertisingApiKey the Advertising API key
   */
  public function setAdvertisingApiKey($advertisingApiKey){
    $this->advertisingApiKey=$advertisingApiKey?:'';
  }

  /**
   * Returns the Advertising default template for displays
   *
   * @since 1.3
   * @return string the Advertising default template for displays
   */
  public function getAdvertisingDefaultTemplateDisplays(){
    return $this->advertisingDefaultTemplateDisplays;
  }

  /**
   * Sets the Advertising default template for displays
   *
   * @since 1.3
   * @param string $advertisingDefaultTemplateDisplays the Advertising default template for displays
   */
  public function setAdvertisingDefaultTemplateDisplays($advertisingDefaultTemplateDisplays){
    $this->advertisingDefaultTemplateDisplays=$advertisingDefaultTemplateDisplays?:'';
  }

  /**
   * Returns the Advertising default template for house types
   *
   * @since 1.3
   * @return string the Advertising default template for house types
   */
  public function getAdvertisingDefaultTemplateHouseTypes(){
    return $this->advertisingDefaultTemplateHouseTypes;
  }

  /**
   * Sets the Advertising default template for house types
   *
   * @since 1.3
   * @param string $advertisingDefaultTemplateHouseTypes the Advertising default template for house types
   */
  public function setAdvertisingDefaultTemplateHouseTypes($advertisingDefaultTemplateHouseTypes){
    $this->advertisingDefaultTemplateHouseTypes=$advertisingDefaultTemplateHouseTypes?:'';
  }

  /**
   * Returns the Contact form API key
   *
   * @since 1.1
   * @return string the Contact form API key
   */
  public function getContactApiKey(){
    return $this->contactApiKey;
  }

  /**
   * Sets the Contact form API key
   *
   * @since 1.1
   * @param string $contactApiKey the Contact form API key
   */
  public function setContactApiKey($contactApiKey){
    $this->contactApiKey=$contactApiKey?:'';
  }

  /**
   * Returns the API endpoint base URL
   *
   * @return string the API endpoint base URL
   */
  public function getEndpoint(){
    return trailingslashit($this->endpoint);
  }

  /**
   * Sets the API endpoint base URL
   *
   * @param string $endpoint the API endpoint base URL
   */
  public function setEndpoint($endpoint){
    $this->endpoint=$endpoint?:'';
  }

  /**
   * Returns the Facebook App ID
   *
   * @since 1.4
   * @return string the Facebook App ID
   */
  public function getFacebookAppId(){
    return $this->facebookAppId;
  }

  /**
   * Sets the Facebook App ID
   *
   * @since 1.4
   * @param string $facebookAppId the Facebook App ID
   */
  public function setFacebookAppId($facebookAppId){
    $this->facebookAppId=$facebookAppId?:'';
  }

  /**
   * Returns the Facebook App secret
   *
   * @since 1.4
   * @return string the Facebook App secret
   */
  public function getFacebookAppSecret(){
    return $this->facebookAppSecret;
  }

  /**
   * Sets the Facebook App secret
   *
   * @since 1.4
   * @param string $facebookAppSecret the Facebook App secret
   */
  public function setFacebookAppSecret($facebookAppSecret){
    $this->facebookAppSecret=$facebookAppSecret?:'';
  }

  /**
   * Returns the Log level
   *
   * @return int the Log level
   */
  public function getLogLevel(){
    return $this->logLevel;
  }

  /**
   * Sets the Log level
   *
   * @param int $logLevel the Log level
   */
  public function setLogLevel($logLevel){
    if($logLevel<self::$LOG_LEVEL_ERROR)
      $logLevel=self::$LOG_LEVEL_ERROR;
    else if($logLevel>self::$LOG_LEVEL_INFO)
      $logLevel=self::$LOG_LEVEL_INFO;

    $this->logLevel=(int)$logLevel;
  }

  /**
   * Returns the Log method
   *
   * @since 1.3
   * @return int the Log method
   */
  public function getLogMethod(){
    return $this->logMethod;
  }

  /**
   * Sets the Log method
   *
   * @since 1.3
   * @param int $logMethod the Log method
   */
  public function setLogMethod($logMethod){
    if(!in_array($logMethod,[self::$LOG_METHOD_FILE,self::$LOG_METHOD_OPTION]))
      $logMethod=self::$LOG_METHOD_FILE;

    $this->logMethod=(int)$logMethod;
  }

  /**
   * Returns the Login page ID
   *
   * @return int the Login page ID
   */
  public function getLoginPage(){
	  return $this->loginPage;
  }

  /**
   * Sets the Login page ID
   *
   * @param int $loginPage the Login page ID
   */
  public function setLoginPage($loginPage){
	  $this->loginPage=(int)$loginPage;
  }

  /**
   * Returns the ResetPassword page ID
   *
	 * @return int the ResetPassword page ID
   */
  public function getResetPasswordPage(){
	  return $this->resetPasswordPage;
  }

  /**
   * Sets the ResetPassword page ID
   *
	 * @param int $resetPasswordPage the ResetPassword page ID
   */
  public function setResetPasswordPage($resetPasswordPage){
	  $this->resetPasswordPage=(int)$resetPasswordPage;
  }

  /**
   * Returns the Logoff menu location
   *
   * @return string the Logoff menu location
   */
  public function getLogoffMenuLocation(){
    return $this->logoffMenuLocation;
  }

  /**
   * Sets the Logoff menu location
   *
   * @param string $logoffMenuLocation the Logoff menu location
   */
  public function setLogoffMenuLocation($logoffMenuLocation){
    $this->logoffMenuLocation=$logoffMenuLocation?:'';
  }

  /**
   * Returns the Logoff option name
   *
   * @return string the Logoff option name
   */
  public function getLogoffOptionName(){
    return $this->logoffOptionName;
  }

  /**
   * Sets the Logoff option name
   *
   * @param string $logoffOptionName the Logoff option name
   */
  public function setLogoffOptionName($logoffOptionName){
    $this->logoffOptionName=$logoffOptionName?:'';
  }

  /**
   * Returns the Main page ID
   *
   * @return int the Main page ID
   */
  public function getMainPage(){
    return $this->mainPage;
  }

  /**
   * Sets the Main page ID
   *
   * @param int $mainPage the Main page ID
   */
  public function setMainPage($mainPage){
    $this->mainPage=(int)$mainPage;
  }

  /**
   * Returns the Main logo image
   *
   * @return string the Main logo image src
   */
  public function getMainLogo(){
	  return $this->mainLogo;
  }

  /**
   * Sets the Main logo image
   *
   * @return string the Main logo image src
   */
  public function setMainLogo($mainLogo){
	  $this->mainLogo=(string)$mainLogo?:'';
	  //var_dump($this->mainLogo);
  }

  /**
   * Returns the Main background image
   *
   * @return string the Main bg image src
   */
  public function getBgImage(){
	  return $this->bgImage;
  }

  /**
   * Sets the Main background image
   *
   * @return string the Main bg image src
   */
  public function setBgImage($bgImage){
	  $this->bgImage=(string)$bgImage?:'';
  }

  /**
   * Returns the Maintenance maximum file size
   *
   * @since 1.2
   * @return float the Maintenance maximum file size, in MiB
   */
  public function getMaintenanceMaxFileSize(){
    return $this->maintenanceMaxFileSize;
  }

  /**
   * Sets the Maintenance maximum file size
   *
   * @since 1.2
   * @param float $maintenanceMaxFileSize the Maintenance maximum file size, in MiB
   */
  public function setMaintenanceMaxFileSize($maintenanceMaxFileSize){
    $this->maintenanceMaxFileSize=(float)$maintenanceMaxFileSize;
  }

  /**
   * Returns the Maintenance page IDs
   *
   * @since 1.2
   * @return int[] the Maintenance page IDs
   */
  public function getMaintenancePages(){
    return $this->maintenancePages;
  }

  /**
   * Sets the Maintenance page IDs
   *
   * @since 1.2
   * @param int[]|null $maintenancePages the Maintenance page IDs
   */
  public function setMaintenancePages($maintenancePages){
    if(is_array($maintenancePages))
      $this->maintenancePages=$maintenancePages;
    else
      $this->maintenancePages=[];
  }

  /**
   * Returns the Tender page IDs
   *
   * @since 1.5
   * @return int[] the Tender page IDs
   */
  public function getTenderPages(){
    return $this->tenderPages;
  }

  /**
   * Sets the Tender page IDs
   *
   * @since 1.5
   * @param int[]|null $tenderPages the Tender page IDs
   */
  public function setTenderPages($tenderPages){
    if(is_array($tenderPages))
      $this->tenderPages=$tenderPages;
    else
      $this->tenderPages=[];
  }

  /**
   * Whether tender lists with one tender should be skipped
   *
   * @since 1.5
   * @return bool
   */
  public function isTenderSkipList(){
    return $this->tenderSkipList;
  }

  /**
   * Sets the "Skip tender list if only one tender" setting
   *
   * @since 1.5
   * @param bool $tenderSkipList
   */
  public function setTenderSkipList($tenderSkipList){
    $this->tenderSkipList=$tenderSkipList;
  }

  /**
   * Whether tender selection overview should be skipped
   *
   * @since 1.5
   * @return bool
   */
  public function isTenderSkipSelectionOverview(){
    return $this->tenderSkipSelectionOverview;
  }

  /**
   * Sets the "Skip selection overview" setting
   *
   * @since 1.5
   * @param bool $tenderSkipSelectionOverview
   */
  public function setTenderSkipSelectionOverview($tenderSkipSelectionOverview){
    $this->tenderSkipSelectionOverview=$tenderSkipSelectionOverview;
  }

  /**
   * Returns the Tender variation signature declaration text
   *
   * @since 1.6
   * @return string[] the Tender variation signature declaration
   */
  public function getTenderVariationDeclaration(){
    // Default
    if(!isset($this->tenderVariationDeclaration)) {
      $this->tenderVariationDeclaration = '<h4>I certify that:</h4>
<ul>
  <li>The variation options &amp; price are correct.</li>
  <li>I am authorised to submit contract variations.</li>
</ul>
<h4>Signature of authorised person or representative:</h4>';
    }

    return $this->tenderVariationDeclaration;
  }

  /**
   * Sets the Tender variation signature declaration text
   *
   * @since 1.6
   * @param string|null 
   */
  public function setTenderVariationDeclaration($declaration){
      $this->tenderVariationDeclaration = $declaration;
  }

  /**
   * Whether the log is enabled or not
   *
   * @return bool the log enabled status
   */
  public function isLogEnabled(){
    return $this->logEnabled;
  }

  /**
   * Enables or disables the log
   *
   * @param bool $logEnabled whether the log should be enabled or not
   */
  public function setLogEnabled($logEnabled){
    $this->logEnabled=(bool)$logEnabled;
  }

  /**
   * Whether to log responses or not
   *
   * @return bool the log enabled status
   */
  public function isLogResponses(){
    return $this->logResponses;
  }

  /**
   * Enables or disables logging responses
   *
   * @param bool $logResponses whether to log responses
   */
  public function setLogResponses($logResponses){
    $this->logResponses=(bool)$logResponses;
  }

  /**
   * Saves all the options into the WordPress database
   */
  public function saveAll(){
    $this->update(self::$OPTION_ENDPOINT,$this->getEndpoint());
    $this->update(self::$OPTION_LOGIN_PAGE,$this->getLoginPage());
    $this->update(self::$OPTION_RESET_PASSWORD_PAGE,$this->getResetPasswordPage());
    $this->update(self::$OPTION_LOGOFF_MENU_LOCATION,$this->getLogoffMenuLocation());
    $this->update(self::$OPTION_LOGOFF_OPTION_NAME,$this->getLogoffOptionName());
    $this->update(self::$OPTION_MAIN_PAGE,$this->getMainPage());
    $this->update(self::$OPTION_MAIN_LOGO,$this->getMainLogo());
    $this->update(self::$OPTION_BG_IMAGE,$this->getBgImage());
    $this->update(self::$OPTION_LOG_ENABLED,$this->isLogEnabled());
    $this->update(self::$OPTION_LOG_RESPONSES,$this->isLogResponses());
    $this->update(self::$OPTION_LOG_LEVEL,$this->getLogLevel());
    $this->update(self::$OPTION_CONTACT_API_KEY,$this->getContactApiKey());
    $this->update(self::$OPTION_MAINTENANCE_PAGES,$this->getMaintenancePages());
    $this->update(self::$OPTION_MAINTENANCE_MAX_FILE_SIZE,$this->getMaintenanceMaxFileSize());
    $this->update(self::$OPTION_ADVERTISING_API_KEY,$this->getAdvertisingApiKey());
    $this->update(self::$OPTION_LOG_METHOD,$this->getLogMethod());
    $this->update(self::$OPTION_ADVERTISING_DEFAULT_TEMPLATE_HOUSE_TYPES,$this->getAdvertisingDefaultTemplateHouseTypes());
    $this->update(self::$OPTION_ADVERTISING_DEFAULT_TEMPLATE_DISPLAYS,$this->getAdvertisingDefaultTemplateDisplays());
    $this->update(self::$OPTION_FACEBOOK_APP_ID,$this->getFacebookAppId());
    $this->update(self::$OPTION_FACEBOOK_APP_SECRET,$this->getFacebookAppSecret());
    $this->update(self::$OPTION_TENDER_PAGES,$this->getTenderPages());
    $this->update(self::$OPTION_TENDER_SKIP_LIST,$this->isTenderSkipList());
    $this->update(self::$OPTION_TENDER_SKIP_SELECTION_OVERVIEW,$this->isTenderSkipSelectionOverview());
    $this->update(self::$OPTION_TENDER_VARIARTION_DECLARATION,$this->getTenderVariationDeclaration());
  }

  /**
   * Loads all the options from the WordPress database
   */
  private function loadAll(){
    $this->setEndpoint($this->option(self::$OPTION_ENDPOINT));
    $this->setLoginPage($this->option(self::$OPTION_LOGIN_PAGE));
    $this->setResetPasswordPage($this->option(self::$OPTION_RESET_PASSWORD_PAGE));
    $this->setLogoffMenuLocation($this->option(self::$OPTION_LOGOFF_MENU_LOCATION));
    $this->setLogoffOptionName($this->option(self::$OPTION_LOGOFF_OPTION_NAME));
    $this->setMainPage($this->option(self::$OPTION_MAIN_PAGE));
    $this->setMainLogo($this->option(self::$OPTION_MAIN_LOGO));
    $this->setBgImage($this->option(self::$OPTION_BG_IMAGE));
    $this->setLogEnabled($this->option(self::$OPTION_LOG_ENABLED));
    $this->setLogResponses($this->option(self::$OPTION_LOG_RESPONSES));
    $this->setLogLevel($this->option(self::$OPTION_LOG_LEVEL));
    $this->setContactApiKey($this->option(self::$OPTION_CONTACT_API_KEY));
    $this->setMaintenancePages($this->option(self::$OPTION_MAINTENANCE_PAGES));
    $this->setMaintenanceMaxFileSize($this->option(self::$OPTION_MAINTENANCE_MAX_FILE_SIZE, 1.00)); // This option has a default value of 1.00 MiB
    $this->setAdvertisingApiKey($this->option(self::$OPTION_ADVERTISING_API_KEY));
    $this->setLogMethod($this->option(self::$OPTION_LOG_METHOD));
    $this->setAdvertisingDefaultTemplateHouseTypes($this->option(self::$OPTION_ADVERTISING_DEFAULT_TEMPLATE_HOUSE_TYPES));
    $this->setAdvertisingDefaultTemplateDisplays($this->option(self::$OPTION_ADVERTISING_DEFAULT_TEMPLATE_DISPLAYS));
    $this->setFacebookAppId($this->option(self::$OPTION_FACEBOOK_APP_ID));
    $this->setFacebookAppSecret($this->option(self::$OPTION_FACEBOOK_APP_SECRET));
    $this->setTenderPages($this->option(self::$OPTION_TENDER_PAGES));
    $this->setTenderSkipList($this->option(self::$OPTION_TENDER_SKIP_LIST));
    $this->setTenderSkipSelectionOverview($this->option(self::$OPTION_TENDER_SKIP_SELECTION_OVERVIEW));
    $this->setTenderVariationDeclaration($this->option(self::$OPTION_TENDER_VARIARTION_DECLARATION));
  }

  /**
   * Loads an option from the WordPress database
   *
   * @param string $name    the option name
   * @param mixed  $default the default value (Optional - default null)
   * @return mixed the option value
   */
  private function option($name,$default=null){
    //echo($name . ': ' . var_dump(get_option(self::$PREFIX.$name,$default)));
    return get_option(self::$PREFIX.$name,$default);
  }

  /**
   * Saves an option into the WordPress database
   *
   * @param string $name  the option name
   * @param mixed  $value the option value
   * @return bool whether the option was updated or not
   */
  private function update($name,$value){
    return update_option(self::$PREFIX.$name,$value);
  }

  /**
   * API endpoint base URL (eg http://endpoint.com.au)
   *
   * @var string
   */
  private $endpoint;

  /**
   * Login page ID (0 if no page is set)
   *
   * @var int
   */
  private $loginPage;

  /**
   * Forgot Password page ID (0 if no page is set)
   *
   * @var int
   */
  private $resetPasswordPage;

  /**
   * Logoff menu location (eg "main_menu")
   *
   * @var string
   */
  private $logoffMenuLocation;

  /**
   * Logoff option name (eg "Logoff")
   *
   * @var string
   */
  private $logoffOptionName;

  /**
   * Main page ID (0 if no page is set)
   *
   * @var int
   */
  private $mainPage;

  /**
   * Logo image
   *
   * @var string 
   */
  private $mainLogo;

  /**
   * Background image
   *
   * @var string ../../../plugins/ClickHome-MyHome/images/bg1.jpg
   */
  private $bgImage;

  /**
   * Whether the log is enabled or not
   *
   * @var bool
   */
  private $logEnabled;

  /**
   * Whether to log responses
   *
   * @var bool
   */
  private $logResponses;

  /**
   * Logging level
   *
   * @var int
   * @see MyHomeOptions::$LOG_LEVEL_ERROR
   * @see MyHomeOptions::$LOG_LEVEL_INFO
   */
  private $logLevel;

  /**
   * Contact form API key
   *
   * @since 1.1
   * @var string
   */
  private $contactApiKey;

  /**
   * Maintenance page IDs
   *
   * @since 1.2
   * @var int[]
   */
  private $maintenancePages;

  /**
   * Maintenance maximum file size
   *
   * @since 1.2
   * @var float
   */
  private $maintenanceMaxFileSize;

  /**
   * Advertising API key
   *
   * @since 1.3
   * @var string
   */
  private $advertisingApiKey;

  /**
   * Logging method
   *
   * @since 1.3
   * @var int
   * @see   MyHomeOptions::$LOG_METHOD_FILE
   * @see   MyHomeOptions::$LOG_METHOD_OPTION
   */
  private $logMethod;

  /**
   * Advertising default template for house types
   *
   * @since 1.3
   * @var string
   */
  private $advertisingDefaultTemplateHouseTypes;

  /**
   * Advertising default template for displays
   *
   * @since 1.3
   * @var string
   */
  private $advertisingDefaultTemplateDisplays;

  /**
   * Facebook App ID
   *
   * @since 1.4
   * @var string
   */
  private $facebookAppId;

  /**
   * Facebook App secret
   *
   * @since 1.4
   * @var string
   */
  private $facebookAppSecret;

  /**
   * Tender page IDs
   *
   * @since 1.5
   * @var int[]
   */
  private $tenderPages;

  /**
   * "Skip tender list if only one tender" setting
   *
   * @since 1.5
   * @var bool
   */
  private $tenderSkipList;

  /**
   * "Skip selection overview" setting
   *
   * @since 1.5
   * @var bool
   */
  private $tenderSkipSelectionOverview;

  /**
   * Tender Variation Signature Declaration
   *
   * @since 1.6
   * @var string
   */
  private $tenderVariationDeclaration;
}
