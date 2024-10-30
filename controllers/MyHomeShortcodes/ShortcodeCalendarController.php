<?php

/**
 * The ShortcodeCalendarController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeCalendarController'))
  return;

/**
 * The ShortcodeCalendarController class
 *
 * Controller for the Calendar shortcode
 */
class ShortcodeCalendarController extends MyHomeShortcodesBaseController{
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
    myHome()->log->info('calendarController doPostXhr');
    list($month,$year)=$this->extractParams(['myHomeMonth',
      'myHomeYear'],$params);

    if($month===null||$year===null||!($month>=1&&$month<=12)||!($year>=2000&&$year<=2050)){
      $month=(int)date('n');
      $year=(int)date('Y');
    }

    $date=sprintf('%s %u',self::$MONTH_NAMES[$month],$year);

    $events=$this->eventsFor($month,$year);
    if($events===null)
      myHome()->abort(403,'Job steps not available'); // Forbidden

    list($firstDayOfWeek,$numDays,$numDaysPrevious)=$this->calculateDays($month,$year);

    $response=['month'=>$month,
      'year'=>$year,
      'date'=>$date,
      'events'=>$events,
      'firstDayOfWeek'=>$firstDayOfWeek,
      'numDays'=>$numDays,
      'numDaysPrevious'=>$numDaysPrevious];

    echo json_encode($response);
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    //$atts=shortcode_atts(['list'=>'true'],$atts);
    //$attList=$atts['list'];
    $atts['list'] = isset($atts['list']) ? $atts['list'] !== 'false' : true;
    $atts['resource'] = isset($atts['resource']) ? $atts['resource'] !== 'false' : true;

    if(!$this->verifyList($atts['list'])){
      myHome()->handleError('Wrong List attribute: '.  $atts['list']);
      $attList='true';
    }

    if($atts['list']){
      $events=$this->eventsList();
      if($events===null)
        return;
    }
    else
      $events=null;

    //var_dump($events);

    $this->loadView('shortcodeCalendar','MyHomeShortcodes',compact('atts','events'));
  }

  /**
   * Calculates the starting day of the first week and the number of days of a given month, as well the number of days
   * of the previous one
   *
   * @param int $month the month (1 to 12)
   * @param int $year  the year
   * @return int[] the three values:
   *                   <ul>
   *                   <li>Starting day of the first week in ISO-8601 representation: 1 (for Monday) to 7 (for
   *                   Sunday)</li>
   *                   <li>Number of days ot the given month</li>
   *                   <li>Number of days ot the previous month</li>
   *                   </ul>
   */
  private function calculateDays($month,$year){
    $dt=new DateTime(sprintf('%04u-%02u-01',$year,$month));

    $firstDayOfWeek=(int)$dt->format('N');

    $intervalMonth=new DateInterval('P1M');
    $intervalDay=new DateInterval('P1D');

    // Decrease one day to the date to get the last day of the previous month
    $dt->sub($intervalDay);
    $numDaysPrevious=(int)$dt->format('j');

    // Restore the date, and then increase one month and decrease one day to get the last day of the given month
    $dt->add($intervalDay);
    $dt->add($intervalMonth);
    $dt->sub($intervalDay);
    $numDays=(int)$dt->format('j');

    return [$firstDayOfWeek,$numDays,$numDaysPrevious];
  }

  /**
   * Returns a list of events for a given month after querying the API with the jobsteps command
   *
   * Used by doPostXhr()
   *
   * @uses MyHomeApi::get()
   * @param int $month the month (1 to 12)
   * @param int $year  the year
   * @return mixed[]|null the events list, (null if not available) - each item is composed of:
   *                   <ul>
   *                   <li>Array key: day of the month (generated from the dateactual field)</li>
   *                   <li>Array value: an array of events - each item consisting of:</li>
   *                   <ul>
   *                   <li>name: event name (name field)</li>
   *                   <li>resourceName: resource name (resourcename field)</li>
   *                   </ul>
   *                   </ul>
   */
  private function eventsFor($month,$year){
    $authentication=myHome()->session->getAuthentication();
    $jobSteps=myHome()->api->get('jobsteps',$authentication,true);

    if($jobSteps===null)
      return null;

    $events=[];

    // Used to compare the year and month with each event date
    $yearMonth=sprintf('%04u%02u',$year,$month);

    foreach($jobSteps as $task){
      if(empty($task->dateactual))
        continue;
      if(empty($task->name))
        continue;

      $dt=new DateTime($task->dateactual);
      if($dt->format('Ym')!==$yearMonth)
        continue;

      $day=(int)$dt->format('j');
      if(!isset($events[$day]))
        $events[$day]=[];

      // The resource name may be empty
      if(!empty($task->resourcename))
        $resourceName=$task->resourcename;
      else
        $resourceName='';

      $events[$day][]=['name'=>$task->name,
        'resourceName'=>$resourceName];
    }

    return $events;
  }

  /**
   * Returns the sorted events list after querying the API with the jobsteps command
   *
   * Used by doShortcode() when the list attribute is set to "true"
   *
   * @uses MyHomeBaseController::dateString()
   * @return mixed[]|null the events list (null if not available), sorted by sequence number in ascending order - each
   *                      item is composed of:
   * <ul>
   * <li>Array key: event sequence number (sequence field)</li>
   * <li>name: event name (name field)</li>
   * <li>resourceName: resource name (resourcename field)</li>
   * <li>date: formatted event date (generated from the dateactual field)</li>
   * <li>month: date month (generated from the dateactual field) - used to perform the appropriate Ajax query when
   * clicking on an event date</li>
   * <li>year: date year (generated from the dateactual field) - same as month</li>
   * </ul>
   */
  private function eventsList(){ // Used in the shortcode when list=true
    $authentication=myHome()->session->getAuthentication();
    $jobSteps=myHome()->api->get('jobsteps',$authentication,true);

    if($jobSteps===null)
      return null;

    $events=[];

    foreach($jobSteps as $task){
      if(empty($task->dateactual))
        continue;
      if(empty($task->name))
        continue;
      if(empty($task->sequence))
        continue;

      //myHome()->log->info('event: ' . json_encode($task));
      $sequence=$task->sequence;
      //myHome()->log->info($sequence);
      if(!isset($events[$sequence]))
        $events[$sequence]=[];

      // The resource name may be empty
      if(!empty($task->resourcename))
        $resourceName=$task->resourcename;
      else
        $resourceName='';

      $dt=new DateTime($task->dateactual);
      $month=$dt->format('n');
      $year=$dt->format('Y');

      $events[$sequence][] = [
        'name'=>$task->name,
        'resourceName'=>$resourceName,
        'date'=>$this->dateString($dt,true),
        'month'=>$month,
        'year'=>$year,
        'sequence'=>$sequence
      ];
    }
    // myHome()->log->info('events: ' . json_encode($events));

    // Flatten the array - $eventsList is made up of arrays of items, one per sequence number (eg if three events share the same number, they are grouped in one array)
    $eventsList=[];
    foreach($events as $eventsSequence)
      $eventsList=array_merge($eventsList,$eventsSequence);

    // Sort by sequence number
    usort($eventsList, function($a, $b) { 
      return $a['sequence'] > $b['sequence'];
    });
    //foreach($eventsList as $event) { myHome()->log->info($event['sequence'] . ' - ' . $event['name']); }

    return $eventsList;
  }

  /**
   * Verifies the value of the list shortcode attribute provided
   *
   * @param string $list the list attribute value to check
   * @return bool whether the attribute is valid or not (it must be "false" or "true")
   */
  private function verifyList($list){
    return in_array($list,['false','true']);
  }
}
