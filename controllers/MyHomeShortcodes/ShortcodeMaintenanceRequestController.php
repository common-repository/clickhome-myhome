<?php

/**
 * The ShortcodeMaintenanceRequestController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeMaintenanceRequestController'))
  return;

/**
 * The ShortcodeMaintenanceRequestController class
 *
 * Controller for the Maintenance Request shortcode
 *
 * @since 1.2
 */
class ShortcodeMaintenanceRequestController extends ShortcodeMaintenanceBaseController{
  /**
   * {@inheritDoc}
   */
  protected function doPostMaintenance(array $params) {
    list($postId, $type) = $this->extractParams([self::$PARAM_POST_ID, 'myHomeMaintenanceType'], $params); // list($postId, $type, $attrMultipleJobs, $attrMoreIssues, $attrMoreIssuesLimit) = $this->extractParams([self::$PARAM_POST_ID, 'myHomeMaintenanceType', 'myHomeAllowMultipleJobs', 'myHomeAllowMoreIssues', 'myHomeAllowMoreIssuesLimit'], $params);
    $maintenanceTypes = $this->retrievePostAtts($postId);
    $selectedType = $maintenanceTypes[$type];
    //myHome()->log->info("maintenanceTypes: " . json_encode($maintenanceTypes));
    if(!is_array($maintenanceTypes)) throw new MyHomeException('Post cached maintenanceTypes not available');
    if(!$selectedType) throw new MyHomeException('Maintenance type not provided');
    if(!$postId) throw new MyHomeException('Post ID not provided');

    myHome()->log->info("selection: " . json_encode($selectedType, JSON_PRETTY_PRINT));
    /*myHome()->log->info("params: " . json_encode($params, JSON_PRETTY_PRINT));
    if($attrMoreIssues) { }*/

    $jobParams = (object) array(
      'type' => $selectedType->name,
      'name' => sprintf('Created by ClickHome.MyHome WordPress Plugin'),
      //'description' => null
    );
    
    // If issueDestination = newJob - create & return a new job
    if($selectedType->job == 'new') //issueDestination == 'newJob')
      $job = myHome()->api->post('maintenancejobs', (array) $jobParams, myHome()->session->getAuthentication(), false);
    else if(isset($selectedType->job))
      $job = $selectedType->job; //  myHome()->api->get('maintenancejobs', $jobParams, myHome()->session->getAuthentication(), false);
    
    // myHome()->log->info("job of selected type: " . json_encode($job, JSON_PRETTY_PRINT));

    $queryVars = [self::$PARAM_JOB_ID => $job->id];
    return $queryVars;
  }

