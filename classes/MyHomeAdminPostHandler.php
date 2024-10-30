<?php

/**
 * The MyHomeAdminPostHandler class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeAdminPostHandler'))
  return;

/**
 * The MyHomeAdminPostHandler class
 *
 * <p>Handles GET and POST requests received via admin-post.php</p>
 * <p>Note that, despite its name, it is not used in the admin pages nor it has any relation with the MyHomeAdmin
 * class; it also handles GET requests</p>
 */
class MyHomeAdminPostHandler extends MyHomeHandler{
  /**
   * Action definitions for GET requests
   *
   * <ul>
   * <li>Array key: action parameter value (eg $PARAM_ACTION=logoff)</li>
   * <li>callback: name of the method within this class which will handle the action (eg $this->actionLogoff())</li>
   * <li>access: required access needed to handle the action (eg ACCESS_CLIENT for a logged in client) - a different
   * nonce is used for each access level</li>
   * <li>redirect: whether a redirection should be done after executing the action callback (Optional - default
   * true)</li>
   * </ul>
   *
   * @see MyHomeHandler for the access level constants
   */
  private static $ACTIONS_GET=[
    /*'document'=>[
      'controller'=>'ShortcodeDocumentsController',
      'redirect'=>false
    ],*/
    'systemDocument'=>[
      'controller'=>'ShortcodeDocumentsController',
      'access'=>self::ACCESS_ALL,
      'redirect'=>false
    ],
    'clientDocument'=>[
      'controller'=>'ShortcodeDocumentsController',
      'access'=>self::ACCESS_CLIENT,
      'redirect'=>false
    ],
    'logoff'=>[
      'controller'=>'ShortcodeLogoffController',
      'access'=>self::ACCESS_ALL
    ]
  ]; // If ACCESS_CLIENT were used, a user with an expired session would get a 403 error when clicking the Logoff button

  /**
   * Action definitions for POST requests
   *
   * @see MyHomeAdminPostHandler::$ACTIONS_GET
   */
  private static $ACTIONS_POST=[
    'adminAdvertising'=>[
      'controller'=>'AdminPageAdvertisingController',
      'access'=>self::ACCESS_ADMIN
    ],

    'adminDebug'=>[
      'controller'=>'AdminPageDebugConsoleController',
      'access'=>self::ACCESS_ADMIN
    ],

    'adminFacebook'=>[
      'controller'=>'AdminPageFacebookController',
      'access'=>self::ACCESS_ADMIN
    ],

    'adminLog'=>[
      'controller'=>'AdminPageLogController',
      'access'=>self::ACCESS_ADMIN
    ],

    'adminMaintenance'=>[
      'controller'=>'AdminPageMaintenanceController',
      'access'=>self::ACCESS_ADMIN
    ],

    'adminSettings'=>[
      'controller'=>'AdminPageSettingsController',
      'access'=>self::ACCESS_ADMIN
    ],

    'contact'=>[
      'controller'=>'ShortcodeContactController',
      'access'=>self::ACCESS_PUBLIC
    ],

    'login'=>[
      'controller'=>'ShortcodeLoginController',
      'access'=>self::ACCESS_PUBLIC
    ],

    'maintenanceConfirmation'=>[
      'controller'=>'ShortcodeMaintenanceConfirmationController',
      'access'=>self::ACCESS_CLIENT
    ],

    'maintenanceIssues'=>[
      'controller'=>'ShortcodeMaintenanceIssuesController',
      'access'=>self::ACCESS_CLIENT
    ],

    'maintenanceRequest'=>[
      'controller'=>'ShortcodeMaintenanceRequestController',
      'access'=>self::ACCESS_CLIENT
    ],

    'maintenanceReview'=>[
      'controller'=>'ShortcodeMaintenanceReviewController',
      'access'=>self::ACCESS_CLIENT
    ],

    'notes'=>[
      'controller'=>'ShortcodeNotesController',
      'access'=>self::ACCESS_CLIENT,
      //'return'=>self::RETURN_HTML
    ],

    'adminTender'=>[
      'controller'=>'AdminPageTenderController',
      'access'=>self::ACCESS_ADMIN
    ],

    'jobs'=>[
      'controller'=>'ShortcodeLoginController',
      'access'=>self::ACCESS_FACEBOOK_PARTIAL
    ]
];

