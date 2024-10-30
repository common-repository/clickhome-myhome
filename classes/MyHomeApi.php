<?php

/**
 * The MyHomeApi class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeApi'))
  return;

/**
 * The MyHomeApi class
 *
 * Executes MyHome API commands and provides some information about the last call to the API
 */
class MyHomeApi{
  /**
   * An error triggered by the API in its response
   */
  public static $ERROR_TYPE_API=1;

  /**
   * Any other kind of error (eg endpoint unreachable)
   */
  public static $ERROR_TYPE_OTHER=2;

  /**
   * Command prefix - usually, a constant relative path which is placed between the base URL and the command itself
   *
   * For example, if the endpoint is "http://endpoint.com.au" and the prefix is "clickhome.myhome/v2", the full URL for
   * a call can be "http://endpoint.com.au/clickhome.myhome/v2/jobs"
   */
  private static $COMMAND_PREFIX='clickhome.myhome/v2/';

  /**
   * Constructor method
   *
   * It does the following:
   * <ul>
   * <li>Initialises some variables</li>
   * <li>Creates the cURL handle</li>
   * </ul>
   *
   * @uses curl_init()
   * @uses MyHomeOptions::getEndpoint() to get the API endpoint base URL
   */
  public function __construct(){
    // Make sure we use the same endpoint in every call performed during the same request
    $this->endpoint=myHome()->options->getEndpoint();

    // Do not trigger an error if cURL is not installed - instead, any request will return an error
    if(function_exists('curl_init')) {
      $this->curl = curl_init();
      //myHome()->log->info('curl: ' . $this->curl);
    } else
      myHome()->handleError(__('cURL extension is not installed','myHome'));
  }

  /**
   * Destructor method
   *
   * Closes the cURL handle
   *
   * @uses curl_close()
   */
  public function __destruct(){
    if(isset($this->curl))
      curl_close($this->curl);
  }

  /**
   * Generates the array needed by MyHomeApi::get() to make an authenticated call
   *
   * @param string $username  the username
   * @param string $password  the password
   * @param string $jobNumber the job number
   * @return string[] the authentication headers array:
   *                          <ul>
   *                          <li>authorization: value for the "Authorization" HTTP header</li>
   *                          <li>contractNumber: value for the "ContractNumber" HTTP header</li>
   *                          </ul>
   */
  public function authenticationHeaders($username,$password,$jobNumber){
    return [
      'authorization'=>'Basic '.base64_encode(sprintf('%s:%s',$username,$password)),
      'contractNumber'=>$jobNumber
    ];
  }

  /**
   * Generates the array needed by MyHomeApi::get() to make an authenticated call using an API key (used by the
   * advertising module)
   *
   * @since 1.3
   * @param string $apiKey the API key
   * @return string[] the authentication headers array:
   *                       <ul>
   *                       <li>authorization: value for the "Authorization" HTTP header</li>
   *                       </ul>
   */
  public function authenticationHeadersApiKey($apiKey){
    return ['authorization'=>$apiKey];
  }

  /**
   * Performs a DELETE request to the API
   *
   * @since 1.2
   * @uses  MyHomeApi::request()
   * @param string|string[] $command        API command (eg "jobs"); it can contain resource IDs and subcommands (eg
   *                                        array("maintenancejobs",123,"maintenanceissues",456))
   * @param string[]|null   $authentication if not null, the authentication headers to use in the HTTP request
   *                                        (Optional - default null)
   * @param bool|null       $defaultArray   what to return when the parser returns an empty string (the original
   *                                        response may be empty or not):
   *                                        <ul>
   *                                        <li>true: the default value is an empty array (array()) - intended for
   *                                        collection of items (eg list of questions)</li>
   *                                        <li>false: the default value is an empty object (new stdClass) - intended
   *                                        for single items (eg login response)</li>
   *                                        <li>null: there is no default value, therefore it returns the parsed
   *                                        response as is</li>
   *                                        </ul>
   *                                        (Optional - default false)
   * @return array|stdClass|string|null the parsed response if the call was successful (it may be an empty string if
   *                                        the response was empty) or null otherwise
   */
  //public function delete($command,array $authentication=null,$defaultArray=false){
  //  return $this->request($command,'DELETE',[],$authentication,$defaultArray);
  public function delete($command,array $params,array $authentication=null,$defaultArray=false){
    return $this->request($command,'DELETE',$params,$authentication,$defaultArray);
  }

