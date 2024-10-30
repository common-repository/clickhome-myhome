<?php

/**
 * The MyHomeBaseController class
 *
 * @package    MyHome
 * @subpackage Controllers
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeBaseController'))
  return;

/**
 * The MyHomeBaseController class
 *
 * <p>Abstract class for view controllers</p>
 * <p>View controllers are used in admin pages and shortcodes</p>
 *
 * @see MyHome::runController() to see how doGet(), doPost(), doPostXhr() and doShortcode() are invoked
 */
abstract class MyHomeBaseController{
  /**
   * Month names
   *
   * <p>It associates each month number with its name for use in the MyHome shortcodes (eg "Jun" for 6)</p>
   * <p>This array is filled up later in the constructor, as it needs to use gettext's __() method</p>
   */
  protected static $MONTH_NAMES=[];

  /**
   * Timeout limit, in seconds, for each cached value
   */
  private static $VAR_TIMEOUT_CACHE=3600;

  /**
   * Timeout limit, in seconds, for each flashed value
   */
  private static $VAR_TIMEOUT_FLASH=90;

  /**
   * The constructor method
   *
   * It fills the $MONTH_NAMES array
   */
  public function __construct(){
    self::$MONTH_NAMES=[1=>__('Jan','myHome'),
      2=>__('Feb','myHome'),
      3=>__('Mar','myHome'),
      4=>__('Apr','myHome'),
      5=>__('May','myHome'),
      6=>__('Jun','myHome'),
      7=>__('Jul','myHome'),
      8=>__('Aug','myHome'),
      9=>__('Sep','myHome'),
      10=>__('Oct','myHome'),
      11=>__('Nov','myHome'),
      12=>__('Dec','myHome')];
  }

  /**
   * Handles a GET request
   *
   * <p>Admin page controllers load the appropriate view</p>
   * <p>Shortcode controllers handle a GET request (eg the logoff request) via admin-post.php</p>
   *
   * @since 1.2 added a return value
   * @param string[] $params GET parameters to be used by the controller (eg array("myHomeDocumentId"=>"1234"), as
   *                         required by the Documents controller)
   * @return bool|null false if an error occurred or null/true otherwise - used to redirect to the error redirect URL
   */
  public abstract function doGet(array $params=[]);

  /**
   * Handles a POST request
   *
   * <p>Admin page controllers handle a POST request (eg saving the settings from the Settings page), via
   * admin-post.php and redirect to the same page after that (responses are flashed with flashVar())</p>
   * <p>Shortcode controllers handle a POST request (eg the login request, upon the login form submission) via
   * admin-post.php</p>
   *
   * @since 1.2 added a return value
   * @param string[] $params POST parameters to be used by the controller (eg array("myHomeUsername"=>"user"), as
   *                         required by the Login controller)
   * @return bool|null false if an error occurred or null/true otherwise - used to redirect to the error redirect URL
   */
  public abstract function doPost(array $params=[]);

  /**
   * Handles a POST request sent via XmlHttpRequest
   *
   * Currently, only shortcode controllers use this to handle Ajax requests (eg the events for a month used by the
   * Calendar shortcode to update the table) via admin-ajax.php
   *
   * @param string[] $params POST parameters to be used by the controller (eg array("myHomeMonth"=>"6"), as required by
   *                         the Calendar controller)
   */
  public abstract function doPostXhr(array $params=[]);

  /**
   * Handles a "shortcode request" - it loads and displays the appropriate view for a shortcode
   *
   * This method doesn't return any value - instead, MyHome::runController() captures its output using
   * ob_get_contents() and then returns it
   *
   * @param string[] $atts shortcode attributes (eg array("limit"=>"5"), as used by the Notes shortcode) - note that
   *                       most attribute values have default values
   */
  public abstract function doShortcode(array $atts=[]);

  /**
   * Helper method used by views to append some fixed parameters to a (POST) form, required to perform a POST request
   *
   * <p>Each parameter is appended as a hidden input field</p>
   * <p>The form attributes array is supposed to be created by formAttributes()</p>
   * <p>For example, the Login shortcode view uses this method to append the required action, redirection and nonce
   * values so that MyHomeAdminPost can handle the login action correctly</p>
   * <p>Apart from the action, redirection (not present for some actions) and nonce values, each view can add whatever
   * fixed parameters it needs</p>
   *
   * @see MyHomeBaseController::formAttributes() to see the structure of the $formAttributes array
   * @param mixed[] $formAttributes the form attributes array
   * @param int     $numSpaces      the number of spaces to prepend to each input field, for correct indentation
   */
  protected function appendFormParams(array $formAttributes,$numSpaces){
    $indent=str_repeat(' ',$numSpaces);

    printf("<div style=\"display:none\">");
    foreach($formAttributes['params'] as $key=>$value)
      // esc_attr() never encodes already escaped strings, so there is no risk using it
      printf("%s<input name=\"%s\" type=\"hidden\" value=\"%s\">\n",esc_attr($indent),$key,esc_attr($value));
    printf("</div>");
  }