  /**
   * Returns an array containing the settings needed to perform a specific request
   *
   * <p>This method is intended to be used by shortcodes which need to create an HTML form (usually, with
   * "method=POST")</p>
   * <p>It is also useful to generate a URL to access a specific GET action (eg the Logoff URL included in the Logoff
   * shortcode or appended to a menu as an option)</p>
   * <p>The following is an example of the POST parameters sent for a specific action:</p>
   * <ul>
   * <li>action: MyHome</li>
   * <li>myHomeAction: logoff</li>
   * <li>myHomeRedirect: http://website.com.au/calendar/</li>
   * <li>myHomeNonce: 83e42c3d61</li>
   * <li>myHomeJobNumber: ABC1234</li>
   * <li>myHomeUsername: username</li>
   * <li>myHomePassword: password</li>
   * </ul>
   *
   * @since 1.2 added the $redirectUrlError parameter
   * @uses  $_SERVER['REQUEST_URI'] to retrieve the current URL (only when $redirectUrl=null)
   * @uses  MyHomeAdminPostHandler::$ACTIONS_GET
   * @uses  MyHomeAdminPostHandler::$ACTIONS_POST
   * @uses  MyHomeHandler::createNonce()
   * @param string      $action           the action name, which should be a key in the
   *                                      MyHomeAdminPostHandler::$ACTIONS_GET or MyHomeAdminPostHandler::$ACTIONS_POST
   *                                      array
   * @param string      $requestMethod    the request method used by the action - $action will be searched for in the
   *                                      actions array corresponding to this parameter
   * @param string|null $redirectUrl      the URL to redirect the user to after handling the request; if null, use the
   *                                      current URL (Optional - default null)
   * @param string|null $redirectUrlError the URL to redirect the user to if the request failed; if null, use the
   *                                      current URL (Optional - default null)
   * @return mixed[] the form attributes array, with all its values escaped (with esc_url() or esc_attr()):
   *                                      <ul>
   *                                      <li>url: the admin-post.php URL (eg
   *                                      "http://website.com.au/wp-admin/admin-post.php")</li>
   *                                      <li>params > action: the WordPress action parameter needed to allow
   *                                      handleAction() to handle the request</li>
   *                                      <li>params > $PARAM_ACTION: the MyHome action parameter which defines the
   *                                      action requested</li>
   *                                      <li>params > $PARAM_REDIRECT: the redirect URL (not present if the action is
   *                                      defined with redirect=false)</li>
   *                                      <li>params > $PARAM_REDIRECT_ERROR: the redirect URL on errors (not present
   *                                      if the action is defined with redirect=false)</li>
   *                                      <li>params > $PARAM_NONCE: the appropriate nonce value for the access level
   *                                      required by the action requested</li>
   *                                      </ul>
   * @throws MyHomeException if the request method is not GET or POST
   * @throws MyHomeException if the action is not found
   * @throws MyHomeException if request URI is not available ($_SERVER['REQUEST_URI'])
   */
  public function formAttributes($action, $requestMethod, $redirectUrl=null, $redirectUrlError=null) { //, $authType=null){
    if($requestMethod!=='GET'&&$requestMethod!=='POST')
      throw new MyHomeException('Request method not supported: '.$requestMethod);

    $actions=self::$ACTIONS_GET;
    if($requestMethod==='POST')
      $actions=self::$ACTIONS_POST;

    if(!isset($actions[$action]))
      throw new MyHomeException(sprintf('Action not found: %s, (%s request)',$action,$requestMethod));

    $actionSettings=$actions[$action];
    
    $redirect = !isset($actionSettings['redirect']) || $actionSettings['redirect']!==false;
    //myHome()->log->info("formAttributesauthType: " . $authType);
    $access = $actionSettings['access']; //$authType ? $authType : $actionSettings['access'];
    //myHome()->log->info("formAttributesAccess: " . $access);
    //myHome()->log->info('adminUrl: ' . admin_url());

    $attributes=[
      'url'=>esc_url(admin_url('admin-post.php')),
      'params'=>[
        'action'=>esc_attr(self::$WP_ACTION_NAME),
        self::$PARAM_ACTION=>esc_attr($action),
        self::$PARAM_NONCE=>esc_attr($this->createNonce($access))
      ]
    ];

    if($redirect){
      if(!isset($_SERVER['REQUEST_URI']))
        throw new MyHomeException('Request URI not available');
      $requestUri=$_SERVER['REQUEST_URI'];

      // If no URLs are provided, use the request URI to go back to the same page
      if($redirectUrl===null)
        $redirectUrl=$requestUri;
      if($redirectUrlError===null)
        $redirectUrlError=$requestUri;

      $attributes['params'][self::$PARAM_REDIRECT]=esc_url($redirectUrl);
      $attributes['params'][self::$PARAM_REDIRECT_ERROR]=esc_url($redirectUrlError);
    }

    return $attributes;
  }

