<?php

/**
 * The main MyHome class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHome'))
  return;

/**
 * The main MyHome class
 *
 * <p>Provides access to the remaining classes (MyHomeAdmin, MyHomeApi, etc.) as well as some general methods used
 * throughout the whole plugin</p>
 * <p>Registers the appropriate WordPress hooks in order to provide its services</p>
 *
 * @since 1.3 loadClass(), loadController(), and doLoadClass() functions removed
 * @property-read MyHomeStorage          $storage
 * @property-read MyHomeOptions          $options
 * @property-read MyHomeLog              $log
 * @property-read MyHomeAdmin            $admin
 * @property-read MyHomeApi              $api
 * @property-read MyHomeShortcodes       $shortcodes
 * @property-read MyHomeWidgets          $widgets
 * @property-read MyHomeSession          $session
 * @property-read MyHomeAdminPostHandler $adminPostHandler
 * @property-read MyHomeAdminAjaxHandler $adminAjaxHandler
 * @property-read MyHomeAdvertising      $advertising
 * @property-read MyHomeDatabase         $database
 * @property-read MyHomeFacebook         $facebook
 */
class MyHome{
  /**
   * Returns the singleton instance - used by myHome()
   *
   * @return MyHome the singleton instance
   */
  public static function getInstance(){
    return self::$instance;
  }

  /**
   * Singleton instance
   *
   * @var MyHome
   */
  private static $instance=null;

  /**
   * Constructor method
   *
   * It does the following:
   * <ul>
   * <li>Stores the class instance into the $instance static property</li>
   * <li>Initialises the PHP session, if there is none available (needed by controllers and MyHomeSession)</li>
   * <li>Sets up the appropriate WordPress hooks</li>
   * </ul>
   *
   * @throws MyHomeException if the MyHome class has already been instantiated
   */
  public function __construct(){ 
    if(self::$instance!==null)
      throw new MyHomeException('The MyHome class is already instantiated');

    // This should be done prior to any code which needs myHome() to return this instance (eg the MyHomeApi initialisation)
    self::$instance=$this;

    try{
      // Ensure MyHomeStorage is loaded before any output starts
      $this->storage;

      // Enable logging if the option is set
      if($this->options->isLogEnabled()){
        $this->log->enable();

        // Set the appropriate log level
        if($this->options->getLogLevel()===MyHomeOptions::$LOG_LEVEL_ERROR)
          $this->log->setLevel(MyHomeLog::$LEVEL_ERROR);
        else if($this->options->getLogLevel()===MyHomeOptions::$LOG_LEVEL_INFO)
          $this->log->setLevel(MyHomeLog::$LEVEL_INFO);

        // Set the appropriate log method
        if($this->options->getLogMethod()===MyHomeOptions::$LOG_METHOD_FILE)
          $this->log->setMethod(MyHomeLog::$METHOD_FILE);
        else if($this->options->getLogMethod()===MyHomeOptions::$LOG_METHOD_OPTION)
          $this->log->setMethod(MyHomeLog::$METHOD_OPTION);
      }

      $this->setupHooks();
    }
      // If an exception is caught at this point, don't exit the script, so that the admin can try to read the log file at the admin panel
    catch(MyHomeException $e){
      $this->handleError($e);
    }

    // Note that this plugin is not internationalised - therefore, load_plugin_textdomain() is not called here
    // Every text string (except the internal error messages) is passed through gettext methods (__(), _e(), etc.), for the event of a future internationalisation of the plugin
  }