  /**
   * Downloads the content returned by a GET request to the API
   *
   * @since 1.2 changed $command from string to string|string[] and removed $resourceId
   * @uses  MyHomeApi::request()
   * @param string|string[] $command        API command (eg "jobs"); it can contain resource IDs and subcommands (eg
   *                                        array("maintenancejobs",123,"maintenanceissues",456))
   * @param string[]|null   $authentication if not null, the authentication headers to use in the HTTP request
   *                                        (Optional - default null)
   * @return string|null the response if the call was successful or null otherwise
   */
  public function download($command,array $authentication=null){
    //myHome()->log->info('download() ' . json_encode($command, JSON_PRETTY_PRINT) . ', auth: ' . serialize($authentication));
    return $this->request($command,'GET',[],$authentication,null,false,'*/*');
  }

  /**
   * Performs a GET request to the API
   *
   * @since 1.2 changed $command from string to string|string[]
   * @uses  MyHomeApi::request()
   * @param string|string[] $command        API command (eg "jobs"); it can contain resource IDs and subcommands (eg
   *                                        array("maintenancejobs",123,"maintenanceissues",456))
   * @param string[]|null   $authentication if not null, the authentication headers to use in the HTTP request
   *                                        (Optional - default null)
   * @param bool|null       $defaultArray   what to return when the parser returns an empty string (the original
   *                                        response may be empty or not):
   *                                        <ul>
   *                                        <li>true: the default value is an empty array (array()) - intended for
   *                                        collection of items (eg list of questions)</li>
   *                                        <li>false: the default value is an empty object (new stdClass) - intended
   *                                        for single items (eg login response)</li>
   *                                        <li>null: there is no default value, therefore it returns the parsed
   *                                        response as is</li>
   *                                        </ul>
   *                                        (Optional - default false)
   * @return array|stdClass|string|null the parsed response if the call was successful (it may be an empty string if
   *                                        the response was empty) or null otherwise
   */
  public function get($command,array $authentication=null,$defaultArray=false){
    return $this->request($command,'GET',[],$authentication,$defaultArray);
  }

  /**
   * Returns the response Content-Disposition of the last successful API call
   *
   * @return string|null the last response Content-Disposition, if available
   */
  public function getLastContentDisposition(){
    return $this->lastContentDisposition;
  }

  /**
   * Returns the response Content-Type of the last successful API call
   *
   * @return string|null the last response Content-Type, if available
   */
  public function getLastContentType(){
    return $this->lastContentType;
  }

  /**
   * If the last call wasn't successful, returns the error message
   *
   * @return string|null the last error message, if available
   */
  public function getLastErrorMessage(){
    return $this->lastErrorMessage;
  }

  /**
   * If the last call wasn't successful, returns the error type
   *
   * @return string|null the last error type, if available
   */
  public function getLastErrorType(){
    return $this->lastErrorType;
  }

  /**
   * Returns the URL of the last successful API call
   *
   * @return string|null the last URL, if available
   */
  public function getLastUrl(){
    return $this->lastUrl;
  }

  /**
   * Performs a POST request to the API
   *
   * @since 1.2 changed $command from string to string|string[]
   * @uses  MyHomeApi::request()
   * @param string|string[] $command        API command (eg "jobs"); it can contain resource IDs and subcommands (eg
   *                                        array("maintenancejobs",123,"maintenanceissues",456))
   * @param string[]        $params         command parameters (eg username, password and job number for the login
   *                                        command)
   * @param string[]|null   $authentication if not null, the authentication headers to use in the HTTP request
   *                                        (Optional - default null)
   * @param bool|null       $defaultArray   what to return when the parser returns an empty string (the original
   *                                        response may be empty or not):
   *                                        <ul>
   *                                        <li>true: the default value is an empty array (array()) - intended for
   *                                        collection of items (eg list of questions)</li>
   *                                        <li>false: the default value is an empty object (new stdClass) - intended
   *                                        for single items (eg login response)</li>
   *                                        <li>null: there is no default value, therefore it returns the parsed
   *                                        response as is</li>
   *                                        </ul>
   *                                        (Optional - default false)
   * @return array|stdClass|string|null the parsed response if the call was successful (it may be an empty string if
   *                                        the response was empty) or null otherwise
   */
  public function post($command,array $params,array $authentication=null,$defaultArray=false){
    return $this->request($command,'POST',$params,$authentication,$defaultArray);
  }

