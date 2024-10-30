<?php

/**
 * The MyHomeSession class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeSession'))
  return;

use Facebook\GraphNodes\GraphUser;

/**
 * The MyHomeSession class
 *
 * Manages MyHome sessions
 *
 * @uses MyHomeStorage
 */
class MyHomeSession{
  /**
   * Name of the session value which stores the MyHome session ID
   */
  private static $NAME_SESSION_ID='session_id';

  /**
   * Name of the session value which stores the job details
   */
  private static $NAME_JOB_DETAILS='job_details';

  /**
   * Name of the session value which stores the timestamp of the last request
   */
  private static $NAME_LAST_TIME='last_time';

  /**
   * Name of the session value which stores the authentication headers used in API calls
   */
  private static $NAME_AUTHENTICATION='authentication';

  /**
   * Name of the session value which stores the Facebook flag - it indicates whether the session is a Facebook session
   *
   * @since 1.4
   */
  private static $NAME_SESSION_FACEBOOK='facebook';

  /**
   * Name of the session value which stores the list of available jobs for Facebook login
   *
   * @since 1.4
   */
  private static $NAME_FACEBOOK_AVAILABLE_JOBS;

  /**
   * Name of the session value which stores the authentication headers used in API /documents call
   *
   * @since 1.4
   */
  private static $NAME_AUTHENTICATION_SYSTEM='authentication_documents';

  /**
   * Session timeout limit in seconds since the last request - if $lastTime+$TIMEOUT<time(), the session has expired
   */
  private static $TIMEOUT=3600;

  /**
   * Constructor method
   *
   * It does the following:
   * <ul>
   * <li>Loads the MyHome session information, if any</li>
   * <li>Checks for MyHome session expiry - all the session information is cleared in the event of a timeout</li>
   * <li>Checks whether the last Facebook session has been closed</li>
   * </ul>
   */
  public function __construct(){
    $this->loadSession();

    if($this->activeSession()){
      $this->checkTimeout();

      if($this->isFacebook())
        $this->checkFacebookLogout();
    }
  }

  /**
   * Returns true if there is an active session of any kind
   *
   * @since 1.4
   * @return bool
   */
  public function activeSession(){
    return $this->get(self::$NAME_SESSION_ID)!==null;
  }

  /**
   * Checks if there is an active Facebook session
   *
   * @since 1.4
   * @uses  MyHomeFacebook::detectLogin()
   * @return GraphUser|null if a Facebook session is detected, the user details (including email
   * address and picture URL)*/
   
  public function detectFacebookLogin(){
    if(myHome()->facebook->appSetUp()) {
      $fbLoginStatus = myHome()->facebook->detectLogin();
      //echo(' detectFacebookLogin:' . var_export($fbLoginStatus, true));
      if($fbLoginStatus) 
        return $fbLoginStatus;
    } //else
    return null;
  }

  /**
   * Returns true if the active Facebook session has more than one available job to choose from
   *
   * @since 1.4
   * @return bool
   */
  public function facebookMultipleJobsAvailable(){
    return count($this->facebookAvailableJobs)>1;
  }

  /**
   * Returns true if the active session is a Facebook session but the user has not yet chosen a job
   *
   * @since 1.4
   * @return bool
   */
  public function facebookPartialLogin(){
    return $this->isFacebook() && ($this->getJobDetails()===null || $this->getAuthentication()===null);
  }

  /**
   * Returns the list of available jobs when the session is a Facebook session
   *
   * @since 1.4
   * @return array
   */
  public function getFacebookAvailableJobs(){
    //echo(' getFacebookAvailableJobs: ' . json_encode($this->facebookAvailableJobs, JSON_PRETTY_PRINT));
    return $this->facebookAvailableJobs;
  }

  /**
   * Returns the authentication headers
   *
   * @return array|null the authentication headers, or null if the user is a guest
   */
  public function getAuthentication(){
    return $this->authentication;
  }

  /**
   * Returns the authentication headers for the /documents API call
   *
   * @since 1.4
   * @return array|null the authentication headers, or null if the user is a guest
   */
  public function getAuthenticationDocuments(){
    return $this->authenticationDocuments;
  }

  /**
   * Returns the job details
   *
   * @return stdClass|null the job details, or null if the user is a guest
   */
  public function getJobDetails(){
    return $this->jobDetails;
  }

  /**
   * Returns the session ID
   *
   * @return int|null the session ID, or null if the user is a guest
   */
  public function getSessionId(){
    return $this->sessionId;
  }

  /**
   * Used to determine if the user is a guest
   *
   * If the active session is a partial Facebook session, it returns true - the user shouldn't access any restricted
   * content until a valid job number is provided
   *
   * @uses MyHomeSession::$NAME_SESSION_ID
   * @return bool whether the user is a guest or a logged in client
   */
  public function guest(){
    return !$this->activeSession() || $this->facebookPartialLogin();
  }