  /**
   * {@inheritDoc}
   */
  protected function doShortcodeMaintenance(array $atts) {//  echo json_encode($atts, JSON_PRETTY_PRINT);
    $jobDetails = myHome()->session->getJobDetails(); // myHome()->log->info("jobDetails: " . json_encode($jobDetails));
    // Attributes
    if(isset($atts['moreissues']) && strpos($atts['moreissues'], ',')) {
      $attrMoreIssues = explode(',', $atts['moreissues'])[0] == 'true';
      $attrMoreIssuesLimit = (int) explode(',', $atts['moreissues'])[1];
      // myHome()->log->info('attrMoreIssuesLimit: ' . json_encode($attrMoreIssuesLimit));
    } else $attrMoreIssues = isset($atts['moreissues']) ? $atts['moreissues'] == 'true' : false;
    
    if(isset($atts['multiplejobs']) && strpos($atts['multiplejobs'], ',')) {
      $attrMultipleJobs = explode(',', $atts['multiplejobs'])[0] == 'false';
      $attrMultiplejobsLimit = (int) explode(',', $atts['multiplejobs'])[1];
      // myHome()->log->info('attrMultiplejobsLimit: ' . json_encode($attrMultiplejobsLimit));
    } else $attrMultipleJobs = isset($atts['multiplejobs']) ? $atts['multiplejobs'] !== 'false' : true; // myHome()->log->info('attrMultipleJobs: ' . json_encode($attrMultipleJobs));
    //echo('multiJobs:' . json_encode($attrMultipleJobs));

    // Hidden Form Inputs
    $paramPostId = static::$PARAM_POST_ID;
    $redirectUrl = $this->maintenancePagePermalink('issues');
    $redirectUrlError = $this->maintenancePagePermalink('request');
    
    // Get Api Maint. types
    $apiMaintTypes = $this->retrieveMaintenanceTypes(); // myHome()->log->info('API Maintenance types configured: ' . json_encode($apiMaintTypes, JSON_PRETTY_PRINT));
    $attrMaintTypes = $this->attrMaintenanceTypes($atts); // myHome()->log->info('MaintRequest shortcode attrs: ' . json_encode($attrMaintTypes));
    if($attrMoreIssues || !$attrMultipleJobs) { // myHome()->log->info('getAuthentication: ' . json_encode(myHome()->session->getAuthentication(), JSON_PRETTY_PRINT));
      $apiMaintJobs = myHome()->api->get('maintenancejobs', myHome()->session->getAuthentication(), false);
      @usort($apiMaintJobs, function($a, $b) { return strcmp($b->id, $a->id); }); // Order by ID DESC
      //myHome()->log->info('apiMaintJobs: ' . json_encode($apiMaintJobs, JSON_PRETTY_PRINT));
      //if($apiMaintJobs === null) throw new MyHomeException('Maintenance Jobs is null');
    }
    
    // Get Handover Date
    try { 
      $daysSinceHandover = (int) date_diff(myHome()->wpDateTime(@$jobDetails->handoverdate)->setTime(0,0), myHome()->wpDateTime()->setTime(0,0))->format("%r%a"); // $daysSinceHandover = myHome()->wpDateTime(@$jobDetails->handoverdate)->setTime(0,0) -> diff(myHome()->wpDateTime()->setTime(0,0)) -> days;
      myHome()->log->info('daysSinceHandover: ' . json_encode($daysSinceHandover));
    } catch(Exception $e) { throw new MyHomeException('Wrong handover date: ' . $jobDetails->handoverdate); }

    // Determine if declared types should be enabled
    $maintenanceTypes = [];
    foreach($attrMaintTypes as $name => $range) {
      myHome()->log->info('For Attr: ' . $name . ' - ' . json_encode($range) . ' - attrMoreIssues: ' . json_encode($attrMoreIssues) . ', attrMultipleJobs: ' . json_encode($attrMultipleJobs));
      if(!isset($apiMaintTypes[$name])) { myHome()->log->info('Skipping ' . $name . ' - no match in API response.'); continue; } // attrType must exist in apiTypes
      if($daysSinceHandover < $range->min || (isset($range->max) && $daysSinceHandover > $range->max)) { myHome()->log->info('Skipping "' . $name . '" as outside daysSinceHandover (' . json_encode($daysSinceHandover) . ')'); continue; }

      // Prevent duplicates by always adding - then removing if/when attrs don't match
      if(!isset($maintenanceTypes[$name])) {
        $maintenanceTypes[$name] = $apiMaintTypes[$name]; 
        $maintenanceTypes[$name]->job = 'new';
      }

      // Unless we want to combine maintIssues with the most recent maintJob (ordered DESC)
      if($attrMoreIssues || !$attrMultipleJobs) {
        if($apiMaintJobs) foreach($apiMaintJobs as $job) {
          $jobType = str_replace(' ', '', strtolower($job->type)); // trim(strtolower($job->type)); 
          if($jobType != $name) { // myHome()->log->info($jobType . ' != ' . $name); 
            continue; 
          };
          myHome()->log->info('For ' . $jobType . ' Job: ' . $job->job);
          if(in_array(strtolower($job->status), ['c', 'x']) ) { myHome()->log->info('Skipping ' . $jobType . ' Job: ' . $job->job . ' - job status: ' . $job->status);
            unset($maintenanceTypes[$name]);
            continue;
          }
          
          // Calculate days since job created
          if($attrMoreIssuesLimit || $attrMoreIssuesLimit) try { 
            $daysSinceJobCreated = (int) date_diff(myHome()->wpDateTime(@$job->createddate)->setTime(0,0), myHome()->wpDateTime()->setTime(0,0))->format("%r%a");
            // myHome()->log->info('daysSinceJobCreated: ' . $daysSinceJobCreated . ', moreIssuesLimit: ' . $attrMoreIssuesLimit);
          } catch(Exception $e) { throw new MyHomeException('Wrong created date: ' . $job->createddate); }

          // MoreIssues,WithinDaysSinceJobCreated takes priority
          if($attrMoreIssues) {
            if($attrMoreIssuesLimit && $daysSinceJobCreated > $attrMoreIssuesLimit) { 
              if(!$attrMultipleJobs) { myHome()->log->info('Skipping ' . $jobType . ' Job: ' . $job->job . ' has existing job or exceeds moreIssuesLimit(' . $attrMoreIssuesLimit . '). daysSinceJobCreated: ' . $daysSinceJobCreated);
                unset($maintenanceTypes[$name]);
                continue;
              }
            } else { 
              $maintenanceTypes[$name]->job = $job; myHome()->log->info('Added ' . $name . ' to available types. Existing job: ' . $job->job . ' - daysSinceJobCreated: ' . $daysSinceJobCreated . ', moreIssuesLimit: ' . $attrMoreIssuesLimit);
              break; // We are within spec - stop looking to remove it
            }
          } else if(!$attrMultipleJobs) { myHome()->log->info('Skipping ' . $jobType . ' Job: ' . $job->job . ' - Multiple jobs is disabled');
            unset($maintenanceTypes[$name]);
            break;
          }

          // MultipleJobs,WithinDays
          if(!$attrMultipleJobs || ($attrMultiplejobsLimit && $daysSinceJobCreated > $attrMultiplejobsLimit)) { // && $maintenanceTypes[$name]->job == 'new')) {
            if(!$attrMultipleJobs) myHome()->log->info('Skipping ' . $jobType . ' Job: ' . $job->job . ' is existing job.');
            else myHome()->log->info('Skipping ' . $jobType . ' Job: ' . $job->job . ' exceeds multipleJobsLimit(' . $attrMultiplejobsLimit . '). daysSinceLastJobCreated: ' . $daysSinceJobCreated);
            unset($maintenanceTypes[$name]);
            continue;
          }

          myHome()->log->info('Added ' . $name . ' to available types (new job) - daysSinceJobCreated: ' . $daysSinceJobCreated);
          break; // We ordered by DESC, so once we have the newest, exit
        }
      } else myHome()->log->info('Added ' . $name . ' to available types. New job.');
    } myHome()->log->info('maintenanceTypesDisplayed: ' . json_encode($maintenanceTypes, JSON_PRETTY_PRINT));
       
    // Cache/Support multiple shortcode atts
    static $count = 0; $count++; // myHome()->log->info('shortcode #' . $count);
    $otherShortcodeMaintTypes = $count>1 ? $this->retrievePostAtts(get_the_ID()) : Array();
    if($otherShortcodeMaintTypes == null) $otherShortcodeMaintTypes = Array(); // myHome()->log->info('otherShortcodeMaintTypes: ' . json_encode(array_keys($otherShortcodeMaintTypes), JSON_PRETTY_PRINT) . ' + ' . json_encode(array_keys($maintenanceTypes)), JSON_PRETTY_PRINT);
    $mergedShortcodeTypes = $maintenanceTypes + $otherShortcodeMaintTypes; // myHome()->log->info('mergedShortcodeAtts: ' . json_encode($mergedShortcodeTypes, JSON_PRETTY_PRINT));
    $this->cachePostAtts($mergedShortcodeTypes);

    $this->loadView('shortcodeMaintenanceRequest','MyHomeShortcodes', compact('maintenanceTypes','paramPostId','attrMultipleJobs','attrMoreIssues','attrMoreIssuesLimit','redirectUrl','redirectUrlError'));
  }

  private function attrMaintenanceTypes(array $atts) {
    $attrMaintTypes = [];
    foreach($atts as $attribute => $value) {
      if(strtolower($value) === 'disable') continue; // Skip type=disabled params
      if(in_array($attribute, array('multiplejobs', 'moreissues'))) continue; // Skip params that are options
      //echo("attribute: '" . $attribute . "': '" . $value . "'<br/>");
      if(preg_match('|(\d+)[,-](\d*)|', $value, $range)) { // Populate $range with min/max
        $attrMaintTypes[$attribute] = (object) array(
          'min' => (int)$range[1],
          'max' => isset($range[2]) ? (int)$range[2] : null
        );
      }
    }
    return $attrMaintTypes;
  }
}