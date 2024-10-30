<?php

/**
 * The AdminPageSettingsController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageSettingsController'))
  return;

/**
 * The AdminPageSettingsController class
 *
 * Controller for the Settings admin page view
 */
class AdminPageSettingsController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeSettings';

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $options=myHome()->options;

    $endpoint=$options->getEndpoint();

    $loginPage=$options->getLoginPage();
    $resetPasswordPage=$options->getResetPasswordPage();

    $logoffMenuLocation=$options->getLogoffMenuLocation();
    $logoffOptionName=$options->getLogoffOptionName();
	
    $mainPage=$options->getMainPage();
    $mainLogo=$options->getMainLogo();
    $bgImage=$options->getBgImage();

    $logEnabled=$options->isLogEnabled();
    $logLevel=$options->getLogLevel();
    $logMethod=$options->getLogMethod();
    $logResponses=$options->isLogResponses();

    $contactApiKey=$options->getContactApiKey();
    $advertisingApiKey=$options->getAdvertisingApiKey();
	
    $suggestedLoginPage=$this->detectLoginPage();
    $suggestedResetPasswordPage=$this->detectResetPasswordPage();

    $requirements=$this->checkRequirements();

    $this->loadView('adminPageSettings','MyHomeAdmin',
      compact('endpoint','loginPage','resetPasswordPage','logoffMenuLocation','logoffOptionName','mainPage','mainLogo','bgImage','logEnabled','logLevel','logResponses',
        'logMethod','contactApiKey','advertisingApiKey','suggestedLoginPage','suggestedResetPasswordPage','requirements'));
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    list($endpoint,$loginPage,$resetPasswordPage,$logoffMenuLocation,$logoffOptionName,$mainPage,$mainLogo,$bgImage,$logEnabled,$logLevel,$logMethod,$logResponses,
      $contactApiKey,$advertisingApiKey)=$this->extractParams(['myHomeApiEndpoint',
      'myHomeLoginPage',
      'myHomeResetPasswordPage',
      'myHomeLogoffMenuLocation',
      'myHomeLogoffOptionName',
      'myHomeMainPage',
      'myHomeMainLogo',
      'myHomeBgImage',
      'myHomeLogEnabled',
      'myHomeLogLevel',
      'myHomeLogMethod',
      'myHomeLogResponses',
      'myHomeContactApiKey',
      'myHomeAdvertisingApiKey'],$params);

    // Filter and typecast the settings received as needed
    $endpoint=$this->filterText($endpoint,255);
    $loginPage=(int)$loginPage;
    $resetPasswordPage=(int)$resetPasswordPage;
    $logoffOptionName=$this->filterText($logoffOptionName,50);
    $logEnabled=(bool)$logEnabled;
    $logLevel=(int)$logLevel;
    $logMethod=(int)$logMethod;
    $logResponses=(bool)$logResponses;
    $contactApiKey=$this->filterText($contactApiKey,36);
    $advertisingApiKey=$this->filterText($advertisingApiKey,36);

