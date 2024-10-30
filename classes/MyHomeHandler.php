<?php

/**
 * The MyHomeHandler class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeHandler'))
  return;

/**
 * The MyHomeHandler class
 *
 * Abstract class for action handlers
 */
abstract class MyHomeHandler{
  /**
   * The user is a WordPress administrator (an actual WordPress user)
   */
  const ACCESS_ADMIN='admin';
  /**
   * No restrictions - the user may or may not be a logged in client
   */
  const ACCESS_ALL='all';
  /**
   * The user is a logged in client
   */
  const ACCESS_CLIENT='client';
  /**
   * The user is not a logged in client
   */
  const ACCESS_PUBLIC='public';
  /**
   * The user is logged in with Facebook but has not chosen a job yet
   *
   * @since 1.4
   */
  const ACCESS_FACEBOOK_PARTIAL='facebookPartial';

  /**
   * Value for the WordPress action parameter (eg ?action=myhome for a GET request)
   */
  protected static $WP_ACTION_NAME='myhome';

  /**
   * Parameter for the specific MyHome action (eg &myHomeAction=logoff for the Logoff GET request)
   */
  protected static $PARAM_ACTION='myHomeAction';

  /**
   * Parameter for the redirect URL (eg &myHomeRedirect=http://website.com.au/)
   */
  protected static $PARAM_REDIRECT='myHomeRedirect';

  /**
   * Parameter for the redirect URL on error (eg &myHomeRedirectError=http://website.com.au/)
   *
   * @since 1.2
   */
  protected static $PARAM_REDIRECT_ERROR='myHomeRedirectError';

  /**
   * Parameter for the nonce value (eg &myHomeNonce=46d71a5b2e)
   */
  protected static $PARAM_NONCE='myHomeNonce';

  /**
   * Nonce action name for wp_create_nonce() used when the action requires admin access
   */
  protected static $NONCE_ADMIN='myhome-nonce-admin';

  /**
   * Nonce action name for wp_create_nonce() used when the action requires client access
   */
  protected static $NONCE_CLIENT='myhome-nonce-client';

  /**
   * Nonce action name for wp_create_nonce() used when the action requires public access
   */
  protected static $NONCE_PUBLIC='myhome-nonce-public';

  /**
   * Nonce action name for wp_create_nonce() used when the action requires all access
   */
  protected static $NONCE_ALL='myhome-nonce-all';

  /**
   * Nonce action name for wp_create_nonce() used when the action requires a "Facebook partial" access
   */
  protected static $NONCE_FACEBOOK_PARTIAL='myhome-nonce-facebook-partial';

  /**
   * Returns a nonce for the required access level
   *
   * If the nonce has already been created in this request, it returns it without calling wp_create_nonce() again
   *
   * @uses MyHomeHandler::$nonces
   * @uses MyHomeHandler::nonceName()
   * @param string $access the access level
   * @return string the nonce requested
   */
  protected function createNonce($access){
    $nonceName=$this->nonceName($access);

    //if(empty($this->nonces[$nonceName]))
      $this->nonces[$nonceName] = wp_create_nonce($nonceName);
    //myHome()->log->info('createNonce:' . $nonceName . ': ' . $this->nonces[$nonceName]);
    //myHome()->log->info("Nonces: " . var_export($this->nonces, true));

    return $this->nonces[$nonceName];
  }

  /**
   * Returns the nonce action name (used as wp_create_nonce()'s only parameter) for a given access level
   *
   * @param string $access the access level
   * @return string the nonce action name
   */
  protected function nonceName($access){
    switch($access){
      case self::ACCESS_ADMIN:
        return self::$NONCE_ADMIN;
      case self::ACCESS_CLIENT:
        return self::$NONCE_CLIENT;
      case self::ACCESS_PUBLIC:
        return self::$NONCE_PUBLIC;
      case self::ACCESS_ALL:
        return self::$NONCE_ALL;
      case self::ACCESS_FACEBOOK_PARTIAL:
        return self::$NONCE_FACEBOOK_PARTIAL;
      default:
        return '';
    }
  }

  /**
   * Returns a request parameter, stored in $_GET or $_POST arrays
   *
   * If the parameter is not found, it returns null
   *
   * @param string $parameter     the parameter name (eg "myHomeAction")
   * @param string $requestMethod the request method (GET or POST)
   * @return string|null the parameter value, if present, or null otherwise
   */
  protected function param($parameter,$requestMethod){
    $array=$requestMethod==='POST'?$_POST:$_GET;

    if(isset($array[$parameter]))
      return $array[$parameter];

    return null;
  }

