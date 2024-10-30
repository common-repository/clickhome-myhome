<?php

/**
 * The ShortcodeMaintenanceIssuesController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeMaintenanceIssuesController'))
  return;

/**
 * The ShortcodeMaintenanceIssuesController class
 *
 * Controller for the Maintenance Issues shortcode
 *
 * @since 1.2
 */
class ShortcodeMaintenanceIssuesController extends ShortcodeMaintenanceBaseController{
  /**
   * Maximum number of files per issue
   */
  protected static $MAX_FILES=3;

  /**
   * {@inheritDoc}
   */
  protected function doPostMaintenance(array $params){
    list($redirectUrlContinue, $jobId, $postId, $skipReviewScreen, $issues, $submit) = $this->extractParams([
      'myHomeRedirectUrlContinue',
      self::$PARAM_JOB_ID,
      self::$PARAM_POST_ID,
      'myHomeSkipReviewScreen',
      'myHomeIssues',
      'myHomeSubmit'
    ], $params);
    $files = !empty($_FILES['myHomeIssues']) ? $_FILES['myHomeIssues'] : [];
    //myHome()->log->info("doPostMaintenance: " . json_encode($params)); 
    //myHome()->log->info("issues myHomeAllowExtraJobs: " . $params['myHomeAllowExtraJobs']);
    //myHome()->log->info("issues myHomeAllowAddition: " . $params['myHomeAllowAddition']);
    //myHome()->log->info("files['error']: " . serialize($files['error']) . "\n"); 

    if(!$redirectUrlContinue)
      throw new MyHomeException('Redirect URL (continue) not provided');
    if(!$jobId)
      throw new MyHomeException('Job ID not provided');
    if(!$postId)
      throw new MyHomeException('Post ID not provided');
    if(!$issues)
      throw new MyHomeException('Issues not provided');
    if(!$submit)
      throw new MyHomeException('Submit action not provided');

    $cachedPostAtts = $this->retrievePostAtts($postId); myHome()->log->info('cachedPostAtts: ' . json_encode($cachedPostAtts, JSON_PRETTY_PRINT));
    if(!is_array($cachedPostAtts))
      throw new MyHomeException('Post cached attributes not available');

    // Retrieve the cached maximum issues attribute set at the source page
    $attMaxIssues=$this->retrieveAttMaxIssues($cachedPostAtts);

    // If no other error is found, notify at the end errors related to maximum issues or maximum files
    $maxIssuesError=false;
    $maxFilesError=false;

    // Verify the number of issues received (if $attMaxIssues>0)
    if($attMaxIssues&&count($issues)>$attMaxIssues){
      $issues=array_slice($issues,0,$attMaxIssues,true);
      $maxIssuesError=true;
    }

    // Get the maximum file size (stored in MiB)
    $maxFileSize=1000000*myHome()->options->getMaintenanceMaxFileSize();

    $authentication=myHome()->session->getAuthentication();
    $api=myHome()->api;

    // Process each issue
    foreach($issues as $id=>$issue){
      $issueParams = [
        'name' => stripslashes($issue['title']),
        'description' => stripslashes($issue['description'])
      ];
      myHome()->log->info("issueParams: " . json_encode($issueParams));

      $command = ['maintenancejobs', $jobId, 'maintenanceissues'];

      // If ID is positive, the issue already exists and must be updated
      if($id >= 1){
        $issueParams['status'] = 1;
        $command[] = $id; // The full command URL is maintenancejobs/{$jobId}/maintenanceissues/{$issueId}
      }
      //if($params['myHomeAllowExtraJobs']) {
        //myHome()->log->info("myHomeAllowExtraJobs: " . json_encode($params['myHomeAllowExtraJobs']));
      //}

      // Create or update the issue
      $issueResponse = $api->post($command,$issueParams,$authentication,false); // myHome()->log->info("issueResponse: " . json_encode($issueResponse, JSON_PRETTY_PRINT));

      if($issueResponse===null||!isset($issueResponse->id))
        $this->notifyApiError(__('Issue update failed','myHome'));

      // If $id is negative, $issueId contains the ID of the new issue; otherwise, it should have the same value as $id
      $issueId=$issueResponse->id;

      // $existingFiles contains the list of IDs of files already present in the server
      $existingFiles=explode(',',$issue['existingFiles']);
      $existingFiles=array_filter($existingFiles,'strlen');
      $numExistingFiles=count($existingFiles);

      // Remove files deleted by the user
      if(false) // Not working yet - skip
        foreach($existingFiles as $fileId)
          // $issue['files'][$fileId] is present if the file was not deleted
          if(empty($issue['files'][$fileId])){
            $command=['maintenancejobs',$jobId,'maintenanceissues',$issueId,'documents',$fileId];
            $documentDeleteResponse=$api->delete($command,$authentication,false);

            if($documentDeleteResponse===null)
              $this->notifyApiError(__('Document deletion failed','myHome'));

            $numExistingFiles--;
          }

      // New issues are indexed by $id, not by $issueId (eg $files['name'][-1]['files'])
      if(!empty($files['name'][$id]['files']))
        $newFiles=$files['name'][$id]['files'];
      else
        $newFiles=[];

      // Restrict the number of total files for this issue
      if($numExistingFiles+count($newFiles)>self::$MAX_FILES){
        $maxFilesError=true;

        // Existing files have priority
        if($numExistingFiles>=self::$MAX_FILES)
          $newFiles=[];
        else
          $newFiles=array_slice($newFiles,0,self::$MAX_FILES-$numExistingFiles,true);
      }

      //myHome()->log->info("temp_dir: " . get_temp_dir() . "\n");      

      // Handle new file uploads
      foreach($newFiles as $key=>$filename){
        $error=$files['error'][$id]['files'][$key];
        $tempFilename=$files['tmp_name'][$id]['files'][$key];
        $size=$files['size'][$id]['files'][$key];
        //myHome()->log->info("file error: " . ((int)$error!==0?'true':'false') . "\n");     

        // Throw an exception if more detailed info can be provided; otherwise, simply return false
        if((int)$error!==UPLOAD_ERR_OK){
          $this->flashVar('error',sprintf(__('Error while receiving the file "%s"','myHome'),$filename));
          throw new MyHomeException(sprintf('Error while receiving the file "%s": error=%u',$filename,$error), $error);
        }
        else if(!is_uploaded_file($tempFilename)){
          $this->flashVar('error',sprintf(__('Error while receiving the file "%s"','myHome'),$filename));
          throw new MyHomeException(sprintf('Error while receiving the file "%s": file not uploaded',$filename));
        }
        else if(!$this->verifyImageFileType($filename)){
          $this->flashVar('error',sprintf(__('The file "%s" is not an image','myHome'),$filename));

          return false;
        }
        else if($size>$maxFileSize){
          $this->flashVar('error',
            sprintf(__('The file "%s" exceeds the size limit (%u bytes)','myHome'),$filename,$maxFileSize));

          return false;
        }

        $contents=@file_get_contents($tempFilename);

        if(!$contents){
          $this->flashVar('error',sprintf(__('Error while receiving the file "%s"','myHome'),$filename));
          throw new MyHomeException(sprintf('Error while receiving the file "%s": file not readable or empty',
            $filename));
        }

        $command=['maintenancejobs',$jobId,'maintenanceissues',$issueId,'documents?title='.rawurlencode($filename)];
        $documentParams=['content'=>base64_encode($contents)];

        // Upload the file base64 encoded; the title is set in the command URL
        $documentUploadResponse=$api->put($command,$documentParams,$authentication,false);

        if($documentUploadResponse===null||!isset($documentUploadResponse->id)) {
          myHome()->log->info("notifyApiError: " . var_dump($documentUploadResponse) . "\n");     
          $this->notifyApiError(__('Document upload failed','myHome'));
        }
      }
    }

    // If an error occurred, store it as flah data
    if($maxIssuesError){
      $this->flashVar('error',sprintf(__('Issues limit reached (%u)','myHome'),$attMaxIssues));
      return false;
    } else if($maxFilesError){
      $this->flashVar('error',sprintf(__('Files limit reached (%u) for one or more issues','myHome'),self::$MAX_FILES));
      return false;
    }

    //myHome()->log->info("submit: " . $submit . "\n");  
    //myHome()->log->info("skipReviewScreen: " . $skipReviewScreen . "\n");     
    // Return the continue URL (the next view) if the user clicked the "Submit and Continue" button
    if($submit==='continue'){
      // If skipping the review screen, submission should be done here directly, without scheduling any appointment
      // It is better to retrieve $skipReviewScreen from the form parameters than calling skipReviewScreen() here, as it could lead to inconsistencies with $redirectUrlContinue
      if($skipReviewScreen){
        // Submit the maintenance job
        $command=['maintenancejobs',$jobId,'submit'];
        $submitResponse=$api->post($command,[],$authentication,false);

        if($submitResponse===null||!isset($submitResponse->id))
          $this->notifyApiError(__('Job submission failed','myHome'));

        // Invalidate the cached entry for this job, as its state may have changed
        $this->deleteJobDetails($jobId);
      }

      return $redirectUrlContinue;
    }

    $queryVars = [];
    //if($params['myHomeAllowExtraJobs']) $queryVars['allowExtraJobs'] = '';
    //if($params['myHomeAllowAddition']) $queryVars['allowAddition'] = '';
    return $queryVars;//true;
  }