  /**
   * Performs a PUT request to the API
   *
   * @since 1.2
   * @uses  MyHomeApi::request()
   * @param string|string[] $command        API command (eg "jobs"); it can contain resource IDs and subcommands (eg
   *                                        array("maintenancejobs",123,"maintenanceissues",456))
   * @param string[]        $params         command parameters (eg username, password and job number for the login
   *                                        command)
   * @param string[]|null   $authentication if not null, the authentication headers to use in the HTTP request
   *                                        (Optional - default null)
   * @param bool|null       $defaultArray   what to return when the parser returns an empty string (the original
   *                                        response may be empty or not):
   *                                        <ul>
   *                                        <li>true: the default value is an empty array (array()) - intended for
   *                                        collection of items (eg list of questions)</li>
   *                                        <li>false: the default value is an empty object (new stdClass) - intended
   *                                        for single items (eg login response)</li>
   *                                        <li>null: there is no default value, therefore it returns the parsed
   *                                        response as is</li>
   *                                        </ul>
   *                                        (Optional - default false)
   * @return array|stdClass|string|null the parsed response if the call was successful (it may be an empty string if
   *                                        the response was empty) or null otherwise
   */
  public function put($command,array $params,array $authentication=null,$defaultArray=false){
    return $this->request($command,'PUT',$params,$authentication,$defaultArray);
  }

  /**
   * Checks for a valid cURL handle
   *
   * @uses MyHomeApi::$curl to check for a valid handle - if it is null, this means the constructor wasn't able to
   *       invoke curl_init()
   * @return bool whether the cURL handle is valid
   */
  private function checkCurl(){
    if($this->curl===null)
      return __('cURL extension is not installed','myHome');

    return null;
  }

  /**
   * Checks for a valid HTTP code returned by the server
   *
   * @uses curl_getinfo()
   * @uses curl_error() to retrieve an error message if the last HTTP code was below 200
   * @return string|null null if the response is correct (codes 200-299) or an error message otherwise
   */
  private function checkResponse(){
    $httpCode=(int)curl_getinfo($this->curl,CURLINFO_HTTP_CODE);

    if($httpCode>=200&&$httpCode<=299)
      return null;
    else if($httpCode>=300)
      return sprintf(__('HTTP code %u','myHome'),$httpCode);

    return curl_error($this->curl);
  }

  /**
   * Parses the response, which is expected to be JSON encoded
   *
   * @param string $response the raw response, as returned by curl_exec()
   * @return stdClass|null|string the parsed response:
   *                         <ul>
   *                         <li>An object (stdClass) if a non-empty and valid response is received</li>
   *                         <li>A null value if the response is not valid - as returned by json_decode()</li>
   *                         <li>An empty string if the response is empty (this includes a "null" content as well)</li>
   *                         </ul>
   */
  private function parse($response){
    if($response==='null'||$response==='')
      return '';

    return json_decode($response);
  }