  /**
   * Returns all the parameters intended to be used by an action handled by a subclass of MyHomeHandler
   *
   * The parameters "action", $PARAM_ACTION, $PARAM_REDIRECT, $PARAM_REDIRECT_ERROR and $PARAM_NONCE are stripped out
   *
   * @param string $requestMethod the request method (GET or POST)
   * @return string[] the parameter values (parameter names are stored as keys)
   */
  protected function params($requestMethod){
    $array=$requestMethod==='POST'?$_POST:$_GET;
    //myHome()->log->info('params() ' . json_encode($array, JSON_PRETTY_PRINT));
   // myHome()->log->info('files() ' . serialize($_FILES));

    $params=[];
    foreach($array as $key=>$value) {
      if(!in_array($key, [
          'action',
          //self::$PARAM_ACTION,
          self::$PARAM_REDIRECT,
          self::$PARAM_REDIRECT_ERROR,
          self::$PARAM_NONCE
        ]
      )) $params[$key] = $value;
    }

    return $params;
  }

  /**
   * Returns the HTTP request method
   *
   * If methods other than GET or POST were to be used, they could be emulated using a custom POST parameter (eg
   * _method=PUT)
   *
   * @uses $_SERVER['REQUEST_METHOD']
   * @return string the request method, or an empty string if it couldn't be determined
   */
  protected function requestMethod(){
    if(isset($_SERVER['REQUEST_METHOD']))
      return $_SERVER['REQUEST_METHOD'];

    return '';
  }

  /**
   * Checks that the user trying to execute an action has the required access
   *
   * @uses current_user_can() to check for the WordPress admin access
   * @uses MyHomeSession::guest() to check for a valid logged in client
   * @param string $access the required access level
   * @return bool whether the user has the required access
   */
  protected function verifyAccess($access){
    switch($access){
      case self::ACCESS_ADMIN:
        return current_user_can('manage_options');
      case self::ACCESS_CLIENT:
        return !myHome()->session->guest();
      case self::ACCESS_PUBLIC:
        return myHome()->session->guest();
      case self::ACCESS_ALL:
        return true;
      case self::ACCESS_FACEBOOK_PARTIAL:
        //myHome()->log->info('fbPartialAccess: ' . json_encode(myHome()->session->facebookPartialLogin()));
        return myHome()->session->facebookPartialLogin();
      default:
        return false;
    }
  }

  /**
   * Checks if the nonce value sent as either a GET or POST parameter is valid for a given access level
   *
   * @uses MyHomeHandler::param()
   * @uses MyHomeHandler::nonceName()
   * @param string $access        the required access
   * @param string $requestMethod the request method - used to know where to look for the $PARAM_NONCE parameter
   *                              return bool whether a nonce was found as a parameter and is a valid nonce
   * @return bool whether the nonce was correct or not
   */
  protected function verifyNonce($access,$requestMethod){
    $nonceName=$this->nonceName($access);
    $nonce=$this->param(self::$PARAM_NONCE, $requestMethod);
    myHome()->log->info(json_encode((object) [
      //'type' => $requestMethod,
      'access' => $access,
      'nonceName' => $nonceName,
      'nonceValue' => $nonce,
      'valid' => ($access == 'public' || $access == 'all') && wp_verify_nonce($nonce, $nonceName)
    ]));
    //myHome()->log->info("verifyNonce: " . $access . ", " . $requestMethod . ", " . $nonceName);
    //myHome()->log->info($nonceName . ': ' . $nonce);
    //myHome()->log->info("realNonce: " . (isset($this->nonces[$nonceName]) ? $this->nonces[$nonceName] : 'no nonce'));
    //myHome()->log->info("Nonces: " . var_export($this->nonces, true));

    if($access == 'public' || $access == 'all') 
      return true;
    else
      return $nonce !== null && wp_verify_nonce($nonce, $nonceName);
  }

  /**
   * Nonces generated in a request are stored in this array with the nonce name ($NONCE_ADMIN, and so on) as the key
   *
   * Actually, wp_create_nonce() doesn't return two different values for the same action name in the same request, so
   * this is simply useful to avoid calling wp_create_nonce() more than once
   */
  private $nonces=[];
}
