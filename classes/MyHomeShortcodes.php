<?php

/**
 * The MyHomeShortcodes class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeShortcodes'))
  return;

/**
 * The MyHomeShortcodes class
 *
 * Handles MyHome shortcodes inserted anywhere in the site
 */
class MyHomeShortcodes{
  /**
   * Shortcodes prefix
   *
   * For example, if the prefix is "MyHome.", then the complete Login shortcode is "[MyHome.Login]"
   */
  private static $SHORTCODE_PREFIX='MyHome.';

  /**
   * Shortcode definitions
   *
   * <ul>
   * <li>Array key: shortcode name, without prefix (eg "Login")</li>
   * <li>Array value: name of the controller class which handles this shortcode with the doShortcode() method</li>
   * </ul>
   */
  private static $SHORTCODES=[
    'ClientName'=>'ShortcodeClientNameController',
    'Calendar'=>'ShortcodeCalendarController',
    'Contact'=>'ShortcodeContactController',
    'Contract'=>'ShortcodeContractController',
    'ContractHeader'=>'ShortcodeContractHeaderController',
    'Display'=>'ShortcodeDisplayController',
    'Documents'=>'ShortcodeDocumentsController',
    'FAQ'=>'ShortcodeFaqController',
    'HouseDetails'=>'ShortcodeHouseDetailsController',
    'HouseType'=>'ShortcodeHouseTypeController',
    'Login'=>'ShortcodeLoginController',
    'ResetPassword'=>'ShortcodeResetPasswordController',
    'Logoff'=>'ShortcodeLogoffController',
    'MaintenanceConfirmation'=>'ShortcodeMaintenanceConfirmationController',
    'MaintenanceConfirmed'=>'ShortcodeMaintenanceConfirmedController',
    'MaintenanceHeader'=>'ShortcodeMaintenanceHeaderController',
    'MaintenanceIssues'=>'ShortcodeMaintenanceIssuesController',
    'MaintenanceRequest'=>'ShortcodeMaintenanceRequestController',
    'MaintenanceReview'=>'ShortcodeMaintenanceReviewController',
    'Notes'=>'ShortcodeNotesController',
    'Photos'=>'ShortcodePhotosController',
    'ProductSelection'=>'ShortcodeProductSelectionController',
    'Progress'=>'ShortcodeProgressController',
    'Stories'=>'ShortcodeStoriesController',
    'Tasks'=>'ShortcodeTasksController',
    'TenderList'=>'ShortcodeTenderListController',
    'TenderOverview'=>'ShortcodeTenderOverviewController',
    'TenderOverviewVariations'=>'ShortcodeTenderOverviewVariationsController',
    'TenderOverviewPackages'=>'ShortcodeTenderOverviewPackagesController',
    'TenderOverviewSelections'=>'ShortcodeTenderOverviewSelectionsController',
    'TenderVariations'=>'ShortcodeTenderVariationsController',
    'TenderPackages'=>'ShortcodeTenderPackagesController',
    'TenderSelections'=>'ShortcodeTenderSelectionsController',
    'TenderSelectionsEdit'=>'ShortcodeTenderSelectionsEditController',
    'TenderSelectionsEmail'=>'ShortcodeTenderSelectionsEmailController'
  ];

  /**
   * Shortcodes which can be displayed more than once
   */
  private static $REPEATABLE_SHORTCODES=[
    'ClientName',
    'Contact',
    'ContractHeader',
    'Progress',
    'Tasks',
    'MaintenanceRequest',
    'Logoff'
  ];

  /**
   * Shortcodes accessible by non authenticated users
   */
  private static $PUBLIC_SHORTCODES=[
    'Contact',
    'Display',
    'HouseType',
    'Login',
	  'ResetPassword',
    'ProductSelection'
  ];

  /**
   * Detects the presence of a MyHome shortcode within a given content (usually, the post_content attribute of a
   * WP_Post object) - it can detect any shortcode or a specific one
   *
   * <p>This method is needed to search for content requiring a valid MyHome session before the page is rendered, so it
   * can redirect to the login page</p>
   * <p>It also autodetects the login page (the one containing a Login shortcode) and allows the WordPress admin to
   * quickly pick it as the login page in the settings panel</p>
   *
   * @uses MyHomeShortcodes::$SHORTCODE_PREFIX
   * @param string      $content   the content to look for MyHome shortcodes
   * @param string|null $shortcode the specific shortcode to look for, if not null - it detects any shortcode otherwise
   *                               (Optional - default null)
   * @return bool whether any shortcode or the specific shortcode (if given) is present
   */
  public function detect($content,$shortcode=null){
    // Include the prefix (properly escaped) for either a generic shortcode content or a specific one
    $prefix=preg_quote(self::$SHORTCODE_PREFIX);
    //echo('detect: ' . $shortcode . '<br/>');

    // If not looking for a specific shortcode, use a placeholder for any character (at least one, non-greedy match)
    // Note that this wouldn't be effective to detect what shortcode has been used, as it could include the attributes, if any
    if($shortcode===null)
      $shortcode='.+?';

    // Build the regular expression to match strings, enclosed in square brackets, made up of the prefix, the shortcode (or the placeholder) and some optional attributes
    $regexp=sprintf('|\[%s%s\b.*?\]|',$prefix,$shortcode);

    return preg_match($regexp,$content);
  }

