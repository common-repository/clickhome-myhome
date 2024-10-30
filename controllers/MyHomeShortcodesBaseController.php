<?php

/**
 * The MyHomeShortcodesBaseController class
 *
 * @package    MyHome
 * @subpackage Controllers
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeShortcodesBaseController'))
  return;


class MyHomeUser {
  public function __construct ( $user=[] ) {
    $user = (object) $user;
    $this->profilePhoto = $user->profilePhoto;
  }

  public $profilePhoto;
}

class MyHomeProfilePhoto {
  public function __construct ( $profilePhoto=[] ) {
    $profilePhoto = (object) $profilePhoto;
    $this->small = isset($profilePhoto->small) ? $profilePhoto->small : null;
    $this->medium = isset($profilePhoto->medium) ? $profilePhoto->medium : null;
    $this->large = isset($profilePhoto->large) ? $profilePhoto->large : null;
  }

  public $small;
  public $medium;
  public $large;
}

/**
 * The MyHomeShortcodesBaseController class
 *
 * Abstract class for shortcode view controllers
 */
abstract class MyHomeShortcodesBaseController extends MyHomeBaseController{
  /**
   * Returns the download URL (with Content-Type: inline) for a given document ID
   *
   * This method is called from several shortcodes (House Details, Photos, and Maintenance Issues) to insert images
   * downloaded from the API
   *
   * @see  ShortcodeDocumentsController::doGet()
   * @uses MyHomeShortcodesBaseController::$formAttributes to generate the appropriate GET URL for the document action
   * @param int  $documentId the document ID
   * @param bool $thumb      whether the document should be retrieved using the thumbs API call
   * @param bool $cache      whether the document cache should be used
   * @return string the download URL
   */
  protected function photoDownloadUrl($data, $thumb=false, $cache=false, $authType='system', $inline=true) { // myHome()->info->log('photoDownloadUrl: ' . json_decode($data));
    if(myHome()->helpers->is_base64($data)) {
      return 'data:image/jpeg;base64,' . $data;
    } else if(strpos(strtolower($data), "clickhome.myhome/v2") > -1) {
      $fullUrl = trailingslashit(myHome()->options->getEndpoint()) . $data;

      if($authType == 'client') {
        $fullUrl = add_query_arg(array(
            'auth' => myHome()->session->getAuthentication()['authorization'],
            'contractNo' => myHome()->session->getAuthentication()['contractNumber'],
            'inline' => $inline ? 'true' : 'false',
            'thumb' => $thumb,
            'cache' => 'true'
        ), $fullUrl);
        myHome()->log->info('fullUrl: ' . $fullUrl);
      }

      //$data = str_ireplace("clickhome.myhome/v2", "", $data);
      //$data = myHome()->api->download($data, myHome()->session->getAuthentication());
      //return 'data:image/jpeg;base64,' . $data;
      return $fullUrl;
    } else if(is_numeric($data)) { // Deprecating
      //if($this->photoFormAttributes===null) 
        //$this->photoFormAttributes=myHome()->adminPostHandler->formAttributes('document', 'GET', null, null, $authType) ;//$authType == 'client' ? 'clientDocument' : 'systemDocument','GET');
        $this->photoFormAttributes=myHome()->adminPostHandler->formAttributes($authType == 'client' ? 'clientDocument' : 'systemDocument', 'GET');
      //$this->photoFormAttributes['params']['myHomeAuth'] = $authType;
      $this->photoFormAttributes['params']['myHomeDocumentId'] = $data;
      $this->photoFormAttributes['params']['myHomeInline'] = (int)true; // add_query_arg() ignores parameters with a boolean false value
      $this->photoFormAttributes['params']['myHomeThumb'] = (int)$thumb;
      $this->photoFormAttributes['params']['myHomeCache'] = (int)$cache;
      
      //myHome()->log->info(serialize($this->photoFormAttributes['params']));
      //var_dump(add_query_arg($this->photoFormAttributes['params'],$this->photoFormAttributes['url']));
      return add_query_arg($this->photoFormAttributes['params'],$this->photoFormAttributes['url']);
    } else {
      return MH_URL_IMAGES . '/noPhoto.gif';
    }
  }

  protected function profilePic($user, $size='m', $isRound=true) {
    $user = new MyHomeUser($user);
    $classes = ['profile-pic', 'size-' . $size];
    if($isRound) $classes[] = 'round';

    $div = '<div class="' . implode(' ', $classes) . '">';

    // Try find avatar
    if(isset($user->profilePhoto)) {
      $user->profilePhoto = new MyHomeProfilePhoto($user->profilePhoto);
      //echo json_encode($user, JSON_PRETTY_PRINT);

      switch($size) {
        case 'l':
          $src = $this->first_that_exists($user->profilePhoto->large, $user->profilePhoto->medium, $user->profilePhoto->small);
          break;
        case 'm':
          $src = $this->first_that_exists($user->profilePhoto->medium, $user->profilePhoto->large, $user->profilePhoto->small);
          break;
        case 's':
          $src = $this->first_that_exists($user->profilePhoto->small, $user->profilePhoto->medium, $user->profilePhoto->large);
          break;
      }
    }
    if(isset($src)) $div .= '<img src="' . $src . '" />';

    // else Show Initials
    else {
        if(isset($user->firstName) && isset($user->surname)) {
        $div .= '<div><span>' . $user->firstName[0] . ' ' . $user->surname[0] . '</span></div>';
      } else if(isset($user->name)) {
        $names = explode(' ', $user->name);
        $div .= '<div><span>' . $names[0][0] . ' ' . $names[1][0] . '</span></div>';
      } else {
        //$div .= '<img src="' . MH_URL_IMAGES . '/noPhoto.gif" />';
        return;
      }
    }
    $div .= '</div>';

    return $div;
  }

  protected function first_that_exists() {
    //echo(json_encode(func_get_args()));
    foreach(func_get_args() as $value) { //echo($value . '       ');
      if(isset($value)) return $value;
    }
  }

  /**
   * Returns a GET parameter
   *
   * @since 1.5
   * @param string $param the parameter name
   * @return string|null the parameter value or null if not found
   */
  protected function getParam($param){
    if(isset($_GET[$param]))
      return $_GET[$param];
    else
      return null;

    // Not using query_vars() - see MyHome::setupHooks()
    /*
    global $wp_query;

    // $param must be registered by MyHome::onQueryVars()
    if(isset($wp_query->query_vars[$param]))
      return $wp_query->query_vars[$param];
    else
      return null;
    */
  }

  /**
   * Settings for the document action - used by photoDownloadUrl()
   *
   * @var mixed[]
   */
  private $photoFormAttributes=null;
}