//var_dump($endpoint);
//$this->flashVar('error',$endpoint);
//return;

    if($endpoint!==''&&!$this->validateUrl($endpoint))
      $this->flashVar('error',__('The URL provided is invalid','myHome'));
    else if($loginPage===0)
      $this->flashVar('error',__('A valid Login Page must be provided','myHome'));
    else if(($logoffMenuLocation==='')!==($logoffOptionName===''))
      $this->flashVar('error',__('Setting a Logoff Option requires a Menu Location and an Option Name','myHome'));
    else if($contactApiKey&&!preg_match('|[\da-fA-F]{8}\-[\da-fA-F]{4}\-[\da-fA-F]{4}\-[\da-fA-F]{4}\-[\da-fA-F]{12}|',$contactApiKey))
      $this->flashVar('error',__('The Contact Form API key provided is invalid','myHome'));
    else if($advertisingApiKey&&
      !preg_match('|[\da-fA-F]{8}\-[\da-fA-F]{4}\-[\da-fA-F]{4}\-[\da-fA-F]{4}\-[\da-fA-F]{12}|',$advertisingApiKey)
    )
      $this->flashVar('error',__('The Advertising API key provided is invalid','myHome'));

    // If no errors are found, save the options set by the user
    else{
      $options=myHome()->options;

      // Clear the log if the method has changed
      if($options->getLogMethod()!==$logMethod)
        myHome()->log->clear();

      $options->setEndpoint($endpoint);
      $options->setLoginPage($loginPage);
      $options->setResetPasswordPage($resetPasswordPage);
      $options->setLogoffMenuLocation($logoffMenuLocation);
      $options->setLogoffOptionName($logoffOptionName);
      $options->setMainPage($mainPage);
      $options->setMainLogo($mainLogo);
      $options->setBgImage($bgImage);
      $options->setLogEnabled($logEnabled);
      $options->setLogLevel($logLevel);
      $options->setLogMethod($logMethod);
      $options->setLogResponses($logResponses);
      $options->setContactApiKey($contactApiKey);
      $options->setAdvertisingApiKey($advertisingApiKey);

      $options->saveAll();

      // Remember the fact that the settings were successfully saved
      $this->flashVar('saved',true);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  /**
   * Checks if the minimum requirements are met
   *
   * The returned vlaue of this method is displayed on the Settings tab
   *
   * @return mixed[] the requirements list - each item is composed of:
   * <ul>
   * <li>product: product name (eg "PHP")</li>
   * <li>version: installed version of the product, "Detected" or "Missing"</li>
   * <li>status: whether the requirement is met (boolean)</li>
   * <li>comment: comments about the requirement</li>
   * </ul>
   */
  private function checkRequirements(){
    $requirements=[];

    $requirements[]=['product'=>'PHP',
      'version'=>PHP_VERSION,
      'status'=>version_compare(PHP_VERSION,'5.3.0','>='), // Always true
      'comment'=>__('ClickHome.MyHome requires PHP version 5.3.0 or greater','myHome')];

    global $wp_version;
    $requirements[]=['product'=>'WordPress',
      'version'=>$wp_version,
      'status'=>version_compare($wp_version,'3.9','>='),
      'comment'=>__('ClickHome.MyHome requires WordPress version 3.9 or greater','myHome')];

    $curlDetected=function_exists('curl_init');
    $requirements[]=['product'=>'cURL',
      'version'=>$curlDetected?__('Detected','myHome'):__('Missing','myHome'),
      'status'=>$curlDetected,
      'comment'=>__('ClickHome.MyHome requires the cURL library to perform API requests','myHome')];

    $gdDetected=function_exists('gd_info');
    $requirements[]=['product'=>'GD2',
      'version'=>$gdDetected?__('Detected','myHome'):__('Missing','myHome'),
      'status'=>$gdDetected,
      'comment'=>__('ClickHome.MyHome requires the GD2 library to render the captcha image used in the recovery form',
        'myHome')];

    $mbstringDetected=function_exists('mb_strlen');
    $requirements[]=['product'=>'mbstring',
      'version'=>$mbstringDetected?__('Detected','myHome'):__('Missing','myHome'),
      'status'=>$mbstringDetected,
      'comment'=>__('The Facebook SDK requires the mbstring extension','myHome')];

    return $requirements;
  }

  /**
   * Searches for a page containing the Login shortcode
   *
   * If more than one page contains this shortcode, it returns the first according to the default get_posts() order
   * (post date descending)
   *
   * @uses MyHomeShortcodes::detect()
   * @return int the login page ID, if found, or 0 otherwise
   */
  private function detectLoginPage(){
	  $suggestedLoginPage=0;

	  $pages=get_posts(['posts_per_page'=>-1,
		'post_type'=>'page',
		'post_status'=>'publish']);

	  // Detect the Login shortcode in each page
	  foreach($pages as $page)
		  if(myHome()->shortcodes->detect($page->post_content,'Login')){
			  $suggestedLoginPage=$page->ID;
			  break; // Don't look any further
		  }

	  return $suggestedLoginPage;
  }

  /**
   * Searches for a page containing the ResetPassword shortcode
   *
   * If more than one page contains this shortcode, it returns the first according to the default get_posts() order
   * (post date descending)
   *
   * @uses MyHomeShortcodes::detect()
   * @return int the reset password page ID, if found, or 0 otherwise
   */
  private function detectResetPasswordPage() {
	  $suggestedResetPasswordPage=0;

	  $pages=get_posts(['posts_per_page'=>-1,
		'post_type'=>'page',
		'post_status'=>'publish']);

	  // Detect the Login shortcode in each page
	  foreach($pages as $page)
		  if(myHome()->shortcodes->detect($page->post_content,'ResetPassword')){
			  $suggestedResetPasswordPage=$page->ID;
			  break; // Don't look any further
		  }

	  return $suggestedResetPasswordPage;
  }

  /**
   * Validates a URL
   *
   * @param string $url the URL to validate
   * @return bool whether the URL is valid
   */
  private function validateUrl($url){
    //return preg_match('|^https?://[-a-z0-9+&@#/%?=~_\|!:,.;]*[-a-z0-9+&@#\/%=~_\|]$|i',$url);
    return preg_match('|^https?:\/\/[-a-z0-9+&@#\/%?=~_\|!:,.;]*[-a-z0-9+&@#\/%=~_\|]$|i',$url);
  }
}
