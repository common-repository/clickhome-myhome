<?php

/**
 * The ShortcodeStoriesController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeStoriesController'))
  return;

/**
 * The ShortcodeStoriesController class
 *
 * Controller for the Stories shortcode
 */
class ShortcodeStoriesController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts=shortcode_atts(['mode'=>'ABC'],$atts);

    $attMode=$atts['mode'];

    if(!$this->verifyMode($attMode)){
      myHome()->handleError('Wrong Mode attribute: '.$attMode);
      $attMode='ABC';
    }

    $stories=$this->storiesList();
    if($stories===null)
      return;

    $this->loadView('shortcodeStories','MyHomeShortcodes',compact('attMode','stories'));
  }

  /**
   * Returns the stories list after querying the API with the stories command
   *
   * @uses MyHomeApi::get()
   * @return mixed[]|null the stories list (null if not available) - each item is composed of:
   * <ul>
   * <li>Array key: story section (generated from the sectioncode field) - used by the view to sort the stories
   * according to the mode attribute</li>
   * <li>Array value: an array of stories for that section - each item consisting of:</li>
   * <ul>
   * <li>name: story name (name field)</li>
   * <li>story: story text (story field)</li>
   * </ul>
   * </ul>
   */
  private function storiesList(){
    $authentication=myHome()->session->getAuthentication();
    $stories=myHome()->api->get('stories',$authentication,true);

    if($stories===null)
      return null;

    // Prepare the stories list, indexed by the A, B or C letter depending on the sectioncode field of each story ("AFTER", "BEFORE" or "CURRENT")
    $storiesList=['A'=>[],
      'B'=>[],
      'C'=>[]];

    foreach($stories as $story){
      if(empty($story->sectioncode))
        continue;

      switch($story->sectioncode){
        case 'AFTER':
          $key='A';
          break;
        case 'CURRENT':
          $key='C';
          break;
        case 'BEFORE':
          $key='B';
          break;
        default:
          $key=null;
      }

      // Skip this story if the section code is not "AFTER", "CURRENT" or "BEFORE"
      if($key===null)
        continue;

      $storiesList[$key][]=['name'=>$story->name,
        'story'=>$story->story];
    }

    return $storiesList;
  }

  /**
   * Verifies the value of the mode shortcode attribute provided
   *
   * @param string $mode the mode attribute value to check
   * @return bool whether the attribute is valid or not (it must be any combination of the letters "A", "B" and "C")
   */
  private function verifyMode($mode){
    return in_array($mode,['ABC','ACB','BAC','BCA','CAB','CBA']);
  }
}