  /**
   * Used to retrieve read-only properties (singleton instances)
   *
   * @uses MyHome::$classes
   * @param string $name name of the property
   * @return mixed|null the singleton, if available (eg the MyHomeAdmin instance if $name is "admin"), or null otherwise
   */
  public function __get($name){
    // Lazy load the required instance
    if(array_key_exists($name,$this->classes)){
      if($this->classes[$name]===null){
        switch($name){
          case 'storage':
            $instance=new MyHomeStorage;
            break;
          case 'options':
            $instance=new MyHomeOptions;
            break;
          case 'log':
            $instance=new MyHomeLog;
            break;
          case 'admin':
            $instance=new MyHomeAdmin;
            break;
          case 'api':
            $instance=new MyHomeApi;
            break;
          case 'shortcodes':
            $instance=new MyHomeShortcodes;
            break;
          case 'widgets':
            $instance=new MyHomeWidgets;
            break;
          case 'session':
            $instance=new MyHomeSession;
            break;
          case 'adminPostHandler':
            $instance=new MyHomeAdminPostHandler;
            break;
          case 'adminAjaxHandler':
            $instance=new MyHomeAdminAjaxHandler;
            break;
          case 'advertising':
            $instance=new MyHomeAdvertising;
            break;
          case 'database':
            $instance=new MyHomeDatabase;
            break;
          case 'facebook':
            $instance=new MyHomeFacebook;
            break;
          case 'helpers':
            $instance=new MyHomeHelpers;
            break;
          default:
            $instance=null;
        }

        $this->classes[$name]=$instance;
      }

      return $this->classes[$name];
    }

    // Do not throw an exception, as this is more like a standard PHP error
    trigger_error('Undefined property: '.$name);

    return null;
  }

  /**
   * Used to prevent read-only properties from being modified
   *
   * @uses MyHome::$classes
   * @param string $name  name of the property (other than the read-only properties)
   * @param mixed  $value new property value
   */
  public function __set($name,$value){
    if(!isset($this->classes[$name]))
      $this->$name=$value;
  }

  /**
   * Aborts the script execution
   *
   * This method should be called before any output - ie from template_redirect, admin_post or wp_ajax actions
   *
   * @uses handleError() to handle the error according to the settings and the debug constant
   * @param int    $statusCode   HTTP status code to return
   * @param string $errorMessage an error message explaining what went wrong - final users shouldn't see it
   */
  public function abort($statusCode, $errorMessage){
    status_header($statusCode, $errorMessage);
    $this->handleError(sprintf('HTTP error code %u: %s', $statusCode, $errorMessage));

    die;
  }

  /**
   * Handles a MyHome error
   *
   * <p>This method is called from hook callbacks when catching exceptions and from the abort() method</p>
   * <p>If logging is enabled, appends the error message to the log file</p>
   * <p>If MH_DEBUG is set, displays the error (stdout) as well as the stack trace</p>
   *
   * @uses MH_DEBUG to determine if the message should be displayed
   * @uses debug_print_backtrace() to print the stack trace
   * @param MyHomeException|string $error either a MyHomeException object or an error message as a string
   */
  public function handleError($error){
    // Generate the appropriate message string
    if($error instanceof MyHomeException)
      $message = sprintf('MyHome exception %s: "%s" (%s:%u)',$error->getUniqueCode(),$error->getMessage(),$error->getFile(),$error->getLine());
    else
      $message=$error;

    $caller = debug_backtrace()[1]['class'] . "." . debug_backtrace()[1]['function'] . "() [" . debug_backtrace()[0]['line'] . "]";

    $this->log->error($caller . ": " . $message);

    if(MH_DEBUG){
      echo '<hr>';

      printf('<p>%s</p>',esc_html($message));

      echo '<pre>';
      debug_print_backtrace();
      echo '</pre>';

      echo '<hr>';
    }
  }

  /**
   * Appends the Logoff option to the appropriate menu, if available, and if a valid MyHome session is active
   *
   * Triggered by the wp_nav_menu_items action
   *
   * @uses MyHomeSession::guest() to check if a user is logged in
   * @uses MyHomeOptions::getLogoffOptionName()
   * @uses MyHomeOptions::getLogoffMenuLocation()
   * @param string   $items the menu in HTML format - the Logoff option is appended to this string
   * @param stdClass $args  action arguments
   * @return string the menu in HTML format
   */
  public function onNavMenuItems($items,$args){
    try{
      if(!$this->session->activeSession())
        return $items;

      // Facebook users should see the Logoff option only when they have chosen a job and have more jobs available
      //if($this->session->isFacebook())
      //  if($this->session->facebookPartialLogin()||!$this->session->facebookMultipleJobsAvailable())
      //    return $items;
      if($args->theme_location!==$this->options->getLogoffMenuLocation())
        return $items;

      // Use the site homepage as the redirection page (the main page is supposed to required authentication, so it is not considered here)
      $redirect=home_url();

      $formAttributes=$this->adminPostHandler->formAttributes('logoff','GET',$redirect);
      $url=add_query_arg($formAttributes['params'],$formAttributes['url']);
      $url=esc_url($url);

      $name=$this->options->getLogoffOptionName();
      $name=strip_tags($name);
      $name=esc_html($name);

      $items.=sprintf('<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="%s">%s</a></li>',
        $url,$name);

      return $items;
    }
    catch(MyHomeException $e){
      $this->handleError($e);

      return '';
    }
  }