  /**
   * Returns the Facebook flag
   *
   * @since 1.4
   * @return bool
   */
  public function isFacebook(){
    return $this->activeSession()&&$this->facebook;
  }

  /**
   * Stores the information needed to handle the session of a recently logged in client
   *
   * <p>This method should be called right after a successful response from the login API call</p>
   * <p>It stores the MyHome session ID, the job details and the authentication headers required by the API to make
   * authenticated calls</p>
   * <p>It also resets the timestamp of the last request to the current timestamp</p>
   *
   * @since 1.4 added $facebook, $authenticationDocuments, and $facebookAvailableJobs parameters; $jobDetails and
   * $authentication can be null - for partial Facebook logins
   * @uses  MyHomeSession::loadSession() to update the session properties
   * @param bool          $facebook                the MyHome session ID, as returned by the login command
   * @param int           $sessionId               the MyHome session ID, as returned by the login command
   * @param stdClass|null $jobDetails              the whole response received from the job command - used to speed up
   *                                               the Contract Header shortcode
   * @param array|null    $authentication          the authentication headers, to be used by subsequent API calls
   * @param array|null    $authenticationDocuments the authentication headers to be used for the /documents API call
   *                                               - if null, use $authentication
   * @param array|null    $facebookAvailableJobs   the list of available job numbers for the active Facebook session
   */
  public function login($facebook,$sessionId,stdClass $jobDetails=null,array $authentication=null,array $authenticationSystem=null,array $facebookAvailableJobs=null){
    if(!$facebook)
      if($jobDetails===null||$authentication===null){
        myHome()->log->error('No job details and/or authentication provided for a non-Facebook login');
        return;
      }

    $this->put(self::$NAME_SESSION_FACEBOOK,$facebook);
    $this->put(self::$NAME_SESSION_ID,$sessionId);
    $this->updateJobDetails($jobDetails); //myHome()->log->info(json_encode($jobDetails));
    $this->put(self::$NAME_AUTHENTICATION, $authentication);
    $this->put(self::$NAME_AUTHENTICATION_SYSTEM, $authenticationSystem ? $authenticationSystem : $authentication);
    //myHome()->log->info('authenticationUser: ' . serialize($authentication));
    //myHome()->log->info('authenticationSystem: ' . serialize($authenticationSystem));

    if(!$facebook)
      $this->put(self::$NAME_FACEBOOK_AVAILABLE_JOBS,null);
    // If $facebookAvailableJobs is null, don't overwrite it, as the second call to this method won't provide this value
    else if($facebookAvailableJobs!==null)
      $this->put(self::$NAME_FACEBOOK_AVAILABLE_JOBS,$facebookAvailableJobs);

    // Reset the timestamp of the last request
    $this->put(self::$NAME_LAST_TIME,time());

    // Update the session properties corresponding to the variables set above - doing this allows any filtering applied by loadSession()
    $this->loadSession();

    if($this->getSessionId())
      myHome()->log->info(sprintf(__('User logged in (session ID %u)','myHome'),$this->getSessionId()));
  }

  /**
   * Wipes all the session data and regenerates the PHP session ID, effectively invalidating the session
   *
   * <p>This method should be called upon client logging off</p>
   * <p>It is used by checkTimeout() as well when the session expires</p>
   *
   * @since 1.4 added the $clearJobIfMoreAvailable parameter
   * @uses  MyHomeSession::loadSession() to update the session properties
   * @param bool $clearJobIfMoreAvailable if the active session is a Facebook session with multiple jobs available,
   *                                      whether this method should just clear the job details (which will allow the
   *                                      user to choose another one without logging in again) or do a complete logoff
   */
  public function logoff($clearJobIfMoreAvailable=true){
    // If we're under a complete Facebook session, make it partial again, so that the user can choose a different job
    // (only if more than one job is available)
    /*if(
      $clearJobIfMoreAvailable && 
      $this->isFacebook() && 
      !$this->facebookPartialLogin() && 
      $this->facebookMultipleJobsAvailable()
    ) {*/
      $this->updateJobDetails(null);
      $this->put(self::$NAME_AUTHENTICATION,null);
      $this->put(self::$NAME_AUTHENTICATION_SYSTEM,null);
    //} else {
      
      myHome()->log->info(sprintf(__('User logged off (session ID %u)','myHome'),$this->getSessionId()));

      // Empty the storage container, so that variables are not kept by PHP across different MyHome sessions (but under the same PHP session)
      myHome()->storage->resetContainer();
      //myHome()->storage->delete('facebookToken');

      // Try redirect home (added 6-7-17)
      wp_redirect( home_url() );
    //}

    // Update the session properties
    $this->loadSession();
  }

  /**
   * Updates the job details - used after updating client data from the Maintenance Confirmation form
   *
   * @since 1.2
   * @since 1.4 $jobDetails can be null - for partial Facebook logins
   * @param stdClass|null $jobDetails the whole response received from the job command
   */
  public function updateJobDetails(stdClass $jobDetails=null){
    $this->put(self::$NAME_JOB_DETAILS,$jobDetails);
  }

