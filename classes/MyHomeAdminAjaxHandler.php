<?php

/**
 * The MyHomeAdminAjaxHandler class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeAdminAjaxHandler'))
  return;

/**
 * The MyHomeAdminAjaxHandler class
 *
 * <p>Handles XmlHttpRequest (Ajax) POST requests received via admin-ajax.php</p>
 * <p>Note that, despite its name, it is not used in the admin pages nor it has any relation with the MyHomeAdmin
 * class</p>
 */
class MyHomeAdminAjaxHandler extends MyHomeHandler{
  /**
   * The request returns HTML content
   */
  const RETURN_HTML='text/html; charset=UTF-8';

  /**
   * The request returns JSON content
   */
  const RETURN_JSON='application/json; charset=UTF-8';

  /**
   * Action definitions
   *
   * <ul>
   * <li>Array key: action parameter value (eg $PARAM_ACTION=calendar)</li>
   * <li>callback: name of the method within this class which will handle the action (eg $this->actionCalendar())</li>
   * <li>access: required access needed to handle the action (eg ACCESS_CLIENT for a logged in client) - a different
   * nonce is used for each access level</li>
   * <li>return: content returned by the request, which is set as the Content-Type header value in the response</li>
   * </ul>
   *
   * @see MyHomeHandler for the access level constants
   */
  private static $ACTIONS=[
    /*'document'=>[ // Single handler for both auth types
      'controller'=>'ShortcodeDocumentsController',
    ],*/
    'systemDocument'=>[
      'access'=>self::ACCESS_ALL,
      'redirect'=>false
    ],
    'clientDocument'=>[
      'access'=>self::ACCESS_CLIENT,
      'redirect'=>false
    ],

    'calendar'=>[
      'controller'=>'ShortcodeCalendarController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_JSON
    ],

    'notes'=>[
      'controller'=>'ShortcodeNotesController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_HTML
    ],

    'share'=>[
      'controller'=>'ShortcodePhotosController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_JSON
    ],

    'variationApprove'=>[
      'controller'=>'ShortcodeTenderVariationsController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_JSON
    ],

    'variationReject'=>[
      'controller'=>'ShortcodeTenderVariationsController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_JSON
    ],

    'packageEdit'=>[
      'controller'=>'ShortcodeTenderPackagesController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_JSON
    ],

    'selectionEdit'=>[
      'controller'=>'ShortcodeTenderSelectionsEditController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_JSON
    ],

    'emailMySelections'=>[
      'controller'=>'ShortcodeTenderSelectionsEmailController',
      'access'=>self::ACCESS_CLIENT,
      'return'=>self::RETURN_JSON
    ]
  ];

