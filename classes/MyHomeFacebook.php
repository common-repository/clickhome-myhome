<?php

/**
 * The MyHomeFacebook class
 *
 * @package    MyHome
 * @subpackage Classes
 * @since      1.4
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeFacebook'))
  return;

use Facebook\Facebook;
use Facebook\GraphNodes\GraphNode;
use Facebook\GraphNodes\GraphUser;

/**
 * The MyHomeFacebook class
 *
 * Interacts with the Facebook API using the Facebook PHP SDK
 *
 * @since 1.4
 * @uses  MyHomeOptions
 */
class MyHomeFacebook{
  /**
   * Name of the session value which stores the Facebook access token
   */
  private static $NAME_ACCESS_TOKEN='facebookToken';

  /**
   * Returns true if the Facebook App is set up or false otherwise
   *
   * This should be called before any other method in this class
   *
   * @return bool whether the Facebook App is set up
   */
  public function appSetUp() {
    $options=myHome()->options;

    $appId=$options->getFacebookAppId();
    $appSecret=$options->getFacebookAppSecret();

    return $appId&&$appSecret;
  }

  /**
   * Checks if there is an active Facebook session
   *
   * @return GraphUser|null if a Facebook session is detected, the user details (including email
   * address and picture URL)
   */
  public function detectLogin() {
    $fb = $this->fb();
    $token = $this->userToken();

    try {
      //echo(' perms:<pre>' . json_encode((array) $fb->get('/me/permissions', $token), JSON_PRETTY_PRINT) . '</pre>');
      // Returns a `Facebook\FacebookResponse` object
      $response = $fb->get('/me?fields=id,name,email,picture', $token);
      //echo(' detectLoginRes:<pre>' . json_encode((array) $response, JSON_PRETTY_PRINT) . '</pre>');
      //echo(' detectLoginRes:' . json_encode($response, JSON_PRETTY_PRINT) . '<br/>');
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    } catch(Exception $e) {
      //echo 'facebook exception: ' . $e->getMessage();
      return null;
    } 
    
    $user = $response->getGraphUser();
    //echo(' detectLoginRes:<pre>' . var_export($user, true) . '</pre>' . count($user));
    return count($user) == 0 ? null : $user; // count $user to ensure it is not empty
  }

  /**
   * Gets the Graph Node for a specific photo by is ID
   *
   * @see MyHomeFacebook::photoPageUrl()
   * @see MyHomeFacebook::photoUrl()
   * @param int $photoId
   * @return GraphNode the Graph Node with information about the photo
   * @throws Exception if something goes wrong
   */
  public function photoDetails($photoId) {
    $fb=$this->fb();
    $token=$this->userToken();

    // Link and images are needed by photoPageUrl() and photoUrl()
    $endpoint=sprintf('/%s?fields=link,images',$photoId);

    // This may throw an exception
    $response=$fb->get($endpoint,$token);

    return $response->getGraphNode();
  }

  /**
   * Gets the URL for the Facebook page for a given photo (eg https://www.facebook.com/photo.php?fbid=...)
   *
   * @param GraphNode $graphNode the Graph Node with information about the photo
   * @return string the URL for that photo
   */
  public function photoPageUrl(GraphNode $graphNode){
    if(!empty($graphNode['link']))
      return $graphNode['link'];
    else
      return '';
  }

  /**
   * Gets the URL for a given photo on Facebook (eg https://scontent.xx.fbcdn.net/.../photo_o.jpeg)
   *
   * @param GraphNode $graphNode the Graph Node with information about the photo
   * @return string the URL for that photo
   */
  public function photoUrl(GraphNode $graphNode){
    $url='';
    $maxWidth=null;

    // Look for the largest image available
    foreach($graphNode['images'] as $image)
      if($maxWidth===null||$image['width']>$maxWidth){
        $url=$image['source'];
        $maxWidth=$image['width'];
      }

    return $url;
  }

  /**
   * Checks if the App is correctly configured
   *
   * @return GraphNode|null if information is available from the App, the App details (including name, contact email,
   * supported platforms and URL)
   */
  public function testApp(){
    $fb = $this->fb();
    $token = $this->appToken();

    $endpoint = sprintf('/%s?fields=id,name,contact_email,supported_platforms,website_url', myHome()->options->getFacebookAppId());

    try {
      $response = $fb->get($endpoint, $token);
      echo '<pre>' . json_encode($response, JSON_PRETTY_PRINT) . '</pre>';
    }
    catch(Exception $e){
      return null;
    }

    return $response->getGraphNode();
  }

  /**
   * Uploads an image to the default album for the Facebook app
   *
   * @see MyHomeFacebook::photoDetails()
   * @param string $image   the image contents
   * @param string $message the message to attach to the image
   * @return int the ID of the new photo
   * @throws Exception if something goes wrong
   */
  public function uploadImage($image,$message){
    $fb=$this->fb();
    $token=$this->userToken();

    $file=new MyHomeFacebookImageMemory('/fakepath.jpeg',$image);

    $endpoint='/me/photos';
    $data=['message'=>$message,'source'=>$file,'no_story'=>true];

    // This may throw an exception
    $response=$fb->post($endpoint,$data,$token);

    return $response->getGraphNode()['id'];
  }

  /**
   * Returns the Facebook access token (for use with App related functions)
   *
   * @return string the Facebook access token
   * @throws MyHomeException if the Facebook App is not properly configured
   */
  private function appToken(){
    $options=myHome()->options;

    $appId=$options->getFacebookAppId();
    $appSecret=$options->getFacebookAppSecret();

    if(!$appId||!$appSecret)
      throw new MyHomeException('Facebook App not set up');

    return sprintf('%s|%s',$appId,$appSecret);
  }

  /**
   * Returns the Facebook object - lazy loaded
   *
   * @return Facebook the Facebook object
   * @throws MyHomeException if the Facebook App is not properly configured
   */
  private function fb(){
    if(!isset($this->fb)){
      require_once MH_PATH_VENDOR.'/facebook-php-sdk/src/Facebook/autoload.php';

      $options = myHome()->options;
      $appId = $options->getFacebookAppId();
      $appSecret = $options->getFacebookAppSecret();

      if(!$appId || !$appSecret)
        throw new MyHomeException('Facebook App not set up');

      $this->fb=new Facebook([
        'app_id'=>$appId,
        'app_secret'=>$appSecret,
        'default_graph_version'=>'v2.10'
      ]);
    }

    return $this->fb;
  }

  /**
   * Resets the user token
   */
  private function forgetUserToken(){
    myHome()->storage->delete(static::$NAME_ACCESS_TOKEN);

    $this->userToken=null;
  }

  /**
   * Returns the Facebook access token (for use with user related functions) - lazy loaded
   *
   * @return string the Facebook access token
   */
  public function userToken() {
    if($this->userToken===null){
      $this->userToken=myHome()->storage->get(static::$NAME_ACCESS_TOKEN);

      if($this->userToken===null){
        $helper=$this->fb()->getJavaScriptHelper();

        try {
          $accessToken = $helper->getAccessToken();
        }
        catch(Exception $e){
          return null;
        }

        if(!isset($accessToken))
          return null;

        $this->userToken=(string)$accessToken;

        myHome()->storage->put(static::$NAME_ACCESS_TOKEN,$this->userToken);
      }
    }

    //echo('userToken: ' . $this->userToken);
    return $this->userToken;
  }

  /**
   * The Facebook object
   *
   * @var Facebook|null
   */
  private $fb;

  /**
   * The Facebook access token
   *
   * @var string|null
   */
  private $userToken;
}