  /**
   * {@inheritDoc}
   */
  protected function doShortcodeMaintenance(array $atts){
    // Store the attributes in the cache
    $this->cachePostAtts($atts); // myHome()->log->info('cachePostAtts: ' . json_encode($atts, JSON_PRETTY_PRINT));

    $attMaxIssues = $this->retrieveAttMaxIssues($atts);  myHome()->log->info('attMaxIssues: ' . $attMaxIssues);
    //myHome()->log->info('attMoreIssues: ' . $);

    // The job ID must be present as a GET parameter
    $jobId=$this->getParam(self::$PARAM_JOB_ID);
    if($jobId===null)
      throw new MyHomeException('Job ID not provided');

    //$allowExtraJobs = isset($_GET['allowExtraJobs']) ? true : false;
    //$allowAddition = isset($_GET['allowAddition']) ? true : false;

    $authentication=myHome()->session->getAuthentication();
    $api=myHome()->api;

    // Retrieve the issues list for this job
    $issuesResponse = $api->get(['maintenancejobs',$jobId,'maintenanceissues'], $authentication, true);
    if($issuesResponse === null)
      throw new MyHomeException('Error while retrieving the maintenance issues');
    //myHome()->log->info("issuesResponse: " . json_encode($issuesResponse));

    $existingIssues = [];
    $issues = [];

    // Build the issues array, with each element composed of:
    // * id: issue ID
    // * title: issue title
    // * description: issue description
    // * files:
    //   - id: file ID
    //   - title: file title
    //   - url: file URL
   // $daysSinceLastIssue = null; // Days since last issue
    //echo myHome()->api->lastHeader;
    //$headerSize = curl_getinfo($this->curl,CURLINFO_HEADER_SIZE);
    //$header = substr($fullResponse,0,$headerSize);
    //echo 'now: ' . json_encode(myHome()->wpDateTime()->setTime(0,0)); 
    //echo json_encode(myHome()->wpDateTime('2017-07-20')->setTime(0,0) -> diff(myHome()->wpDateTime()->setTime(0,0)) -> days); 
    foreach($issuesResponse as $issue) {
      if(empty($issue->id)) continue; // || empty($issue->name) || empty($issue->description)
      //echo '<br/>' . $issue->dateraised;

      $issue->deletable = false;
      $issue->files = [];
      if(!empty($issue->documents))
        foreach($issue->documents as $document) {
          if(empty($document->id) || empty($document->title) || empty($document->url)) continue;
      
          $issue->files[] = [
            'id'=>$document->id,
            'title'=>$document->title,
            'url'=>$document->url
          ];
        }
      
      // Sort issues into editable & read-only
      try { 
        $daysSinceLastIssue = (int) date_diff(myHome()->wpDateTime($issue->dateraised)->setTime(0,0), myHome()->wpDateTime()->setTime(0,0))->format("%r%a"); //  myHome()->wpDateTime($issue->dateraised)->setTime(0,0) -> diff(myHome()->wpDateTime()->setTime(0,0)) -> days; 
        // myHome()->log->info('issueRaised: ' . json_encode($daysSinceLastIssue));
        //echo json_encode(myHome()->wpDateTime($issueResponse->dateraised) -> diff(myHome()->wpDateTime()) -> format('i')); 
      } catch(Exception $e) { throw new MyHomeException('Wrong dateraised date: ' . $issue->dateraised); }
      //echo ' diff: ' . $daysSinceLastIssue . '<br/><br/>';
      //echo json_encode($issue);
      if($daysSinceLastIssue >= 1) 
        $existingIssues[] = $issue;
      else 
        $issues[] = $issue;
    }
    //echo json_encode($existingIssues);
    //echo json_encode($issues);
    $issues = array_reverse($issues);

    $addIssue = $this->getParam('myHomeAddIssue');
    $addIssue = !empty($addIssue);

    $skipReviewScreen = $this->skipReviewScreen();

    $redirectParams=[self::$PARAM_JOB_ID=>$jobId];
    $redirectUrlContinue=$this->maintenancePagePermalink($skipReviewScreen?'confirmed':'review'); // Submit and Continue
    $redirectUrlContinue=add_query_arg($redirectParams,$redirectUrlContinue); // Both screens need the job ID parameter
    
    // myHome()->log->info('lastIssueRaised: ' . json_encode($daysSinceLastIssue) . ' days ago. Max days since last issue is: '); // limit is since job created not lastIssue
    $redirectParams['myHomeAddIssue'] = true;
    $redirectUrl=$this->maintenancePagePermalink('issues'); // Submit and Add More
    $redirectUrl=add_query_arg($redirectParams,$redirectUrl);

    $redirectUrlError=$redirectUrl; // If an error occurs, try to add a new issue as well

    $paramJobId=static::$PARAM_JOB_ID;
    $paramPostId=static::$PARAM_POST_ID;
    $maxFiles=static::$MAX_FILES;

    // Values passed to the view:
    // * attMaxIssues: maximum number of issues allowed (0 for no limit)
    // * jobId: current job ID
    // * issues: issues array
    // * addIssue: whether a new issue is to be appended to the form, if there is room for more
    // * skipReviewScreen: whether the Review screen should be skipped (when there is no page set for that screen)
    // * redirectUrl: URL to redirect to on success (when clicking "Submit and Add More")
    // * redirectUrlError: URL to redirect to on error
    // * redirectUrlContinue: URL to redirect to on success (when clicking "Submit and Continue")
    // * paramJobId: parameter name for the job ID
    // * paramPostId: parameter name for the post ID of the current page
    // * maxFiles: maximum number of files per issue
    $this->loadView('shortcodeMaintenanceIssues','MyHomeShortcodes',
      compact('attMaxIssues','jobId','existingIssues','issues','addIssue','skipReviewScreen','redirectUrl','redirectUrlError','redirectUrlContinue','paramJobId','paramPostId','maxFiles')); //,'allowExtraJobs','allowAddition'));
  }

  /**
   * Retrieves the maximum issues shortcode attribute (maxissues) from a given attributes array
   *
   * @param string[] $atts shortcode attributes
   * @return int the maximum number of issues allowed (0 for no limit)
   */
  private function retrieveAttMaxIssues(array $atts){
    $atts=shortcode_atts(['maxissues'=>'0'],$atts);

    $attMaxIssues=$atts['maxissues'];

    if($attMaxIssues<0){
      myHome()->handleError('Wrong Maximum Issues attribute: '.$attMaxIssues);
      $attMaxIssues=0;
    }

    return (int)$attMaxIssues;
  }

  /**
   * Checks whether the review screen should be skipped
   *
   * @return bool whether the review screen should be skipped
   */
  private function skipReviewScreen(){
    return !$this->isPageSet('review');
  }

  /**
   * Checks whether a file is a valid image file based on its name
   *
   * @param string $filename the filename
   * @return bool whether the file is valid or not
   */
  private function verifyImageFileType($filename){
    $extension=pathinfo($filename,PATHINFO_EXTENSION);
    $imageExtensions=['jpg',
      'jpeg',
      'png'];

    return in_array(strtolower($extension),$imageExtensions);
  }
}
