<?php

/**
 * The ShortcodeTasksController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTasksController'))
  return;

/**
 * The ShortcodeTasksController class
 *
 * Controller for the Tasks shortcode
 */
class ShortcodeTasksController extends MyHomeShortcodesBaseController{
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
    $atts = shortcode_atts([
      'mode' => 'list', 
      'filter' => null,
      'hidefields' => ''
    ], $atts);

    $attMode = $atts['mode'];
    $attHideFields = explode(',',$atts['hidefields']);
    $attHideFields = array_map('trim',$attHideFields);
    $attHideFields = array_filter($attHideFields,'strlen');
    //myHome()->log->info("attHideFields: " . in_array('day', $attHideFields));

    if(!$this->verifyMode($attMode)){
      myHome()->handleError('Wrong Mode attribute: '.$attMode);
      $attMode = 'list';
    }

    $events = $this->eventsList($atts);
    if($events===null)
      return;
    else {
      // Customise event dates
      $events = array_map(function($event) use ($attHideFields) {
        $dateFormat = '';
        if(!in_array('day', $attHideFields) && strtolower($event['completed']) == 'completed') $dateFormat .= 'd ';
        $dateFormat .= 'M Y';
        //myHome()->log->info("event: " . json_encode($dateFormat) . json_encode($event));
        $event['date'] = date_format(date_create($event['dateactual']), $dateFormat);
        return $event;
      }, $events);
    }

    $this->loadView('shortcodeTasks','MyHomeShortcodes',compact('attMode','attHideFields','events'));
  }

  /**
   * Returns the sorted events list after querying the API with the jobsteps command
   *
   * @uses MyHomeApi::get()
   * @return mixed[]|null the events list (null if not available), sorted by sequence number in ascending order - each
   *                      item is composed of:
   * <ul>
   * <li>Array key: event sequence number (sequence field)</li>
   * <li>name: event name (name field)</li>
   * <li>date: event date (datedescription field)</li>
   * <li>completed: whether the event is completed (generated from the status field)</li>
   * </ul>
   */
  private function eventsList($atts){
    $authentication=myHome()->session->getAuthentication();
    $jobSteps=myHome()->api->get('jobsteps',$authentication,true);
    //myHome()->log->info("jobSteps: " . json_encode($jobSteps));

    if($jobSteps===null)
      return null;

    // Detect Current Phase
    if($atts['filter'] == 'auto') {
      foreach($jobSteps as $task) {
        if(!isset($mostRecentlyCompleted) || ($task->status==='Completed' && $task->sequence > $mostRecentlyCompleted->sequence))
          $mostRecentlyCompleted = $task;
      }
      $atts['filter'] = strtolower($mostRecentlyCompleted->phasecode);
      //echo('Current Phase: ' . $mostRecentlyCompleted->phasecode);
    }

    $events=[];
    foreach($jobSteps as $task){
      //print_r($task->phasecode);
      if(empty($task->datedescription))
        continue;
      if(empty($task->status))
        continue;
      if(empty($task->name))
        continue;
      if(empty($task->sequence))
        continue;
      if($atts['filter'] != null && strpos(strtolower($task->phasecode), $atts['filter']) === false) {
        //echo('skip');
        continue;
      }
      //echo $task->sequence . ", ";
      //myHome()->log->info("sequence: " . $task->sequence . ", name: " . $task->name . ", date: " . $task->dateactual);

      $sequence=$task->sequence;
      if(!isset($events[$sequence]))
        $events[$sequence]=[];

      $events[$sequence][]=[
        'name'=>$task->name,
        'date'=>$task->datedescription,
        'dateactual'=>$task->dateactual,
        'completed'=>$task->status
      ];//==='Completed'];

       //print_r($events[$sequence]);
    }

    // Sort by sequence number (array key) // Tasks should sort by date - is already correct from server
    //ksort($events,SORT_NUMERIC);
    //print_r($events);

    // Flatten the array - $eventsList is made up of arrays of items, one per sequence number (eg if three events share the same number, they are grouped in one array)
    $eventsList=[];
    foreach($events as $eventsSequence)
      $eventsList=array_merge($eventsList,$eventsSequence);

    // myHome()->log->info("eventsList: " . json_encode($eventsList));
    return $eventsList;
  }

  /**
   * Verifies the value of the mode shortcode attribute provided
   *
   * @param string $mode the mode attribute value to check
   * @return bool whether the attribute is valid or not (it must be "list" or "grid")
   */
  private function verifyMode($mode){
    return in_array($mode,['list','grid']);
  }
}