  /**
   * Adds the GET parameter used by some of the plugin shortcodes
   *
   * Triggered by the query_vars action
   *
   * @since 1.5
   * @param string[] $vars
   * @return string[]
   */
  public function onQueryVars(array $vars){
    $vars[]='myHomeLoginRedirect';
    $vars[]='myHomeJobId';
    $vars[]='myHomeAddIssue';
    $vars[]='myHomeTenderId';
    $vars[]='myHomeTenderSelectionId';

    return $vars;
  }

  /**
   * Redirects to the login page if the user is attempting to access a page containing a MyHome shortcode other than
   * the Login shortcode without any authentication
   *
   * <p>It prevents a logged in client from accessing the login page again</p>
   * <p>It also removes the post relational links from the header when displaying the login page</p>
   * <p>Triggered by the template_redirect action</p>
   *
   * @uses MyHomeShortcodes::detect() to search for shortcodes in the post content
   * @uses MyHomeSession::guest() to check if a user is logged in
   */
  public function onTemplateRedirect(){ //myHome()->log->info('onTemplateRedirect()');
    try{
      global $post;

      // If no global $post can be found, do nothing
      if(empty($post)||!($post instanceof WP_Post))
        return; 

      $postId=$post->ID;
      $loginPage=$this->options->getLoginPage();
      $guest=$this->session->guest();

      // If no login page is yet configured, no shortcode content can be displayed
      if(!$loginPage) 
        $this->abort(500,'Login page not set up');
   

      $isLoginPage=$postId==$loginPage;

      // If the user is not requesting the login page...
      if(!$isLoginPage){
        // If no private MyHome shortcodes are detected in the post, do nothing
        if(!$this->shortcodes->detectAll($post->post_content,false,true)) {
        //if(count($this->shortcodes->usedShortcodes)) {
          //myHome()->log->info(count($this->shortcodes->detectAll($post->post_content,false,true)) .  ' MyHome shortcodes were found in content');
          //myHome()->log->info(count($this->shortcodes->usedShortcodes) . ' used MyHome shortcodes');
          //myHome()->log->info(has_shortcode( $post->post_content, 'Progress' ) == true ? 'true' : 'false');
          //myHome()->log->info(serialize($this->shortcodes->usedShortcodes));
          return;
        } //else return;

        //myHome()->log->info('MyHome shortcodes found: ' . serialize($this->shortcodes->detectAll($post->post_content,false,true)));
        //myHome()->log->info("onMyHomeTemplateRedirect: " . $guest . "\n");        

        // If the user is not authenticated, redirect to the login page
        if($guest){
          if(isset($_SERVER['REQUEST_URI']))
            $requestUri=$_SERVER['REQUEST_URI'];
          else
            $requestUri=get_permalink();

          wp_safe_redirect(add_query_arg('myHomeLoginRedirect',rawurlencode($requestUri),get_permalink($loginPage)));
          die;
        }
        else{
          // Check if this is the Tender List page and only one tender is available
          if($this->options->isTenderSkipList()){
            $tenderPages=$this->options->getTenderPages();
            if(!empty($tenderPages['list'])&&$postId==$tenderPages['list']){
              $tenders=ShortcodeTenderListController::tenders();

              //var_dump($tenders);
              // If only one tender is available, redirect to its overviewUrl, if available
              if($tenders&&count($tenders)===1){
                $tender=array_values($tenders)[0];// $tender=array_values($tenders)[0];

                if(!empty($tender->overviewUrl)){
                  wp_safe_redirect($tender->overviewUrl);
                  die;
                }
              }
            }
          }

          // Otherwise, send some HTTP headers to prevent the browser from caching the server page
          nocache_headers();
          @header('Expires: Wed, 25 Nov 1981 00:30:00 GMT');
        }
      }
      // If the user is requesting the login page...
      else // If the user is not authenticated, remove the post relational links from the header, as they are not meant to be seen by a search engine
        if($guest) {
          //var_dump(get_permalink($this->options->getMainPage()));
          remove_action('wp_head','adjacent_posts_rel_link_wp_head',10);
        // If the user is authenticated, redirect to the main page, if it is different from the login page
        }else{
          $mainPage=$this->options->getMainPage();

          if($mainPage&&$mainPage!=$loginPage){
            wp_safe_redirect(get_permalink($mainPage));
            die;
          }
        }
    }
    catch(MyHomeException $e){
      myHome()->log->info('onTemplateRedirect() catch' . serialize($e));
      $this->handleError($e);
    }
  }