  /**
   * Handles a MyHome action (one with action=$WP_ACTION_NAME set)
   *
   * <p>The specific action requested is determined here by querying the $PARAM_ACTION parameter</p>
   * <p>Triggered by the admin_post and admin_post_nopriv actions - note that all the actions defined in $ACTIONS
   * trigger the same WordPress action</p>
   *
   * @uses MyHomeAdminPostHandler::$ACTIONS_GET
   * @uses MyHomeAdminPostHandler::$ACTIONS_POST
   * @uses MyHome::abort()
   * @uses MyHomeHandler::requestMethod()
   * @uses MyHomeHandler::verifyAccess()
   * @uses MyHomeHandler::verifyNonce()
   * @uses MyHomeHandler::params() to get all the GET or POST parameters intended to be used by the action itself
   * @uses home_url() to get the root WordPress site URL if no redirect URL is provided
   * @uses wp_safe_redirect()
   */
  public function handleAction(){ // myHome()->log->info("\n\n---------- adminPost handleAction() ----------");
    try{
      $requestMethod=$this->requestMethod();

      if($requestMethod!=='GET'&&$requestMethod!=='POST')
        myHome()->abort(405,'Method not implemented: '.$requestMethod); // Method not implemented

      $actions=self::$ACTIONS_GET;
      if($requestMethod==='POST')
        $actions=self::$ACTIONS_POST;

      $action=$this->param(self::$PARAM_ACTION,$requestMethod);

      if(!isset($actions[$action]))
        myHome()->abort(404,sprintf('PostHandler Action not found: %s, %s',$requestMethod,$action)); // Not found

      $params = $this->params($requestMethod);
      $params[self::$PARAM_ACTION] = $action;

      //$callback=$actionSettings['callback'];
      //if(!is_callable([$this,$callback]))
      //  myHome()->abort(500,'Callback not callable: '.$callback); // Internal error

      $actionSettings = $actions[$action];
      $controller = $actionSettings['controller'];

      // TODO: There is a bug here where MyHome needs to redirect to logged in home if $action is 'login' and user is already logged in (therfore has no nonce)

      $access = $actionSettings['access'];
      /*$access = isset($actionSettings['access']) ? $actionSettings['access'] : call_user_func(function() {
          switch($_GET['myHomeAuth']) {
            case 'client':
              return self::ACCESS_CLIENT;
            default: // system
              return self::ACCESS_ALL;
          };
      }); 
      myHome()->log->info("\n\n---------- adminPost handleAction() ----------\n" . json_encode((object) [
        'type' => $requestMethod,
        'access' => $access,
        'params' => $params
      ], JSON_PRETTY_PRINT));*/

      if(!$this->verifyAccess($access)) // && $action != 'login')
        myHome()->abort(403,sprintf('Access denied (level required: %s)', $access)); // Forbidden

      // The redirect parameter is optional, and true by default
      $redirect=!isset($actionSettings['redirect'])||$actionSettings['redirect']!==false;
      //myHome()->log->info("redirect: " . $redirect);

      if(!$this->verifyNonce($access,$requestMethod))
        myHome()->abort(403,'Wrong nonce'); // Forbidden

      // Invoke the callback with all the GET or POST parameters (other than the action, the redirect URL and the nonce) as its parameter
      //$status=$this->$callback($this->params($requestMethod));
      $status = $this->performRequest($controller, $requestMethod, $params);
      //myHome()->log->info("adminPost status: " . json_encode($status) . ", redirect " . json_encode($redirect) . "\n");  

      // Before version 1.2, the user was redirected to the same page regardless of the request being successful
      // For example, when trying to access /houseDetails, a public user was taken to /login first; if login wasn't successful, he was redirected to /houseDetails and then back to /login
      if($redirect){
        // Get the redirect URL from the GET or POST parameter
        $redirectUrl=$this->param(self::$PARAM_REDIRECT,$requestMethod);
        $redirectUrlError=$this->param(self::$PARAM_REDIRECT_ERROR,$requestMethod);
        //myHome()->log->info("redirect: " . $redirectUrl);  

        // If not found or empty, redirect to the site homepage
        if(!$redirectUrl)
          $redirectUrl=home_url();
        if(!$redirectUrlError)
          $redirectUrlError=home_url();

        // See MyHomeAdminPostHandler::performRequest() for a description of the possible values of $status
        if($status!==false)
          if(is_string($status))
            wp_safe_redirect($status);
          else if(is_array($status))
            wp_safe_redirect(add_query_arg($status,$redirectUrl));
          else
            wp_safe_redirect($redirectUrl);
        else
          wp_safe_redirect($redirectUrlError);
      }
    }
    catch(MyHomeException $e){
      myHome()->handleError($e);
    }
  }