  /**
   * Helper method used by views to append the target URL to a (POST) form, required to perform a POST request
   *
   * <p>The URL is supposed to be appended to the form's "action" attribute</p>
   * <p>The form attributes array is supposed to be created by formAttributes()</p>
   *
   * @see MyHomeBaseController::formAttributes() to see the structure of the $formAttributes array
   * @param string[] $formAttributes the form attributes array
   */
  protected function appendFormUrl(array $formAttributes){
    // esc_url() never encodes already escaped strings
    echo esc_url($formAttributes['url']);
  }

  /**
   * Stores a value in the cache
   *
   * @since 1.2
   * @uses  MyHomeStorage::put()
   * @uses  MyHomeBaseController::varName() to generate the full variable name for $name
   * @param string $name    the variable name
   * @param mixed  $value   the value to be stored
   * @param bool   $replace whether the previous value with the same name should be replaced, if already present
   *                        (Optional - default true)
   * @param bool   $global  whether the scope of this variable is global (Optional - default false)
   * @return mixed|null the previous value, if present, or null otherwise
   */
  protected function cacheVar($name,$value,$replace=true,$global=false){
    return myHome()->storage->put($this->varName($name,$global),$value,$replace,self::$VAR_TIMEOUT_CACHE);
  }

  /**
   * Returns a date with the format "j/M/y" or "j/M/Y" (eg "9/Jun/14" or "9/Jun/2014")
   *
   * It is used instead of date() or DateTime::format() in order to have a better control of the month names (which
   * could be translated) as well as localised date formats</p>
   *
   * @param DateTime $dt        a DateTime object to get the date from
   * @param bool     $yearShort whether the year should be short (two digits) or long (Optional - default false)
   * @return string the formatted date
   */
  protected function dateString(DateTime $dt,$yearShort=false){
    $day=$dt->format('j');
    $month=$dt->format('n');
    $year=$dt->format($yearShort?'y':'Y');

    // Using __() with numbered placeholders allows to rearrange the format in the event of a translation
    return sprintf(__('%1$s/%2$s/%3$s','myHome'),$day,self::$MONTH_NAMES[$month],$year);
  }

  /**
   * Delete a previously stored (flashed or cached) variable
   *
   * @uses MyHomeStorage::delete()
   * @uses MyHomeBaseController::varName() to generate the full variable name for $name
   * @param string $name   the variable name
   * @param bool   $global whether the scope of this variable is global (Optional - default false)
   * @return mixed|null the previous value, if present, or null otherwise
   */
  protected function deleteVar($name,$global=false){
    return myHome()->storage->delete($this->varName($name,$global));
  }

  /**
   * Completes an array (usually, the $params argument in doGet(), etc.) with missing keys from a given list of
   * required parameters
   *
   * <p>Any parameter not found in $params will have a null value in the returned array</p>
   * <p>This method is useful when receiving parameters from a form with checkboxes in it - these will have null if not
   * checked, or "on" (as set by default by the browser) otherwise</p>
   *
   * @param string[] $keys   the required parameters list
   * @param string[] $params the parameters list, as received by doGet(), etc.
   * @return mixed[] the complete parameters array
   */
  protected function extractParams(array $keys,array $params){
    $values=[];

    foreach($keys as $key)
      if(isset($params[$key]))
        $values[]=$params[$key];
      else
        $values[]=null;

    return $values;
  }

  protected function filterText($text,$maxLength){
    $text=wp_unslash($text);
    $text=trim($text);

    if($text!=='')
      $text=substr($text,0,$maxLength);

    return $text;
  }

  /**
   * Stores a value only for the next request (flash data)
   *
   * @uses MyHomeStorage::put()
   * @uses MyHomeBaseController::varName() to generate the full variable name for $name
   * @param string $name    the variable name
   * @param mixed  $value   the value to be stored
   * @param bool   $replace whether the previous value with the same name should be replaced, if already present
   *                        (Optional - default true)
   * @param bool   $global  whether the scope of this variable is global (Optional - default false)
   * @return mixed|null the previous value, if present, or null otherwise
   */
  protected function flashVar($name,$value,$replace=true,$global=false){
    return myHome()->storage->put($this->varName($name,$global),$value,$replace,self::$VAR_TIMEOUT_FLASH,true);
  }