  /**
   * Loads a controller class and executes an appropriate method
   *
   * This method is used by:
   * <ul>
   * <li>Admin page hooks (GET method): to display a specific admin page view</li>
   * <li>Shortcode hooks (shortcode method): to display a shortcode view - from
   * MyHomeShortcodes::handleShortcode()</li>
   * <li>Ajax requests (XHR method): to process a specific Ajax request (eg load the events for a month in the
   * calendar) - from MyHomeAdminAjaxHandler::handleAction()</li>
   * <li>GET/POST requests (GET and POST methods): to process a specific request (eg log a user in or off) - from
   * MyHomeAdminPostHandler::handleAction()</li>
   * </ul>
   *
   * @see  MyHomeAdminPostHandler::performRequest() for a list of possible return values in GET or POST requests
   * @uses ob_get_contents() to retrieve the outputted content if $return is true
   * @param string  $controllerClass controller class name
   * @param string  $requestMethod   this parameter determines which method to run, and depends on the request method -
   *                                 it can be:
   *                                 <ul>
   *                                 <li>GET or POST: for GET or POST requests</li>
   *                                 <li>XHR: for Ajax requests (via the wp_ajax action)</li>
   *                                 <li>shortcode: for shortcode contents</li>
   *                                 </ul>
   * @param mixed[] $paramsOrAtts    GET or POST parameters; if $requestMethod is "shortcode", the shortcode attributes
   * @param bool    $return          whether the contents should be returned (for shortcodes) or displayed directly
   *                                 (Optional - default false)
   * @return string|mixed if $return is true, the controller output; if not, the return value of the invoked method
   * @throws MyHomeException if a controller method throws an exception, it is rethrown here - also, if $requestMethod
   *                                 is not "GET" or "POST"
   */
  public function runController($controllerClass, $requestMethod, array $paramsOrAtts=[], $return=false){
    if($return)
      ob_start();

    try{
      /**
       * @var MyHomeBaseController $controller
       */
      //myHome()->log->info('runController: ' . $requestMethod . ' ' . $controllerClass);
      $controller=new $controllerClass;
      //myHome()->log->info('$controller: ' . serialize($controller));

      switch($requestMethod){
        case 'GET':
          $result=$controller->doGet($paramsOrAtts);
          break;
        case 'POST':
          $result=$controller->doPost($paramsOrAtts);
          break;
        case 'XHR':
          $result=$controller->doPostXhr($paramsOrAtts);
          break;
        case 'shortcode':
          // $return should be true - $result will be ignored
          $result=$controller->doShortcode($paramsOrAtts);
          break;
        default:
          throw new MyHomeException('Request method not supported: '.$requestMethod);
          break;
      }

      // If $return=true, capture everything on the output buffer since the previous call to ob_start() and clean the buffer
      if($return){
        $contents=ob_get_contents();
        ob_end_clean();

        return $contents;
      }
      else
        return $result;
    }
    catch(MyHomeException $e){
      // Clean the output buffer
      if($return)
        ob_end_clean();

      // This should be handled by the action callback which called this method
      throw $e;
    }
  }

  /**
   * Creates a DateTime object using the timezone set in this WordPress site
   *
   * Used instead of new DateTime when the timezone is important (eg when comparing dates)
   *
   * @since 1.2
   * @uses  MyHome::wpDateTimeZone()
   * @param string $time a date/time string (Optional - default 'now')
   * @return DateTime the DateTime object
   */
  public function wpDateTime($time='now'){
    return new DateTime($time,$this->wpDateTimeZone());
  }

