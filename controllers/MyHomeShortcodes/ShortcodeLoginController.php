<?php

/**
 * The ShortcodeLoginController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
	die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeLoginController'))
	return;

/**
 * The ShortcodeLoginController class
 *
 * Controller for the Login shortcode
 */
class ShortcodeLoginController extends MyHomeShortcodesBaseController{
	/**
	 * {@inheritDoc}
	 */
	public function doGet(array $params=[]){
	}

	/**
	 * {@inheritDoc}
	 */
	public function doPost(array $params=[]){
		// The action parameter is added by MyHomeAdminPostHandler::actionLogin() and is based on the myHomeAction parameter
		list($action)=$this->extractParams(['myHomeAction'],$params); 

		if($action==='login')
			return $this->login($params);
		else if($action==='jobs') 
			$this->selectJob($params);

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function doPostXhr(array $params=[]){
	}

	/**
	 * {@inheritDoc}
	 */
	public function doShortcode(array $atts=[]){ // myHome()->log->info('doShortcode');
		$atts=shortcode_atts(['facebook'=>'no'],$atts);

		$attFacebook=$atts['facebook'];

		if(!$this->verifyFacebook($attFacebook)){
			myHome()->handleError('Wrong Facebook attribute: '.$attFacebook);
			$attFacebook='no';
		}

		if($attFacebook==='yes'&&!myHome()->facebook->appSetUp()){
			myHome()->handleError('The Facebook App is not set up - disabling Facebook login');
			$attFacebook='no';
		}
		//echo('<div style="opacity: .2">curl exists: ' . function_exists('curl_reset') . "</div>");
		//var_dump(PHP_URL_HOST);

		// If a redirect GET parameter is present, use it
		// Otherwise, use the main page from the options page - if not set, use the site homepage
		$loginRedirect=$this->getParam('myHomeLoginRedirect');
		if($loginRedirect===null) {
			$mainPageId=myHome()->options->getMainPage();

			if($mainPageId)
				$loginRedirect=get_permalink($mainPageId);
			else
				$loginRedirect=home_url();
		}

		if($loginRedirect&&!myHome()->session->guest()){
			wp_safe_redirect($loginRedirect);
			die;
		}

		$facebookAppId='';
		$facebookError='';

		$facebookAppId = myHome()->options->getFacebookAppId();
		if(@$_GET['facebook'] && $this->checkFacebookLogin($loginRedirect)) return; /* {
			$facebookAppId = myHome()->options->getFacebookAppId();
			$result = $this->checkFacebookLogin($loginRedirect);
			//echo(' isFBLoggedIn:' . var_export($result, true));
			$fbUserToken = myHome()->facebook->userToken();

			if($result===true)
				return;
			else if($result&&is_string($result))
				$facebookError = $result;

			//if($jobSelectView) return $jobSelectView;
		}*/
		
		$this->loadView('shortcodeLogin','MyHomeShortcodes', compact('attFacebook','loginRedirect','facebookAppId','facebookError','fbUserToken'));
	}

	/**
	 * Attempts to login the client by quering the API with the clientlogin command
	 *
	 * <p>Upon successful login, it queries the API with the job command to cache the job details</p>
	 * <p>Note that the redirection is handled from MyHomeAdminPostHandler::handleAction()</p>
	 *
	 * @since 1.2 added a return value
	 * @uses  MyHomeApi::get()
	 * @uses  MyHomeApi::post()
	 * @param string[] POST parameters received (it should contain the job number, the username and the password)
	 * @return bool whether the login was successful
	 * @throws MyHomeException if an error occurred
	 */
	private function login(array $params){ //myHome()->log->info('login');
		list($jobNumber,$username,$password)=$this->extractParams(['myHomeJobNumber',
		  'myHomeUsername',
		  'myHomePassword'],$params);

		if(trim($jobNumber)===''){
			$this->flashVar('error',__('The Job Number provided is invalid','myHome'));
			return false;
		}
		if(trim($username)===''){
			$this->flashVar('error',__('The Username provided is invalid','myHome'));
			return false;
		}
		if(trim($password)===''){
			$this->flashVar('error',__('The Password provided is invalid','myHome'));
			return false;
		}

		$params=['username'=>$username,
		  'job'=>$jobNumber,
		  'password'=>$password];

		$api=myHome()->api;

    	//myHome()->log->info('login() ' . json_encode($params));
		$loginResponse = $api->post('clientLogin', $params);
   		//myHome()->log->info('loginresponse() ' . serialize($loginResponse));
		$loginError = true;

		// If there is a response from the clientlogin command, check the status
		if($loginResponse!==null) {
			// If status=OK and the ID is a numeric value, the attempt is successful
			if(!empty($loginResponse->status)&&$loginResponse->status==='OK')
				if(!empty($loginResponse->id)&&is_numeric($loginResponse->id)){
					// Generate the authentication headers to be used by all the authenticated API calls during all the MyHome session
					$authentication=$api->authenticationHeaders($username,$password,$jobNumber);
          			//myHome()->log->info('$authentication ' . serialize($authentication));

					// Retrieve and cache the job details
					$jobResponse=$api->get('job',$authentication,false);

					// Cast $jobResponse to array in order to check for an empty response (PHP <5.5 requires this do be done outside of empty())
					$jobResponseArray=(array)$jobResponse;

					// If the job response is successful, store the session ID, the job details and the authentication headers in the session
					if($jobResponse!==null&&!empty($jobResponseArray)){
						$apiKey=myHome()->options->getAdvertisingApiKey();
						$authenticationSystem=$api->authenticationHeadersApiKey($apiKey);

						myHome()->session->login(false,$loginResponse->id,$jobResponse,$authentication,$authenticationSystem);
						$loginError=false;
					} else myHome()->log->info('login response OK but no job ' . serialize($jobResponse));
				} else myHome()->log->info('login response OK but no id ' . serialize($loginResponse->id));
    	}

		// This may indicate that the system is not responding as well (if $loginResponse=null)
		if($loginError){
			$this->flashVar('error',__('Wrong Username, Password or Job Number','myHome'));

			// Remember the username and the job number until the next request
			$this->flashVar('username',$username);
			$this->flashVar('job',$jobNumber);

			if($api->getLastErrorType()===MyHomeApi::$ERROR_TYPE_API) // This one never happens
				$errorMessage=sprintf('Login error: The API returned an error (%s)',$api->getLastErrorMessage());
			else
				$errorMessage=sprintf('Login error: The API call could not be completed (%s)',$api->getLastErrorMessage());

			throw new MyHomeException($errorMessage);
		}

		return true;
	}

	/**
	 * Verifies the value of the facebook shortcode attribute provided
	 *
	 * @since 1.4
	 * @param string $facebook the facebook attribute value to check
	 * @return bool whether the attribute is valid or not (it must be "yes" or "no")
	 */
	private function verifyFacebook($facebook){
		return in_array($facebook,['no','yes']);
	}

	/**
	 * @since 1.4
	 * @param string $email the email address to query
	 * @return string[] the job numbers list associated to the account's email address
	 */
	private function jobsForEmail($email){
		$api=myHome()->api;

		$apiKey=myHome()->options->getAdvertisingApiKey();
		$authentication=$api->authenticationHeadersApiKey($apiKey);

		$jobs=$api->post('jobsbyemail',['email'=>$email],$authentication,true);

		if(!is_array($jobs))
			$jobs=[];

		$jobNumbers=array_map(function($job){
		  return isset($job->job)?$job->job:null;
		},$jobs);

		$jobNumbers=array_filter($jobNumbers,'strlen');

		return $jobNumbers;
	}

	/**
	 * Checks and handles the status of the underlying Facebook session
	 *
	 * <p>Precondition: there is not yet a complete Facebook session (ie a MyHome session linked to the underlying
	 * Facebook session and with a job number selected)</p>
	 * <p>Possible results:</p>
	 * <ul>
	 * <li>If an underlying Facebook session is detected, it queries the API server for a list of jobs linked to the
	 * user's email address; if only one job is received, it creates a complete MyHome session and redirects to
	 * $loginRedirect</li>
	 * <li>If more than one job is received, it loads the login.jobsList subview in order to request the job number</li>
	 * </ul>
	 *
	 * @since 1.4
	 * @param string $loginRedirect
	 * @return bool|string if true, it means the main login view should not be loaded; if false or string, it means
	 * it should - if the return value is a string, it also sets the Facebook error variable to this value
	 */
	private function checkFacebookLogin($loginRedirect) {
		$user = myHome()->session->detectFacebookLogin();
    	//echo(' checkFacebookLogin: ' . var_export($user, true));

		// If a Facebook login is detected...
		if($user !== null) {
			$email = $user->getEmail();

			if(!myHome()->session->activeSession()) {
				$jobsList = $this->jobsForEmail($email);
				myHome()->session->login(true,0,null,null,null,$jobsList);
			} else
				$jobsList = myHome()->session->getFacebookAvailableJobs();

			if(!$jobsList) 
				$error = __('There are no jobs associated with this Facebook account','myHome');
			
				//return __('There are no jobs associated with this Facebook account','myHome');
			// TODO: This cannot be done here
			/*else if(count($jobsList)===1) {
			  $result=$this->completeFacebookLogin($email,$jobsList[0]);

			  if($result['ok']){
			    wp_safe_redirect($loginRedirect);
			    die;
			  } else return $result['error'];
			}*/
			// Partial login - the user will need to choose a job from the dropdown
			//else 
				$picture = $user->getPicture();

				$this->loadView(['shortcodeLogin','jobsList'],'MyHomeShortcodes',compact('email','picture','jobsList','loginRedirect','error'));
				return true;
			
		}

		return false;
	}

	/**
	 * @since 1.4
	 * @param string[] POST parameters received (it should contain the job number, the username and the password)
	 * @return bool whether the job selection was successful
	 */
	//public $isSelectJob;// = false;

	private function selectJob($params){ // myHome()->log->info('selectJob');
		list($email,$jobNumber)=$this->extractParams(['myHomeEmail', 'myHomeJobNumber'], $params);

		if(trim($email)===''){
			$this->flashVar('error',__('The Email Address provided is invalid','myHome'));
			//return false;
		}
		if(trim($jobNumber)===''){
			$this->flashVar('error',__('The Job Number provided is invalid','myHome'));
			//return false;
		}

		$result = $this->completeFacebookLogin($email,$jobNumber);

		if(isset($result['error'])) {
			$this->flashVar('error',$result['error']);
			return false;
		}

		if(isset($result['exception']))
			throw $result['exception'];

		return $result['ok'];
	}

	/**
	 * @since 1.4
	 * @param string $email
	 * @param string $jobNumber
	 * @return mixed[]
	 * @throws MyHomeException
	 */
	private function completeFacebookLogin($email,$jobNumber){
		$api=myHome()->api;
		$apiKey=myHome()->options->getAdvertisingApiKey();

		$params=[
			'username'=>$email,
			'job'=>$jobNumber,
			'password'=>$apiKey
		];

		$loginResponse=$api->post('clientlogin',$params,null,false);
		$loginError=true;

		// If there is a response from the clientlogin command, check the status
		if($loginResponse!==null)
			// If status=OK and the ID is a numeric value, the attempt is successful
			if(!empty($loginResponse->status)&&$loginResponse->status==='OK')
				if(!empty($loginResponse->id)&&is_numeric($loginResponse->id)){
					// Generate the authentication headers to be used by all the authenticated API calls during all the MyHome session
					$authentication=$api->authenticationHeaders($email,$apiKey,$jobNumber);
					$authenticationDocuments=$api->authenticationHeadersApiKey($apiKey);

					// Retrieve and cache the job details
					$jobResponse=$api->get('job',$authentication,false);

					// Cast $jobResponse to array in order to check for an empty response (PHP <5.5 requires this do be done outside of empty())
					$jobResponseArray=(array)$jobResponse;

					// If the job response is successful, store the session ID, the job details and the authentication headers in the session
					if($jobResponse!==null&&!empty($jobResponseArray)){
						myHome()->session->login(true,$loginResponse->id,$jobResponse,$authentication,$authenticationDocuments);
						$loginError=false;
					}
				}

		if(!$loginError)
			return ['ok'=>true];
		// This may indicate that the system is not responding as well (if $loginResponse=null)
		else{
			if($api->getLastErrorType()===MyHomeApi::$ERROR_TYPE_API) // This one never happens
				$errorMessage=sprintf('Login error: The API returned an error (%s)',$api->getLastErrorMessage());
			else
				$errorMessage=sprintf('Login error: The API call could not be completed (%s)',$api->getLastErrorMessage());

			//myHome()->session->logoff();
			return [
				'ok'=>false,
				'error'=>__('Could not authenticate with the MyHome server','myHome'),
				'exception'=>new MyHomeException($errorMessage)
			];
		}
	}
}