  /**
   * Performs a request to the API
   *
   * @since 1.2 changed $command from string to string|string[] and removed $resourceId
   * @uses  curl_exec()
   * @uses  MyHomeApi::url() to get the full command URL
   * @uses  MyHomeApi::setLastError()
   * @uses  MyHomeApi::resetCurl() to set up the cURL handle in order to perform this request
   * @uses  MyHomeApi::checkCurl()
   * @uses  MyHomeApi::checkResponse()
   * @uses  MyHomeApi::parse()
   * @param string|string[] $command        API command (eg "jobs"); it can contain resource IDs and subcommands (eg
   *                                        array("maintenancejobs",123,"maintenanceissues",456))
   * @param string          $requestMethod  the request method (GET, POST, PUT, or DELETE)
   * @param string[]        $params         parameters (used in POST requests)
   * @param string[]|null   $authentication if not null, the authentication headers to use in the HTTP request
   *                                        (Optional - default null)
   * @param bool|null       $defaultArray   what to return when the parser returns an empty string (the original
   *                                        response may be empty or not):
   *                                        <ul>
   *                                        <li>true: the default value is an empty array (array()) - intended for
   *                                        collection of items (eg list of questions)</li>
   *                                        <li>false: the default value is an empty object (new stdClass) - intended
   *                                        for single items (eg login response)</li>
   *                                        <li>null: there is no default value, therefore it returns the parsed
   *                                        response as is</li>
   *                                        </ul>
   *                                        (Optional - default false)
   * @param bool            $parse          whether the content should be parsed (Optional - default true)
   * @param string          $acceptContent  the accepted content (Optional - default 'application/json')
   * @return array|stdClass|string|null the parsed response if the call was successful (it may be an empty string if
   *                                        the response was empty) or null otherwise
   * @throws MyHomeException if the request method is not GET, POST, PUT, or DELETE
   */
  private function request($command, $requestMethod, array $params, array $authentication=null, $defaultArray=false, $parse=true, $acceptContent='application/json'){ 
    //error_reporting(E_ALL);
    if(!in_array($requestMethod,['GET','POST','PUT','DELETE']))
      throw new MyHomeException('Request method not supported: '.$requestMethod);

    $url = $this->url($command);
    //myHome()->log->info("\n--------- request ----------\n");
    // myHome()->log->info(json_encode($url) . '    ' . json_encode($command));

    // Reset the last error properties
    $this->setLastError(null,null);

    $error=$this->checkCurl();
    if($error!==null){
      $this->setLastError($error,self::$ERROR_TYPE_OTHER);
      return null;
    }

    if($requestMethod==='GET'){
      $options=[CURLOPT_URL=>$url];
      $extraHeaders=[];
    } else {
      // The POST payload needs to be sent as application/x-www-form-urlencoded
      $options = [
        CURLOPT_URL=>$url,
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>json_encode($params)
      ]; // The fields are sent in JSON format
      $extraHeaders=['Content-Type: application/json'];

      if($requestMethod==='PUT')
        $options[CURLOPT_CUSTOMREQUEST]='PUT';
      else if($requestMethod==='DELETE')
        $options[CURLOPT_CUSTOMREQUEST]='DELETE';
    }
    //myHome()->log->info('port: ' . parse_url($this->endpoint, PHP_URL_PORT));
    //$options[CURLOPT_PORT] = parse_url($this->endpoint, PHP_URL_PORT);

    // Reset the cURL handle and set the full URL, POST fields and authentication headers, if present
    $this->resetCurl($acceptContent,$options,$extraHeaders,$authentication);

    myHome()->log->info($requestMethod . ' - ' . $url . ', params: ' . json_encode($params, JSON_PRETTY_PRINT)); // . ', auth: ' . $authentication['authorization']);
    //myHome()->log->info('options: ' . json_encode($options, JSON_PRETTY_PRINT));
    //myHome()->log->info('headers: ' . json_encode(CURLOPT_HEADERFUNCTION, JSON_PRETTY_PRINT));
    //myHome()->log->info('headers: ' . json_encode(apache_request_headers(), JSON_PRETTY_PRINT));

    $startTime = microtime(true);
    // Using cURL
    //myHome()->log->info('curl: ' . @json_encode(curl_getinfo($this->curl), JSON_PRETTY_PRINT));
    $fullResponse = $this->curl_exec_follow($this->curl); //curl_exec($this->curl);
    // Using WP func
    /*$fullResponse = wp_remote_request($url, array(
        'method' => $requestMethod,
        'headers' => $authentication
      )
    );
    myHome()->log->info('requestOptions: ' . json_encode($reqOptions, JSON_PRETTY_PRINT));*/
    myHome()->log->info('Request took: ' . round(microtime(true) - $startTime, 3) . 'ms');

    $headerSize=curl_getinfo($this->curl,CURLINFO_HEADER_SIZE);
    $header=substr($fullResponse,0,$headerSize);
    $response=substr($fullResponse,$headerSize);


    $error=$this->checkResponse();  //myHome()->log->info($error);
    if($error!==null) {
      $this->setLastError($error,self::$ERROR_TYPE_OTHER);

      if(myHome()->helpers->is_json($response)) { //myHome()->log->info("ERROR RESPONSE IS JSON");
        $decodedResponse = json_decode($response);
      } else { //myHome()->log->info("ERROR RESPONSE IS NOT JSON");
        $decodedResponse = (object) [
          'status' => (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
          'error' => $error
        ];
      }
      //return $decodedResponse;
      //var_dump($decodedResponse);
      if(isset($decodedResponse->status) && isset($decodedResponse->error)) {
        status_header($decodedResponse->status, preg_replace("/[\r\n]*/","", $decodedResponse->error));
        myHome()->handleError(sprintf('HTTP error code %u: %s for %s', $decodedResponse->status, $decodedResponse->error, $url));
      }
      // Below broke redirects (try login when server down)
      //myHome()->abort($decodedResponse->status, preg_replace("/[\r\n]*/","", $decodedResponse->error));
    }

    if($parse)
      $parsedResponse=$this->parse($response);
    else
      $parsedResponse=$response;

    // Use with caution as these can max logs out
    if(myHome()->options->isLogResponses()) {
      //myHome()->log->info('FullResponse: ' . $fullResponse);
      //myHome()->log->info('ResponseHeader: ' . $header);
      myHome()->log->info('Response: ' . json_encode($parsedResponse, JSON_PRETTY_PRINT));
    }

    // The parser returns null when a malformed response is received
    if($parsedResponse===null){
      $this->setLastError(sprintf(__('Wrong response format (%s %s)','myHome'),$requestMethod,$command), self::$ERROR_TYPE_OTHER);
      return null;
    } else if($parsedResponse==='') { // The parser returns an empty string when an empty (but valid) response is received
      if($defaultArray!==null)
        return $defaultArray?[]:new stdClass;
      else
        return $response;
    }

    $this->lastUrl=$url;

    // This is intended to be used in downloads (Documents controller), along with $parse=false
    $this->lastContentType=curl_getinfo($this->curl,CURLINFO_CONTENT_TYPE);
    if(preg_match('|^Content-Disposition: (.+)$|m',$header, $contentDisposition)) // cURL does not provide any other means to get the Content-Disposition header
      $this->lastContentDisposition=trim($contentDisposition[1]);
    else
      $this->lastContentDisposition=null;

    return $parsedResponse;
  }

  /**
   * Resets the cURL handle and sets extra options, extra HTTP headers and authentication headers, if present
   *
   * @uses curl_reset()
   * @uses curl_setopt_array()
   * @uses parse_url() to extract the host from the endpoint URL (needed to append the "Host" HTTP header)
   * @param string   $acceptContent  the accepted content (used in the "Accept" HTTP header)
   * @param string[] $extraOptions   extra cURL options (eg CURLOPT_URL to set the target URL)
   * @param string[] $extraHeaders   extra HTTP headers (eg Content-Type: application/json)
   * @param string[] $authentication authentication headers
   * @throws MyHomeException if the authentication headers are wrong - ie no authorization or contractNumber values
   *                                 provided
   */
  private function resetCurl($acceptContent,$extraOptions=[],$extraHeaders=[],array $authentication=null){
    $host=parse_url($this->endpoint,PHP_URL_HOST);
    //var_dump('PHP_URL_HOST: ' . parse_url($this->endpoint,PHP_URL_HOST));
    //var_dump($host);

    $headers=['Accept: '.$acceptContent,
      'Host: '.$host];

    // Append the extra headers provided
    $headers=array_merge($headers,$extraHeaders); // Because the arrays aren't associative, we should use array_merge()

    if($authentication!==null){
      if(empty($authentication['authorization']))
        throw new MyHomeException('Wrong authentication data');

      $headers[]='Authorization: '.$authentication['authorization'];

      if(!empty($authentication['contractNumber']))
        $headers[]='ContractNumber: '.$authentication['contractNumber'];
    }
    myHome()->log->info('headers: ' . json_encode($headers));
    //myHome()->log->info('port: ' . json_encode(parse_url($this->endpoint,PHP_URL_PORT)));

    $options=
      [
        CURLOPT_CONNECTTIMEOUT=>7,
        CURLOPT_FOLLOWLOCATION=>false,//true, // Follow redirections (when a "Location" HTTP header is present in the response)
        CURLOPT_MAXREDIRS=>5, // Follow no more than five redirections
        CURLOPT_RETURNTRANSFER=>true, // Return the transfer with curl_exec()
        CURLOPT_ENCODING=>'gzip,deflate', // Expect gzip and deflate encodings
        CURLOPT_HEADER=>true, // Needed to retrieve the last Content-Disposition header received
        CURLOPT_HTTPHEADER=>$headers,
        //CURLOPT_PORT=> parse_url($this->endpoint,PHP_URL_PORT) || 80
    ]; // Set the remaining HTTP headers

    // Add the extra options provided
    $options += $extraOptions;
    //$options=array_merge($options, $extraOptions);

    // Reset the cURL handle and set the cURL options
    if(function_exists('curl_reset')) { // PHP >=5.5.0
      //myHome()->log->info('curl_reset >= 5.5: ' . @json_encode($this->curl));
      curl_reset($this->curl);
    } else {
      //myHome()->log->info('curl_reset < 5.5: ' . @json_encode($this->curl));
      if(isset($this->curl))
        curl_close($this->curl);
      $this->curl=curl_init();
    }

    //myHome()->log->info('curl: ' . @json_encode($this->curl, JSON_PRETTY_PRINT));
    //myHome()->log->info('curl options: ' . @json_encode($options, JSON_PRETTY_PRINT));
    curl_setopt_array($this->curl,$options);    //var_dump($this->curl);    //throwrandomerror();
  }

  /**
   * Allow CURLOPT_FOLLOWLOCATION=true with an php open_basedir
   *
   * @since 1.5.2
   */
  private function curl_exec_follow($ch, &$maxredirect = null) { // myHome()->log->info('curl_exec_follow: ' . @json_encode($ch, JSON_PRETTY_PRINT));
    // we emulate a browser here since some websites detect
    // us as a bot and don't let us do our job
    $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
                  " Gecko/20041107 Firefox/1.0";
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

    $mr = $maxredirect === null ? 5 : intval($maxredirect);

	$open_basedir = ini_get('open_basedir');
  //myHome()->log->info('curl baseDir: ' . json_encode($open_basedir, JSON_PRETTY_PRINT));
  //myHome()->log->info('curl baseDir isEmpty: ' . json_encode(empty($open_basedir), JSON_PRETTY_PRINT));
  //myHome()->log->info('safe_mode: ' . json_encode(filter_var(ini_get('safe_mode'), FILTER_VALIDATE_BOOLEAN) === false, JSON_PRETTY_PRINT));
  //myHome()->log->info('isSafeCurl: ' . json_encode(empty($open_basedir)	&& filter_var(ini_get('safe_mode'), FILTER_VALIDATE_BOOLEAN) === false, JSON_PRETTY_PRINT));
	if (empty($open_basedir)
	&& filter_var(ini_get('safe_mode'), FILTER_VALIDATE_BOOLEAN) === false
	) { // myHome()->log->info('isNOTSafeMode');
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
      curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    } else { // myHome()->log->info('isSafeMode');
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

      if ($mr > 0)
      {
        $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $newurl = $original_url;
      
        //myHome()->log->info('ch: ' . @json_encode($ch, JSON_PRETTY_PRINT));
        $rch = curl_copy_handle($ch);
        //myHome()->log->info('rch: ' . @json_encode($rch, JSON_PRETTY_PRINT));
      
        curl_setopt($rch, CURLOPT_HEADER, true);
        curl_setopt($rch, CURLOPT_NOBODY, true);
        curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
        do
        { // myHome()->log->info('doWhile: ' . $mr . " : " . $newurl);
          curl_setopt($rch, CURLOPT_URL, $newurl);
          //myHome()->log->info('curl: ' . json_encode(@curl_getinfo($rch), JSON_PRETTY_PRINT));
          $header = curl_exec($rch);
          //myHome()->log->info('header: ' . @json_encode($header, JSON_PRETTY_PRINT));
          if (curl_errno($rch)) {
            $code = 0;
          } else {
            $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
            if ($code == 301 || $code == 302) {
              preg_match('/Location:(.*?)\n/i', $header, $matches);
              $newurl = trim(array_pop($matches));
            
              // if no scheme is present then the new url is a
              // relative path and thus needs some extra care
              if(!preg_match("/^https?:/i", $newurl)){
                $newurl = $original_url . $newurl;
              }   
            } else {
              $code = 0;
            }
          }
        } while ($code && --$mr); // myHome()->log->info('doneWhile.');
      
        curl_close($rch);
      
        if (!$mr)
        {
          if ($maxredirect === null)
            trigger_error('Too many redirects.', E_USER_WARNING);
          else
            $maxredirect = 0;
        
          return false;
        }
        curl_setopt($ch, CURLOPT_URL, $newurl);
      }
    }
    // myHome()->log->info('curl_exec_follow end.');
    return curl_exec($ch);
  }

  /**
   * Sets the last error message and type
   *
   * @param string|null $message error message, or null to reset the property
   * @param int|null    $type    error type, or null to reset the property
   */
  private function setLastError($message,$type){
    $this->lastErrorMessage=$message;
    $this->lastErrorType=$type;
  }

  /**
   * Generates the URL for a given command
   *
   * @since 1.2 changed $command from string to string|string[] and removed $resourceId
   * @uses  MyHomeApi::$endpoint
   * @uses  MyHomeApi::$COMMAND_PREFIX
   * @param string|string[] $command API command (eg "jobs"); it can contain resource IDs and subcommands (eg
   *                                 array("maintenancejobs",123,"maintenanceissues",456))
   * @return string the command URL
   */
  public function url($command = null) {
    //myHome()->log->info('isArray: ' . (is_array($command)?'true':'false') . ', command: ' . $command); 

    //$endpoint = preg_replace('/:[0-9]+/', '', trailingslashit($this->endpoint)); // Removes port #
    $endpoint = trailingslashit($this->endpoint);

    $commands = is_array($command) ? implode('/',$command) : $command;
    $commands = str_ireplace(self::$COMMAND_PREFIX, '', $commands);
    
    //myHome()->log->info('remove prefix: ' . self::$COMMAND_PREFIX); 
    //myHome()->log->info('commands: ' . $commands); 
    //myHome()->log->info(sprintf('%s%s/%s', $endpoint, self::$COMMAND_PREFIX, $commands)); // 'endpoint: ' . $endpoint);

    return sprintf('%s%s%s', $endpoint, self::$COMMAND_PREFIX, $commands);
  }

  /**
   * API endpoint base URL (eg "http://endpoint.com.au")
   *
   * @var string
   */
  private $endpoint;

  /**
   * The cURL handle used to make the API calls
   *
   * @var resource|null
   */
  private $curl=null;

  /**
   * If the last call triggered an error, its message is stored in this variable; otherwise, it is set to null
   *
   * @var string|null
   */
  private $lastErrorMessage='';

  /**
   * If the last call triggered an error, its type (eg $ERROR_TYPE_OTHER) is stored in this property; otherwise, it is
   * set to null
   *
   * @var int|null
   */
  private $lastErrorType=null;

  /**
   * The URL of the last successful call, if any; otherwise, it is set to null
   *
   * @var string|null
   */
  private $lastUrl=null;

  /**
   * The response Content-Type of the last successful call, if any; otherwise, it is set to null
   *
   * @var string|null
   */
  private $lastContentType=null;

  /**
   * The response Content-Disposition of the last successful call, if any; otherwise, it is set to null
   *
   * @var string|null
   */
  private $lastContentDisposition=null;
}

