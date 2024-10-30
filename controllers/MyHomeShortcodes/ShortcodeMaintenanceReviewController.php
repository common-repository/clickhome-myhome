<?php

/**
 * The ShortcodeMaintenanceReviewController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeMaintenanceReviewController'))
  return;

/**
 * The ShortcodeMaintenanceReviewController class
 *
 * Controller for the Maintenance Review shortcode
 *
 * @since 1.2
 */
class ShortcodeMaintenanceReviewController extends ShortcodeMaintenanceBaseController{
  /**
   * {@inheritDoc}
   */
  protected function doPostMaintenance(array $params){
    list($jobId,$postId,$timeFrame,$date)=$this->extractParams([self::$PARAM_JOB_ID,
      self::$PARAM_POST_ID,
      'myHomeTimeFrame', // It must be null when $attDailyLimit=1
      'myHomeDate'],$params);

    if(!$jobId)
      throw new MyHomeException('Job ID not provided');
    if(!$postId)
      throw new MyHomeException('Post ID not provided');

    $cachedPostAtts=$this->retrievePostAtts($postId);
    if(!is_array($cachedPostAtts))
      throw new MyHomeException('Post cached attributes not available');

    // Retrieve the daily limit attribute set at the source page
    list($attDailyLimit,$attExcludeComingDays)=$this->retrieveShortcodeAtts($cachedPostAtts);

    // Set the appointment time in hh:mm format (with leading zeros) - it is obtained from the myHomeTimeFrame if $attDailyLimit==2
    if($attDailyLimit==1)
      $time='08:00';
    else
      switch($timeFrame){
        case 'm':
          $time='08:00';
          break;
        case 'a':
          $time='13:00';
          break;
        default:
          $this->flashVar('error',__('The Time Frame provided is invalid','myHome'));
          return false;
      }

    // Check the date provided
    // $dt=DateTime::createFromFormat('d/m/Y',$date);
    $dt=myHome()->wpDateTimeFromFormat('d/m/Y',$date);
    if($dt===false){
      // $this->flashVar('error',__('The Date provided is invalid','myHome'));
      // return false;
    }

    // Allow $dt to be compared to $minDate below
    $dt->setTime(0,0);

    $minDate=myHome()->wpDateTime();
    $minDate->add(new DateInterval(sprintf('P%uD',$attExcludeComingDays)));
    $minDate->setTime(0,0);

    // Verify that the date provided is not within the days excluded by the WordPress admin
    $difference=$dt->diff($minDate);
    if($difference->days!==0&&
      $difference->invert===0
    ){ // If days is not 0 and invert is 0, it means a positive difference - $minDate is later than $dt
      $this->flashVar('error',__('The Date provided is unavailable','myHome'));
      return false;
    }

    $appointmentExclusions=$this->retrieveAppointmentExclusions($jobId);

    // Verify that the date provided is not listed as not available for appointment
    foreach($appointmentExclusions as $appointmentExclusion)
      if($appointmentExclusion->format('d/m/Y')===$date){
        $this->flashVar('error',__('The Date provided is unavailable','myHome'));

        return false;
      }

    $authentication=myHome()->session->getAuthentication();
    $api=myHome()->api;

    $command=['maintenancejobs',$jobId,'appointments'];
    $dateTime=sprintf('%sT%s:00',$dt->format('Y-m-d'),$time); // Full date and time (eg "2014-09-18T18:05:00")
    $appointmentParams=[
      'appointmentdate'=>$dateTime,
      'comments'=>'Client meeting'
    ];

    // Schedule the appointment
    $appointmentResponse=$api->post($command,$appointmentParams,$authentication,false);
    myHome()->log->info('appointmentResponse: ' . json_encode($appointmentResponse));

    if($appointmentResponse===null||!isset($appointmentResponse->id)) {
      myHome()->log->info('submitResponse FAIL');
      $this->notifyApiError(__('Appointment failed','myHome'));
    }

    // Submit the maintenance job
    $command=['maintenancejobs',$jobId,'submit'];
    $submitResponse=$api->post($command,[],$authentication,false);
    myHome()->log->info('maintJobResponse: ' . json_encode($submitResponse));

    if($submitResponse===null||!isset($submitResponse->id)) {
      myHome()->log->info('maintJobResponse FAIL');
      $this->notifyApiError(__('Job submission failed','myHome'));
    }

    // Invalidate the cached entry for this job, as its state may have changed
    $this->deleteJobDetails($jobId);

    return true;
  }