  /**
   * Loads a view or subview file (located in MH_PATH_VIEWS)
   *
   * View files display the content as soon as the are included, so there is no need to instance any class
   *
   * @param string|string[] $view      the view name (eg "shortcodeLogin") or the view and subview names (eg
   *                                   array("shortcodeNotes","note"))
   * @param string          $directory subdirectory within the views directory (Optional - default '')
   * @param mixed[]         $vars      an array with variables seen by the view as local variables - usually created
   *                                   with compact() (Optional - default array())
   * @throws MyHomeException if the view or subview file was not found or the $view parameter is an array with more
   *                                   than two values
   */
  protected function loadView($view,$directory='',$vars=[]){
    if($directory!=='')
      $directory=trailingslashit($directory);

    $subview='';

    if(is_array($view)){
      if(!$view||count($view)>2)
        throw new MyHomeException('Wrong view/subview array');

      if(isset($view[1]))
        $subview=$view[1];

      if(isset($view[0]))
        $view=$view[0];
      else
        throw new MyHomeException('Wrong view/subview array');
    }

    if($subview!=='')
      $view.='.'.$subview; // For example: "shortcodeNotes.note.php"

    $path=sprintf('%s/%s%s.php',MH_PATH_VIEWS,$directory,$view);

    if(!is_readable($path))
      throw new MyHomeException('View file not found: '.$path);
    
    // Enable shortcodes within shortcodes
    //if(isset($vars['content'])) $vars['content'] = do_shortcode($vars['content']);
    if(isset($vars['atts']['content'])) $vars['atts']['content'] = do_shortcode($vars['atts']['content']);

    extract($vars);

    // A view file can be included as many times as needed - it is up to each view to limit itself to one instance per request
    /** @noinspection PhpIncludeInspection */
    require $path;
  }

  /**
   * Restores a previously stored (flashed or cached) variable
   *
   * @uses MyHomeStorage::get()
   * @uses MyHomeBaseController::varName() to generate the full variable name for $name
   * @param string     $name    the variable name
   * @param mixed|null $default the value to return if the variable is not found (Optional - default null)
   * @param bool       $global  whether the scope of this variable is global (Optional - default false)
   * @return mixed|null the value, if present, or null otherwise
   */
  protected function restoreVar($name,$default=null,$global=false){
    return myHome()->storage->get($this->varName($name,$global),$default);
  }

  /**
   * Returns an array containing the settings needed to perform a specific request
   *
   * Note that a view can use this method with an action not handled by its corresponding controller
   *
   * @see  MyHomeAdminAjaxHandler::xhrAttributes() for an example of a valid XHR request
   * @uses MyHomeAdminAjaxHandler::xhrAttributes()
   * @param string $action the action name, which should be a key in the MyHomeAdminAjaxHandler::$ACTIONS array
   * @return mixed[] the XHR attributes array:
   *                       <ul>
   *                       <li>url: the admin-ajax.php URL (eg "http://website.com.au/wp-admin/admin-ajax.php")</li>
   *                       <li>params > action: the WordPress action parameter needed to allow handleAction() to handle
   *                       the request</li>
   *                       <li>params > $PARAM_ACTION: the MyHome action parameter which defines the action
   *                       requested</li>
   *                       <li>params > $PARAM_NONCE: the appropriate nonce value for the access level required by the
   *                       action requested</li>
   *                       </ul>
   */
  protected function xhrAttributes($actions = null){
    return myHome()->adminAjaxHandler->xhrAttributes($actions);
  }

  /**
   * Generates a full variable name for a given base for use within the calling class (or within any class if
   * $global=true)
   *
   * For example, if this class is AdminPageSettingsController, the full name for "saved" is
   * "AdminPageSettingsController_saved"
   *
   * @uses get_called_class() to know the calling class name (requires PHP >=5.3.0)
   * @param string $nameBase the variable name base (eg "maintenanceJob123")
   * @param bool   $global   whether the scope of this variable is global
   * @return string the full variable name
   */
  private function varName($nameBase,$global){
    if(!$global)
      return get_called_class().'_'.$nameBase;

    return 'global_'.$nameBase;
  }
}