  /**
   * Handles a MyHome action (one with action=$WP_ACTION_NAME set)
   *
   * <p>The specific action requested is determined here by querying the $PARAM_ACTION parameter</p>
   * <p>Triggered by the wp_ajax and wp_ajax_nopriv actions - note that all the actions defined in $ACTIONS trigger the
   * same WordPress action</p>
   *
   * @uses MyHomeAdminAjaxHandler::$ACTIONS
   * @uses MyHome::abort()
   * @uses MyHomeHandler::requestMethod() to check if the request method is POST
   * @uses MyHomeHandler::verifyAccess()
   * @uses MyHomeHandler::verifyNonce()
   * @uses MyHomeHandler::params() to get all the POST parameters intended to be used by the action itself
   */
  public function handleAction(){ // myHome()->log->info("\n\n---------- adminAjax handleAction() ----------");
    try{
      $requestMethod=$this->requestMethod();

      if($requestMethod!=='POST')
        myHome()->abort(405,'Method not implemented: '.$requestMethod); // Method not implemented

      $action=$this->param(self::$PARAM_ACTION,'POST');
      if(!isset(self::$ACTIONS[$action]))
        myHome()->abort(404,sprintf('AjaxHandler Action not found: %s %s',$requestMethod,$action)); // Not found

      $actionSettings=self::$ACTIONS[$action];

      //$callback=$actionSettings['callback'];
      //if(!is_callable([$this,$callback])) 
      //  myHome()->abort(500,'Callback not callable: '.$callback); // Internal error

      $access=$actionSettings['access'];
      if(!$this->verifyAccess($access))
        myHome()->abort(403,sprintf('Access denied (level required: %s)',$access)); // Forbidden

      // The POST parameter is needed by verifyNonce() to determine where to look for the nonce parameter - it is assumed to be POST
      if(!$this->verifyNonce($access,'POST'))
        myHome()->abort(403,'Wrong nonce'); // Forbidden

      @header('Content-Type: '.$actionSettings['return']);

      // Invoke the callback with all the POST parameters (other than the action and the nonce) as its parameter
      //$this->$callback($this->params('POST'));
      //myHome()->log->info('$this->params(POST) ' . serialize($this->params('POST')));
      //myHome()->log->info('$actionSettings ' . serialize($actionSettings));
      $this->xhrRequest($actionSettings['controller'], $this->params('POST'));

      // Prevent WordPress from appending a "0" to the response
      die;
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
   * <li>wp_ajax: handles the Ajax request when a WordPress user is logged in</li>
   * <li>wp_ajax_nopriv: handles the Ajax request when a WordPress user is not logged in</li>
   * </ul>
   * Note that the complete hook names are something like "wp_ajax_MyHome" (if $WP_ACTION_NAME is defined as "MyHome")
   *
   * @uses MyHomeHandler::$WP_ACTION_NAME to generate the correct hook name
   */
  public function setupHooks(){
    add_action('wp_ajax_'.self::$WP_ACTION_NAME,[$this,'handleAction']);
    add_action('wp_ajax_nopriv_'.self::$WP_ACTION_NAME,[$this,'handleAction']);
  }

  /**
   * Returns an array containing the settings needed to perform a specific request
   *
   * <p>This method is intended to be used by shortcodes which need to invoke jQuery's $.post() method</p>
   * <p>The following is an example of the POST parameters used in a valid XHR request:</p>
   * <ul>
   * <li>action: MyHome</li>
   * <li>myHomeAction: calendar</li>
   * <li>myHomeNonce: 6236af3eaa</li>
   * <li>myHomeMonth: 6</li>
   * <li>myHomeYear: 2014</li>
   * </ul>
   *
   * @uses MyHomeAdminAjaxHandler::$ACTIONS
   * @uses MyHomeHandler::createNonce()
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
   * @throws MyHomeException if the action is not found
   */
  public function xhrAttributes($actions = null){ //myHome()->log->info('xhrAttributes');
    // As xhrAttributes() can only be executed once per page, it seemed a bit silly to lock eachpage into one type of xhr request
    // Generate nonces for each potential ajax action
    if(isset($actions)) {
      if(is_string($actions)) $actions = array($actions);

      // Unlike MyHomeAdminPostHandler::formAttributes(), these strings are not escaped, as they're not meant to be directly included in the shortcode output
      $attributes=[
        'url' => admin_url('admin-ajax.php'),
        'actions' => array()
      ];

      foreach ($actions as $action) {
        if(!isset(self::$ACTIONS[$action])) {
          myHome()->log->error('Action not found: '.$action);
          throw new MyHomeException('Action not found: '.$action);
        } else {
          $actionSettings=self::$ACTIONS[$action];

          //myHome()->log->info("xhrAttributesAccess: " . json_encode($_GET));
          //$access = isset($actionSettings['access']) ? $actionSettings['access'] : null;

          array_push($attributes['actions'], array(
            'action' => self::$WP_ACTION_NAME, // "myhome"
            self::$PARAM_ACTION => $action,
            self::$PARAM_NONCE => $this->createNonce($actionSettings['access']) //$access)
          ));
        }
      }

      return $attributes;
    } else return false;
  }

  /**
   * Loads the appropriate controller class and runs its method to handle Ajax requests
   *
   * @uses MyHome::runController()
   * @param string   $controller the controller class name within the given subdirectory
   * @param string[] $params     POST parameters used by the controller
   */
  private function xhrRequest($controller,array $params){
    // myHome()->log->info('xhrRequest ' . $controller . json_encode($params, JSON_PRETTY_PRINT));
    try{
      myHome()->runController($controller,'XHR',$params);
    }
    catch(MyHomeException $e){
      //myHome()->log->info('xhrRequest fail' . serialize($e));
      myHome()->handleError($e);
    }
  }
}