  /**
   * Registers the appropriate WordPress hooks
   *
   * The following hooks are registered:
   * <ul>
   * <li>admin_post: handles the request when a WordPress user is logged in</li>
   * <li>admin_post_nopriv: handles the request when a WordPress user is not logged in</li>
   * </ul>
   * Note that the complete hook names are something like "admin_post_MyHome" (if $WP_ACTION_NAME is defined as
   * "MyHome")
   *
   * @uses MyHomeHandler::$WP_ACTION_NAME to generate the correct hook name
   */
  public function setupHooks(){
    add_action('admin_post_'.self::$WP_ACTION_NAME,[$this,'handleAction']);
    add_action('admin_post_nopriv_'.self::$WP_ACTION_NAME,[$this,'handleAction']);
  }

  /**
   * Loads the appropriate controller class and runs the controller method according to the given request method
   *
   * Note that POST (non Ajax) requests shouldn't use myHome::abort() when an error occurs (the exception to this is
   * MyHomeAdminPostHandler::handleAction()), but store the error as flash data and display it in the following request
   *
   * @since 1.2 renamed from postRequest()
   * @since 1.2 added a return value
   * @uses  MyHome::runController()
   * @param string   $controller    the controller class name within the given subdirectory
   * @param string   $requestMethod the request method
   * @param string[] $params        POST parameters used by the controller
   * @return bool|mixed[]|string result, used to determine the redirect URL at handleAction():
   *                                <ul>
   *                                <li>true: successful request - redirect to $redirectUrl at handleAction()</li>
   *                                <li>false: failure - redirect to $redirectUrlError at handleAction()</li>
   *                                <li>array: successful request with added parameters - redirect to $redirectUrl at
   *                                handleAction() with given parameters (eg Maintence Job ID)</li>
   *                                <li>string: the returned value is a URL - redirect to that URL (eg the Submit
   *                                Review view)</li>
   *                                </ul>
   */
  private function performRequest($controller,$requestMethod,array $params){
    try{
      $result=myHome()->runController($controller,$requestMethod,$params);

      // No return code means no error
      if($result===null)
        $result=true;

      return $result;
    }
    catch(MyHomeException $e){
      myHome()->handleError($e);

      return false;
    }
  }
}