  /**
   * {@inheritDoc}
   */
  protected function doShortcodeMaintenance(array $atts){
    // Store the attributes in the cache
    $this->cachePostAtts($atts);

    list($attDailyLimit,$attExcludeComingDays)=$this->retrieveShortcodeAtts($atts);

    // The job ID must be present as a GET parameter
    $jobId=$this->getParam(self::$PARAM_JOB_ID);
    if($jobId===null)
      throw new MyHomeException('Job ID not provided');

    // Check for the existence of this job
    if(!$this->retrieveJobDetails($jobId))
      throw new MyHomeException('Job not found: '.$jobId);

    $appointmentExclusions=$this->retrieveAppointmentExclusions($jobId);
    //myHome()->log->info("retrieveAppointmentExclusions: " . json_encode($appointmentExclusions));

    $redirectUrlError=$this->maintenancePagePermalink('review');
    $redirectUrlError=add_query_arg([self::$PARAM_JOB_ID=>$jobId],$redirectUrlError);

    $redirectUrl=$this->maintenancePagePermalink('confirmed');
    $redirectUrl=add_query_arg([self::$PARAM_JOB_ID=>$jobId],$redirectUrl);

    $paramJobId=static::$PARAM_JOB_ID;
    $paramPostId=static::$PARAM_POST_ID;

    // Values passed to the view:
    // * attDailyLimit: whether to display (2) or not (1) the time frame option
    // * attExcludeComingDays: number of days from today excluded by the WordPress admin
    // * jobId: current job ID
    // * appointmentExclusions: array of dates not available for appointment (each element is a DateTime object)
    // * redirectUrl: URL to redirect to on success (when clicking "Submit and Add More")
    // * redirectUrlError: URL to redirect to on error
    // * paramJobId: parameter name for the job ID
    // * paramPostId: parameter name for the post ID of the current page
    $this->loadView('shortcodeMaintenanceReview','MyHomeShortcodes',
      compact('attDailyLimit','attExcludeComingDays','jobId','appointmentExclusions','redirectUrl',
        'redirectUrlError','paramJobId','paramPostId'));
  }

  /**
   * Retrieves a list of appointment exclusions for a given job ID
   *
   * If not cached, it is downloaded from the API server and then stored in the cache
   *
   * @uses MyHomeApi::get()
   * @uses MyHomeBaseController::cacheVar()
   * @uses MyHomeBaseController::restoreVar()
   * @param int $jobId the job ID
   * @return DateTime[] the array of appointment exclusions
   */
  private function retrieveAppointmentExclusions($jobId){
    $varName=sprintf('appointmentExclusions%u',$jobId);
    $appointmentExclusions=$this->restoreVar($varName);

    // If the variable is not in the cache, download it and store it there
    if($appointmentExclusions===null){
      $authentication=myHome()->session->getAuthentication();
      $command=['maintenancejobs',$jobId,'appointmentexclusions'];
      $appointmentExclusionsResponse=myHome()->api->get($command,$authentication,true);
      myHome()->log->info('appointmentExclusions: ' . json_encode($appointmentExclusionsResponse), JSON_PRETTY_PRINT);

      $appointmentExclusions=[];

      if(is_array($appointmentExclusionsResponse))
        foreach($appointmentExclusionsResponse as $appointmentExclusion)
          $appointmentExclusions[] = new DateTime($appointmentExclusion->appointmentdate);

      $this->cacheVar($varName,$appointmentExclusions);
    }

    return $appointmentExclusions;
  }

  /**
   * Retrieves the daily limit (dailylimit) and the exclude coming days (excludecomingdays) shortcode attributes from a
   * given attributes array
   *
   * @param string[] $atts shortcode attributes
   * @return int[] the shortcode attributes:
   *                       <ul>
   *                       <li>Daily limit: 1=hide the time frame option, 2=show the time frame option</li>
   *                       <li>Exclude coming days: 1 or greater to exclude the next days (1 excludes only today), 0 to
   *                       disable</li>
   *                       </ul>
   */
  private function retrieveShortcodeAtts(array $atts){
    $atts=shortcode_atts(['dailylimit'=>'1',
      'excludecomingdays'=>'0'],$atts);

    $attDailyLimit=$atts['dailylimit'];
    $attExcludeComingDays=$atts['excludecomingdays'];

    if(!in_array($attDailyLimit,['1','2'])){
      myHome()->handleError('Wrong Daily Limit attribute: '.$attDailyLimit);
      $attDailyLimit=1;
    }

    if($attExcludeComingDays<0){
      myHome()->handleError('Wrong Exclude Coming Days attribute: '.$attExcludeComingDays);
      $attExcludeComingDays=0;
    }

    return [(int)$attDailyLimit,(int)$attExcludeComingDays];
  }
}
