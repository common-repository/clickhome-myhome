<?php

/**
 * The ShortcodeProgressController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeProgressController'))
  return;

/**
 * The ShortcodeProgressController class
 *
 * Controller for the Progress shortcode
 */
class ShortcodeProgressController extends MyHomeShortcodesBaseController{
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
  public function doShortcode(array $atts=[]){ // myHome()->log->info('doShortcode(progress)');
    $atts=shortcode_atts(['mode'=>'horizontal', 'filter'=>null],$atts);

    $attMode=$atts['mode'];

    if(!$this->verifyMode($attMode)){
      myHome()->handleError('Wrong Mode attribute: '.$attMode);
      $attMode='horizontal';
    }

    $progress=$this->progressList($atts);
    if($progress===null)
      return;

    $this->loadView('shortcodeProgress','MyHomeShortcodes',compact('attMode','progress'));
    //echo '---------------';
  }

  /**
   * Returns the sorted events list after querying the API with the jobprogress command
   *
   * @uses MyHomeApi::get()
   * @return mixed[]|null the events list (null if not available), sorted by sequence number in ascending order - each
   *                      item is composed of:
   * <ul>
   * <li>Array key: event sequence number (sequence field)</li>
   * <li>name: event name (name field)</li>
   * <li>completed: whether the event is completed (generated from the status field)</li>
   * <li>phaseCode: phase code, upon which is based the icon displayed (phasecode field)</li>
   * </ul>
   */
  private function progressList($atts){
    $authentication=myHome()->session->getAuthentication();
    $jobProgress=myHome()->api->get('jobprogress',$authentication,true);
	  //var_dump($jobProgress);
    //var_dump($atts['filter']);

    if($jobProgress===null)
      return null;
    
    // Detect Current Phase
    if($atts['filter'] == 'auto') {
      //$mostRecentlyCompleted = ['sequence'=> 0];
      foreach($jobProgress as $task) {
        if(!isset($mostRecentlyCompleted) || ($task->status==='Completed' && $task->sequence > $mostRecentlyCompleted->sequence)) {
          $mostRecentlyCompleted = $task;
          //break;
        }
      }
      $atts['filter'] = strtolower($mostRecentlyCompleted->phasecode);
      myHome()->log->info('Current Phase: ' . $mostRecentlyCompleted->phasecode);
    }

    $progress=[];
    foreach($jobProgress as $task) {
      //echo('<br/>');
      //var_dump(strtolower($task->phasecode));
      //var_dump(strpos(strtolower($task->phasecode), $atts['filter']));
      // myHome()->log->info("task: " . json_encode($task));

      if(empty($task->name))
        continue;
      if(empty($task->sequence))
        continue;
      if(empty($task->status))
        continue;
      if(empty($task->phasecode))
        continue;
      if($atts['filter'] != null && strpos(strtolower($task->phasecode), $atts['filter']) === false) {
        // myHome()->log->info('skipped');
        continue;
      }

      $sequence=$task->sequence;
      if(!isset($progress[$sequence]))
        $progress[$sequence]=[];

      $progress[$sequence][]=[
        'name'=>$task->name,
        'completed'=>$task->status==='Completed',
		    'status'=>$task->status,
		    'sequence'=>$task->sequence,
        'phaseCode'=>$task->phasecode
      ];
    }

    //echo "Current Phase: " ;

    // Sort by sequence number (array key)
    ksort($progress,SORT_NUMERIC);
    //print_r($progress);

    // Flatten the array - $progressList is made up of arrays of items, one per sequence number (eg if three events share the same number, they are grouped in one array)
    $progressList=[];
    foreach($progress as $progressSequence)
      $progressList=array_merge($progressList,$progressSequence);

    // Filter the completed flag for every task beyond the first marked as pending
    $completed=true;
    $progressList=array_map(function ($task) use (&$completed){
      if($completed)
        $completed=$task['completed'];
      $task['completed']=$completed;

      return $task;
    },$progressList);

    return $progressList;
  }

  /**
   * Verifies the value of the mode shortcode attribute provided
   *
   * @param string $mode the mode attribute value to check
   * @return bool whether the attribute is valid or not (it must be "horizontal" or "vertical")
   */
  private function verifyMode($mode){
    return in_array($mode,['horizontal','vertical']);
  }
}
