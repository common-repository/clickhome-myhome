<?php

/**
 * The AdminPageDebugConsoleController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageDebugConsoleController'))
  return;

/**
 * The AdminPageDebugConsoleController class
 *
 * Controller for the Debug Console admin page view
 */
class AdminPageDebugConsoleController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeDebugConsole';

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $this->loadView('adminPageDebugConsole','MyHomeAdmin');
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    list($jobNumber,$username,$password,$command,$params)=$this->extractParams(['myHomeJobNumber',
      'myHomeUsername',
      'myHomePassword',
      'myHomeCommand',
      'myHomeParams'],$params);

    // Remember the authentication fields, so that the user doesn't need to repeat them
    $this->flashVar('jobNumber',$jobNumber);
    $this->flashVar('username',$username);
    $this->flashVar('password',$password);

    $command=explode('/',$command);

    foreach($command as $key=>$commandPart)
      if(preg_match('|^{([a-z]+)}$|',$commandPart,$requiredParam)){
        $requiredParam=$requiredParam[1];

        if(!isset($params[$requiredParam])||$params[$requiredParam]===''||!is_numeric($params[$requiredParam])){
          $this->flashVar('error',sprintf(__('Missing parameter or not numeric: %s','myHome'),$requiredParam));

          return;
        }

        $command[$key]=$params[$requiredParam];
      }

    $api=myHome()->api;

    $loginNotRequired=['housedetails','displays'];

    // If login is not required or login details are not provided, try to execute the command without authentication
    $authentication=null;

    if(!in_array($command[0],$loginNotRequired))
      // If authentication is provided for this command, try to login first
      if($jobNumber!==''&&$username!==''&&$password!==''){
        $loginError=true;

        $params=['username'=>$username,
          'job'=>$jobNumber,
          'password'=>$password];

        $loginResponse=$api->post('clientlogin',$params,null,false);

        // If the response has status=OK and a numeric ID, the login is successful
        if($loginResponse!==null)
          if(!empty($loginResponse->status)&&$loginResponse->status==='OK')
            if(!empty($loginResponse->id)&&is_numeric($loginResponse->id))
              $loginError=false;

        // If the login was successful, create the authentication headers for the command and execute it
        if(!$loginError)
          $authentication=$api->authenticationHeaders($username,$password,$jobNumber);
        else{
          $this->flashVar('error',sprintf(__('Login error (%s)','myHome'),$api->getLastErrorMessage()));

          return;
        }
      }

    $useAdvertisingKey=['housedetails','displays','maintenancetypes'];

    if(in_array($command[0],$useAdvertisingKey)){
      $apiKey=myHome()->options->getAdvertisingApiKey();

      if(!$apiKey){
        $this->flashVar('error',__('Advertising API key not set','myHome'));

        return;
      }

      $authentication=$api->authenticationHeadersApiKey($apiKey);
    }

    // Do not set any default value if an empty response is received ($defaultArray=null)
    $output=$api->get($command,$authentication,null);

    // If a valid output is returned, remember it so it is displayed in the Debug Console page
    if($output!==null){
      $this->flashVar('response',true);
      $this->flashVar('url',$api->getLastUrl());
      $this->flashVar('output',$output);
    }
    else if($api->getLastErrorType()===MyHomeApi::$ERROR_TYPE_API)
      $this->flashVar('error',sprintf(__('The API returned an error (%s)','myHome'),$api->getLastErrorMessage()));
    else
      $this->flashVar('error',
        sprintf(__('The API call could not be completed (%s)','myHome'),$api->getLastErrorMessage()));
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }
}
