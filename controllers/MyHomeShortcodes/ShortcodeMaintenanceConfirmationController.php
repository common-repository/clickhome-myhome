<?php

/**
 * The ShortcodeMaintenanceConfirmationController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeMaintenanceConfirmationController'))
  return;

/**
 * The ShortcodeMaintenanceConfirmationController class
 *
 * Controller for the Maintenance Confirmation shortcode
 *
 * @since 1.2
 */
class ShortcodeMaintenanceConfirmationController extends ShortcodeMaintenanceBaseController{
  /**
   * {@inheritDoc}
   */
  protected function doPostMaintenance(array $params){
    list($contactName,$contactPhone,$contactEmail,$contactAgent,$agentName,$agentCompany,$agentPhone,$agentEmail,
      $propertyStreetNo,$propertyUnitNo,$propertyLevelNo,$submit)=$this->extractParams(['myHomeContactName',
      'myHomeContactPhone',
      'myHomeContactEmail',
      'myHomeContactAgent',
      'myHomeAgentName',
      'myHomeAgentCompany',
      'myHomeAgentPhone',
      'myHomeAgentEmail',
      'myHomePropertyStreetNo',
      'myHomePropertyUnitNo',
      'myHomePropertyLevelNo',
      'myHomeSubmit'],$params);

    // Don't make any changes if the client clicked on "Continue"
    if($submit==='continue')
      return;

    // $contactAgent is a checkbox, therefore it is null if not checked (absent from POST parameters)
    $contactAgent=$contactAgent!==null;

    $confirmationParams=['id'=>myHome()->session->getSessionId(),
      'contactname'=>$contactName,
      'contactphone'=>$contactPhone,
      'contactemail'=>$contactEmail,
      'contactagent'=>$contactAgent?'true':'false', // Send boolean as string
      'agentname'=>$agentName,
      'agentcompany'=>$agentCompany,
      'agentphone'=>$agentPhone,
      'agentemail'=>$agentEmail,
      'lotstreetno'=>$propertyStreetNo,
      'lotunitno'=>$propertyUnitNo,
      'lotlevelno'=>$propertyLevelNo];

    $authentication=myHome()->session->getAuthentication();
    $api=myHome()->api;

    $confirmationResponse=$api->post('job',$confirmationParams,$authentication,false);
    myHome()->log->info("confirmationResponse: " . var_dump($confirmationResponse) . "\n");     

    if($confirmationResponse===null||!isset($confirmationResponse->id))
      $this->notifyApiError(__('Address update failed','myHome'));

    // Update cached job details
    $jobDetails=$api->get('job',$authentication,false);
    if($jobDetails!==null)
      myHome()->session->updateJobDetails($jobDetails);
  }

  /**
   * {@inheritDoc}
   */
  protected function doShortcodeMaintenance(array $atts){
    // Get job details from the cached array in the MyHome session
    $jobDetails=
      clone myHome()->session->getJobDetails(); // Clone it, so it can be safely modified if needed within this request
    if($jobDetails===null)
      throw new MyHomeException('Job Details not found');

    $redirectUrl=$this->maintenancePagePermalink('request');
    $redirectUrlError=$this->maintenancePagePermalink('confirmation');

    // Values passed to the view:
    // * jobDetails: cached job details for the client - not to be confused with maintenance jobs
    // * redirectUrl: URL to redirect to on success
    // * redirectUrlError: URL to redirect to on error
    $this->loadView('shortcodeMaintenanceConfirmation','MyHomeShortcodes',
      compact('jobDetails','redirectUrl','redirectUrlError'));
  }
}
