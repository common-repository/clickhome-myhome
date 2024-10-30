<?php

/**
 * The ShortcodeMaintenanceBaseController class
 *
 * @package    MyHome
 * @subpackage Controllers
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeMaintenanceBaseController'))
  return;

/**
 * The ShortcodeMaintenanceBaseController class
 *
 * Abstract class for Maintenance controllers
 *
 * @since 1.2
 */
abstract class ShortcodeMaintenanceBaseController extends MyHomeShortcodesBaseController{
  /**
   * POST parameter name for the post ID of the current page - post ID is sent to admin-post.php to retrieve the cached
   * shortcode attributes set by the admin
   */
  protected static $PARAM_POST_ID='myHomePostId';

  /**
   * GET and POST parameter name for the job ID
   */
  protected static $PARAM_JOB_ID='myHomeJobId';

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    return $this->doPostMaintenance($params);
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
    // Required maintenance pages have to be set by WordPress admin in order to guarantee that the whole process works - ie after the Confirmation page must come the Request page, etc.
    if(!$this->retrieveMaintenancePages())
      throw new MyHomeException('Maintenance Pages not available');

    return $this->doShortcodeMaintenance($atts);
  }

  /**
   * Stores the current post shortcode attributes in the cache for use in a subsequent request
   *
   * @uses get_the_ID()
   * @uses MyHomeBaseController::cacheVar()
   * @param string[] $atts shortcode attributes to store
   * @throws MyHomeException if the post ID is not available
   */
  protected function cachePostAtts(array $atts){
    $postId=get_the_ID();
    if(!$postId)
      throw new MyHomeException('Post ID not available');

    $this->cacheVar(sprintf('postAtts%u',$postId),$atts);
  }

  /**
   * Retrieves from the cache the shortcode attributes for a given post ID
   *
   * @uses MyHomeBaseController::restoreVar()
   * @param int $postId the post ID
   * @return string[]|null the shortcode attributes for the post ID, if found, or null otherwise
   */
  protected function retrievePostAtts($postId){
    return $this->restoreVar(sprintf('postAtts%u',$postId));
  }

  /**
   * Deletes the cached details for a given job ID, if present
   *
   * @uses MyHomeBaseController::deleteVar()
   * @param int $jobId the job ID
   * @return stdClass|null the previous job details, if available, or null otherwise
   */
  protected function deleteJobDetails($jobId){
    $varName=sprintf('maintenanceJob%u',$jobId);
    myHome()->log->info("deleteJobDetails: " . $varName . "\n");  
    return $this->deleteVar($varName,true); // $global=true
  }

  /**
   * Handles the POST request
   *
   * @param string[] $params POST parameters to be used by the controller (eg array("myHomeContactName"=>"John Doe"))
   * @return bool|null false if an error occurred or null/true otherwise - used to redirect to the error redirect URL
   */
  abstract protected function doPostMaintenance(array $params);

  /**
   * Handles the "shortcode request"
   *
   * @param string[] $atts shortcode attributes (eg array("maxissues"=>"5"))
   */
  abstract protected function doShortcodeMaintenance(array $atts);

  /**
   * Checks if a given page is set in this WordPress site
   *
   * @param string $page the page name (confirmation, request, issues, review, or confirmed)
   * @return bool whether the page is set
   */
  protected function isPageSet($page){
    return !empty($this->maintenancePages[$page]);
  }

  /**
   * Returns the permalink for a given maintenance page, based on the settings provided by the WordPress admin
   *
   * @uses ShortcodeMaintenanceBaseController::maintenancePages
   * @param string $page the page name (confirmation, request, issues, review, or confirmed)
   * @return string page permalink
   */
  protected function maintenancePagePermalink($page){
    return get_permalink($this->maintenancePages[$page]);
  }

  /**
   * Notifies an API error
   *
   * <p>It stores an error message as flash data, which is then displayed on the screen</p>
   * <p>It also throws an exception providing details on the error, which is then caught by
   * MyHomeShortcodes::handleShortcode() or MyHomeAdminPostHandler::handleAction()</p>
   *
   * @uses MyHomeApi::getLastErrorType()
   * @uses MyHomeApi::getLastErrorMessage()
   * @param string $error the error message to be presented to the user
   * @throws MyHomeException the exception containing the detailed error message
   */
  protected function notifyApiError($error){
    $this->flashVar('error',$error);

    $api=myHome()->api;

    if($api->getLastErrorType()===MyHomeApi::$ERROR_TYPE_API) // This one never happens
      $errorMessage=sprintf('Maintenance error: The API returned an error (%s)',$api->getLastErrorMessage());
    else
      $errorMessage=sprintf('Maintenance error: The API call could not be completed (%s)',$api->getLastErrorMessage());

    throw new MyHomeException($errorMessage);
  }

  /**
   * Retrieves the details for a given job ID
   *
   * If not cached, the details are downloaded from the API server and then stored in the cache
   *
   * @uses MyHomeApi::get()
   * @uses MyHomeBaseController::cacheVar()
   * @uses MyHomeBaseController::restoreVar()
   * @param int $jobId the job ID
   * @return stdClass|null the job details, if available, or null otherwise
   */
  protected function retrieveJobDetails($jobId){
    $varName=sprintf('maintenanceJob%u',$jobId);
    $jobDetails=$this->restoreVar($varName,null,true); // $global=true

    // If the variable is not in the cache, download it and store it there
    if($jobDetails===null){
      $authentication=myHome()->session->getAuthentication();
      $command=['maintenancejobs',$jobId];
      $jobResponse=myHome()->api->get($command,$authentication,false);

      if($jobResponse!==null&&isset($jobResponse->id)){
        $jobDetails=$jobResponse;
        $this->cacheVar($varName,$jobDetails,true,true); // $global=true
      }
    }

    return $jobDetails;
  }

  /**
   * Retrieves a list of maintenance types
   *
   * If not cached, it is downloaded from the API server and then stored in the cache
   *
   * @uses MyHomeApi::get()
   * @uses MyHomeBaseController::cacheVar()
   * @uses MyHomeBaseController::restoreVar()
   * @return string[] the array of maintenance types:
   * <ul>
   * <li>Array key: maintenance type code (eg "90DayM")</li>
   * <li>Array value: maintenance type title (eg "90 Day Maintenance")</li>
   * </ul>
   */
  protected function retrieveMaintenanceTypes(){
    //myHome()->log->info("retrieveMaintenanceTypes()");
    $maintenanceTypes = $this->restoreVar('maintenanceTypes', null, true); // $global=true

    // If the variable is not in the cache, download it and store it there
    if($maintenanceTypes === null){
      $maintenanceTypesResponse = myHome()->api->get('maintenancetypes', myHome()->api->authenticationHeadersApiKey(myHome()->options->getAdvertisingApiKey()), true);
      //myHome()->log->info("maintenanceTypesResponse: " . json_encode($maintenanceTypesResponse));

      $maintenanceTypes = [];

      if(is_array($maintenanceTypesResponse))
        foreach($maintenanceTypesResponse as $maintenanceType) {
          //myHome()->log->info("maintenanceType " . json_encode($maintenanceType));
          $name = trim(strtolower($maintenanceType->name));
          $maintenanceTypes[$name] = (object) array(
            'name' => $name,
            'title' => $maintenanceType->title,
            'description' => isset($maintenanceType->description) ? $maintenanceType->description : null
          );
        }

      $this->cacheVar('maintenanceTypes', $maintenanceTypes, true, true); // $global=true
    } // else myHome()->log->info("restored cached maintenanceTypes:" . json_encode($maintenanceTypes, JSON_PRETTY_PRINT));
    return $maintenanceTypes;
  }

  /**
   * Retrieves the list of post IDs for each required maintenance page from MyHome options
   *
   * @uses MyHomeOptions::getMaintenancePages()
   * @return bool whether all required pages were set up or not
   */
  private function retrieveMaintenancePages(){
    $requiredPages=['request',
      'issues',
      'confirmed'];

    $maintenancePages=myHome()->options->getMaintenancePages();

    foreach($requiredPages as $page)
      if(empty($maintenancePages[$page]))
        return false;

    $this->maintenancePages=$maintenancePages;

    return true;
  }

  /**
   * Maintenance page IDs, indexed by the page code (confirmation, request, issues, and review)
   *
   * @var int[]
   */
  private $maintenancePages;
}