  /**
   * Looks for any MyHome shortcode used in a given content (usually, the post_content attribute of a WP_Post object)
   *
   * @uses MyHomeShortcodes::$SHORTCODE_PREFIX
   * @uses MyHomeShortcodes::$PUBLIC_SHORTCODES
   * @param string $content the content to look for MyHome shortcodes
   * @param bool   $public  whether public shortcodes should be retrieved
   * @param bool   $private whether private shortcodes (ie any shortcode not marked as public) should be retrieved
   * @return string[] shortcodes detected, without prefix (eg array("Login","ContractHeader","Contact"))
   */
  public function detectAll($content,$public,$private){
    $prefix=preg_quote(self::$SHORTCODE_PREFIX);

    // Build the regular expression to match strings, enclosed in square brackets, made up of the prefix and a shortcode (any character before a white space or the closing square bracket)
    $regexp=sprintf('|\[%s(.+?)(?: .+?)?\]|',$prefix);

    preg_match_all($regexp,$content,$matches);
    $shortcodes=$matches[1];
    //myHome()->log->info(serialize($content));   

    if($public&&$private)
      return $shortcodes;
    else if(!$public&&!$private)
      return [];

    $publicShortcodes=self::$PUBLIC_SHORTCODES;

    if($public) {// $public&&!$private
      //myHome()->log->info('Public');
      return array_filter($shortcodes,function ($shortcode) use ($publicShortcodes){
        return in_array($shortcode,$publicShortcodes);
      });
    } else { // !$public&&$private
      //myHome()->log->info('Private');
      return array_filter($shortcodes,function ($shortcode) use ($publicShortcodes){
        return !in_array($shortcode,$publicShortcodes);
      });
    }
  }

  /**
   * Registers the appropriate WordPress hooks
   *
   * Each shortcode defined in $SHORTCODES is registered - all of them are handled by handleShortcode()
   *
   * @uses MyHomeShortcodes::$SHORTCODES
   */
  public function setupHooks(){
    foreach(array_keys(self::$SHORTCODES) as $shortcode)
      add_shortcode(self::$SHORTCODE_PREFIX.$shortcode,[$this,'handleShortcode']);
  }

  /**
   * Handles any MyHome shortcode
   *
   * @uses MyHomeShortcodes::$SHORTCODE_PREFIX
   * @uses MyHomeShortcodes::$SHORTCODES
   * @uses MyHomeShortcodes::$REPEATABLE_SHORTCODES to prevent non-repeatable shortcodes from being displayed
   * @uses MyHomeShortcodes::$usedShortcodes to know if a shortcode has already been used in this request
   * @uses MyHome::runController()
   * @param array|string $atts      shortcode attributes (eg array("limit"=>"5") for "[MyHome.Notes limit=5]") - if no
   *                                attributes are given, it is an empty string
   * @param string       $content   content enclosed in the shortcode
   * @param string       $shortcode the shortcode to handle (eg "MyHome.Login")
   * @return string the shortcode content generated
   */
  public function handleShortcode($atts,/** @noinspection PhpUnusedParameterInspection */
    $content,$shortcode){ //myHome()->log->info('handleShortcode() ' . $shortcode);
    try{
      // Standardise the attributes array if no attributes are given
      if(!is_array($atts))
        $atts=[];
      if($content) $atts['content'] = $content;
      //myHome()->log->info(serialize($post));

      $prefixLength=strlen(self::$SHORTCODE_PREFIX);

      // If the shortcode does not begin with $SHORTCODE_PREFIX, throw an exception (this shouldn't ever happen) - it will return an empty string
      if(substr($shortcode,0,$prefixLength)!==self::$SHORTCODE_PREFIX)
        throw new MyHomeException('Wrong shortcode: '.$shortcode);

      $shortcode=substr($shortcode,$prefixLength);

      // If no controller class is associated with this shortcode, throw an exception (this shouldn't ever happen) - it will return an empty string
      if(!isset(self::$SHORTCODES[$shortcode]))
        throw new MyHomeException('Wrong shortcode: '.$shortcode);

      // If this is the first time the shortcode is used, add it to the used shortcodes array
      if(!in_array($shortcode,$this->usedShortcodes))
        $this->usedShortcodes[]=$shortcode;
      // Otherwise, check if it is allowed to be repeated - it will return an empty string if not
      else if(!in_array($shortcode,self::$REPEATABLE_SHORTCODES))
        throw new MyHomeException('Repeated shortcode: '.$shortcode);

      // Invoke the appropriate controller class with "shortcode" as the request method for myHome()->runController()
      $controllerClass=self::$SHORTCODES[$shortcode];

      return myHome()->runController($controllerClass,'shortcode',$atts,true);
    }
    catch(MyHomeException $e){
      myHome()->handleError($e);

      return $this->errorMessage($e);
    }
  }

  /**
   * Returns a user friendly error message
   *
   * @since 1.5
   * @param MyHomeException $e
   * @return string
   */
  protected function errorMessage(/** @noinspection PhpUnusedParameterInspection */
    MyHomeException $e){
    $title=__('There is nothing here...','myHome');
    $message1=__('Click here to find out more.','myHome');
    $message2=sprintf(__('%s - %s','myHome'),$e->getUniqueCode(),$e->getMessage());//$e->getUniqueCode());

    $message=<<<END
<div class="mh-wrapper mh-wrapper-error">
  <h3>{$title}</h3>
  <a href="javascript:jQuery('#mh-error-details').show()">{$message1}</a>
  <div id="mh-error-details">{$message2}</div>
</div>

END;

    return $message;
  }

  /**
   * Shortcodes already used in this request
   *
   * @see MyHomeShortcodes::$REPEATABLE_SHORTCODES
   * @var string[]
   */
  public $usedShortcodes=[];
}
