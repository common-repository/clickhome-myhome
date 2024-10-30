<?php

/**
 * The ShortcodeFaqController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeFaqController'))
  return;

/**
 * The ShortcodeFaqController class
 *
 * Controller for the FAQ shortcode
 */
class ShortcodeFaqController extends MyHomeShortcodesBaseController{
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
    $atts=shortcode_atts(['mode'=>'all'],$atts);

    $attMode=$atts['mode'];

    if(!$this->verifyMode($attMode)){
      myHome()->handleError('Wrong Mode attribute: '.$attMode);
      $attMode='all';
    }

    $questions=$this->questionsList();
    if($questions===null)
      return;

    $this->loadView('shortcodeFaq','MyHomeShortcodes',compact('attMode','questions'));
  }

  /**
   * Returns the questions list after querying the API with the questions command
   *
   * @uses MyHomeApi::get()
   * @return mixed[]|null the questions list (null if not available) - each item is composed of:
   * <ul>
   * <li>current: whether the question is current or not (generated from the current field)</li>
   * <li>question: question (question field)</li>
   * <li>answer: the answer to the question (answer field)</li>
   * </ul>
   */
  private function questionsList(){
    $authentication=myHome()->session->getAuthentication();
    $questions=myHome()->api->get('questions',$authentication,true);

    if($questions===null)
      return null;

    $questionsList=[];

    foreach($questions as $question){
      if(!isset($question->current))
        continue;

      $questionsList[]=['current'=>(bool)$question->current,
        'question'=>$question->question,
        'answer'=>$question->answer];
    }

    return $questionsList;
  }

  /**
   * Verifies the value of the mode shortcode attribute provided
   *
   * @param string $mode the mode attribute value to check
   * @return bool whether the attribute is valid or not (it must be "all" or "current")
   */
  private function verifyMode($mode){
    return in_array($mode,['all','current']);
  }
}