  /**
   * Creates a DateTime object from a given format using the timezone set in this WordPress site
   *
   * Used instead of new DateTime when the timezone is important (eg when comparing dates)
   *
   * @since 1.2
   * @uses  MyHome::wpDateTimeZone()
   * @param string $format the format that the passed in string should be in
   * @param string $time   string representing the time
   * @return DateTime the DateTime object
   */
  public function wpDateTimeFromFormat($format,$time){
    return DateTime::createFromFormat($format,$time,$this->wpDateTimeZone());
  }

  /**
   * Registers the appropriate WordPress hooks
   *
   * <p>The following hooks are registered:</p>
   * <ul>
   * <li>wp_nav_menu_items: appends the Logoff option if the plugin is configured to do so and if a user is logged
   * in</li>
   * <li>template_redirect: checks for a single post or page containing a MyHome shortcode and redirects the user to
   * the login page if he/she is a guest</li>
   * </ul>
   * <p>As a general rule, all the hook callbacks (from this or other classes) catch exceptions (MyHomeException) and
   * handle the error with handleError()</p>
   *
   * @uses MyHomeAdmin::setupHooks()
   * @uses MyHomeShortcodes::setupHooks()
   * @uses MyHomeWidgets::setupHooks()
   * @uses MyHomeAdminPostHandler::setupHooks()
   * @uses MyHomeAdminAjaxHandler::setupHooks()
   * @uses MyHomeAdvertising::setupHooks()
   */
  private function setupHooks(){ // myHome()->log->info('setupHooks() ');
    add_filter('wp_nav_menu_items',[$this,'onNavMenuItems'],10,2);
    add_action('template_redirect',[$this,'onTemplateRedirect']);
//    add_action('template_include',[$this,'onTemplateRedirect']); // Broke ability to run MyHome without a theme

    // Adding myHomeLoginRedirect prevents the login page from working when used as the front page
    // add_filter('query_vars',[$this,'onQueryVars']);

    $this->admin->setupHooks();
    $this->shortcodes->setupHooks();
    $this->widgets->setupHooks();
    $this->adminPostHandler->setupHooks();
    $this->adminAjaxHandler->setupHooks();
    $this->advertising->setupHooks();
  }

  /**
   * Returns a DateTimeZone object for this WordPress site
   *
   * @since 1.2
   * @uses  MyHome::wpTimezone()
   * @return DateTimeZone the DateTimeZone object
   */
  private function wpDateTimeZone(){
    if($this->dtz===null)
      $this->dtz=new DateTimeZone($this->wpTimezone());

    return $this->dtz;
  }

  /**
   * Returns the timezone string for a site, even if it's set to a UTC offset
   *
   * Adapted from http://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
   *
   * @since 1.2
   * @return string a valid PHP timezone string
   */
  private function wpTimezone(){
    // If site timezone string exists, return it
    if($timezone=get_option('timezone_string'))
      return $timezone;

    // Get UTC offset, if it isn't set then return UTC
    if(($utcOffset=get_option('gmt_offset',0))===0)
      return 'UTC';

    // Adjust UTC offset from hours to seconds
    $utcOffset*=3600;

    // Attempt to guess the timezone string from the UTC offset
    $timezone=timezone_name_from_abbr('',$utcOffset);

    // Last try, guess timezone string manually
    if($timezone===false){
      $isDst=date('I');

      foreach(timezone_abbreviations_list() as $abbr)
        foreach($abbr as $city)
          if($city['dst']==$isDst&&$city['offset']==$utcOffset)
            return $city['timezone_id'];
    }

    // Fallback to UTC
    return 'UTC';
  }

  /**
   * Class singleton instances accesed as read-only properties
   *
   * @var object[]
   */
  private $classes=[
    'storage'=>null,
    'options'=>null,
    'log'=>null,
    'admin'=>null,
    'api'=>null,
    'shortcodes'=>null,
    'widgets'=>null,
    'session'=>null,
    'adminPostHandler'=>null,
    'adminAjaxHandler'=>null,
    'advertising'=>null,
    'database'=>null,
    'facebook'=>null,
    'helpers'=>null
  ];

  /**
   * DateTimeZone object used in calls to wpDateTime() and wpDateTimeFromFormat()
   *
   * @since 1.2
   * @var DateTimeZone
   */
  private $dtz;
}