  /**
   * Checks whether the session, if any, should expire, and invalidates it in that event
   *
   * If the session shouldn't expire, it refreshes the timestamp
   *
   * @uses MyHomeSession::lastTime to check for the timestamp of the last request
   * @uses MyHomeSession::$TIMEOUT to check for the timeout limit, in seconds
   * @uses MyHomeSession::logoff() to invalidate the session
   */
  private function checkTimeout() {
    // Return if no session is available
    if($this->guest())
      return;

    // Log off if no time information is available
    if($this->lastTime===null) {
      myHome()->log->info(sprintf(__('Timeout (session ID %u): last time not available','myHome'),
        $this->getSessionId()));

      $this->logoff();
    }
    // Log off if the time elapsed since the last request is greater than the timeout limit
    else if(time()>=$this->lastTime+self::$TIMEOUT) {
      myHome()->log->info(sprintf(__('Timeout (session ID %u): session expired (%u>=%u+%u)','myHome'),
        $this->getSessionId(),time(),$this->lastTime,self::$TIMEOUT));

      $this->logoff();
    }
    // If the session is still valid, refresh the timestamp
    else {
      $this->put(self::$NAME_LAST_TIME,time());
      $this->loadSession();
    }
  }

  /**
   * Returns a variable associated with the current PHP session, if present, or null otherwise
   *
   * @uses MyHomeStorage::get()
   * @param string $name the variable name (eg $NAME_AUTHENTICATION)
   * @return mixed|null the variable value, if present, or null otherwise (note that the value can be null as well)
   */
  private function get($name) {
    return myHome()->storage->get($name);
  }

  /**
   * Updates the session properties
   *
   * These properties are read by other classes using the getter methods (eg myHome()->session->getAuthentication()
   * instead of myHome()->session->get(MyHome::$NAME_AUTHENTICATION))
   */
  private function loadSession(){
    $this->facebook=$this->get(self::$NAME_SESSION_FACEBOOK);
    $this->sessionId=$this->get(self::$NAME_SESSION_ID);
    $this->jobDetails=$this->get(self::$NAME_JOB_DETAILS);
    $this->authentication=$this->get(self::$NAME_AUTHENTICATION);
    $this->authenticationDocuments=$this->get(self::$NAME_AUTHENTICATION_SYSTEM);
    $this->lastTime=$this->get(self::$NAME_LAST_TIME);
    $this->facebookAvailableJobs=$this->get(self::$NAME_FACEBOOK_AVAILABLE_JOBS);
  }

  /**
   * Changes a variable associated with the current PHP session
   *
   * @uses MyHomeStorage::put()
   * @param string $name  the variable name (eg $NAME_AUTHENTICATION)
   * @param mixed  $value the new value
   */
  private function put($name,$value){
    $caller = @debug_backtrace()[1]['class'] . "." . @debug_backtrace()[1]['function'] . "() [" . @debug_backtrace()[0]['line'] . "]";
    //myHome()->log->info($caller . ' Save session var: ' . $name . ' - ' . json_encode($value));
    myHome()->storage->put($name,$value);
  }

  /**
   * Checks if the last active Facebook session has been closed
   *
   * If the session has been closed, the MyHome session must expire as well
   *
   * @since 1.4
   */
  private function checkFacebookLogout(){ //echo('checkFacebookLogout' . var_export($this->detectFacebookLogin(), true));
    if($this->detectFacebookLogin()===null)
      // Always do a complete logoff
      $this->logoff(false);
  }

  /**
   * MyHome session ID, returned by the login API call (null if the user is a guest)
   *
   * @var int|null
   */
  private $sessionId;

  /**
   * Job details for the logged in client (null if the user is a guest)
   *
   * This is cached because it is expected to be used frequently by the Contract Header shortcode, while it shouldn't
   * change too often
   *
   * @var stdClass|null
   */
  private $jobDetails;

  /**
   * Timestamp of the last request (null if the user is a guest)
   *
   * Updated by checkTimeout() if a client is logged in and the session didn't expire
   *
   * @var int|null
   */
  private $lastTime;

  /**
   * Authentication headers for the logged in client (null if the user is a guest)
   *
   * This array is retrieved by shortcode controllers to make API calls via MyHomeApi::get() or MyHomeApi::post()
   *
   * @var array|null
   */
  private $authentication;

  /**
   * Authentication headers for the logged in client (null if the user is a guest), to be used to perform a /documents
   * API call
   *
   * This array is retrieved by shortcode controllers to make API calls via MyHomeApi::get() or MyHomeApi::post()
   *
   * @since 1.4
   * @var array|null
   */
  private $authenticationDocuments;

  /**
   * Whether the session is a Facebook session
   *
   * @since 1.4
   * @var bool
   */
  private $facebook;

  /**
   * @since 1.4
   * @var array
   */
  private $facebookAvailableJobs;
}
