<?php
/**
 * The maintenanceIssues view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeMaintenanceIssuesController))
  die;

/**
 * @var ShortcodeMaintenanceIssuesController $this
 * @var int                                  $attMaxIssues
 * @var int                                  $jobId
 * @var mixed[]                              $issues
 * @var bool                                 $addIssue
 * @var bool                                 $skipReviewScreen
 * @var string                               $redirectUrl
 * @var string                               $redirectUrlError
 * @var string                               $redirectUrlContinue
 * @var string                               $paramJobId
 * @var string                               $paramPostId
 * @var int                                  $maxFiles
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('maintenanceIssues','POST',$redirectUrl,$redirectUrlError);
$formAttributes['params']['myHomeRedirectUrlContinue']=$redirectUrlContinue;
$formAttributes['params'][$paramJobId]=$jobId;
// Post ID is used by doPostMaintenance() to retrieve the cached attributes for this post, and then, check how many issues is the user allow to submit
$formAttributes['params'][$paramPostId]=get_the_ID();
//$formAttributes['params']['myHomeAllowExtraJobs'] = $allowExtraJobs;
//$formAttributes['params']['myHomeAllowAddition'] = $allowAddition;

$error=$this->restoreVar('error');

$moreIssuesAllowed = !($attMaxIssues&&count($issues)>=$attMaxIssues);

if(!$moreIssuesAllowed)
  $issues=array_slice($issues,0,$attMaxIssues,true);
/*else if(!$issues||$addIssue)
  $issues[] = [
    'id'=>'-1', // Negative IDs represent new issues
    'title'=>'',
    'description'=>'',
    'deletable'=>$issues&&$addIssue, // Mark as deletable only if it's not the first issue
    'files'=>[]
  ];*/
?>
<form action="<?php $this->appendFormUrl($formAttributes); ?>" class="mh-wrapper mh-wrapper-maintenance-issues" enctype="multipart/form-data" method="POST">
  <?php $this->appendFormParams($formAttributes,2); ?>

  <div class="mh-body mh-section mh-section-maintenance-issues-form">
    <div class="mh-wrapper-notes margin-top-30">
      <!-- Compose -->
      <h3>Create a New Issue</h3><br/>
      <div class="mh-note mh-new-note">
        <input name="myHomeIssues[-1][existingFiles]" type="hidden">
        <div class="mh-compose mh-show">
          <!-- <div class="mh-note-title">Create a New Issue</div> -->
	        <div class="mh-note-subject">
		        <input maxlength="100" name="myHomeIssues[-1][title]" placeholder="<?php _e('Type a subject...', 'myHome'); ?>" type="text" />
	          <div class="mh-error">Please enter your subject</div>
          </div>
	        <div class="mh-note-body">
		        <textarea maxlength="5000" name="myHomeIssues[-1][description]"></textarea>
	          <div class="mh-error">Please enter your message</div>
	        </div>
          <div class="mh-cell mh-cell-maintenance-issues-input">
            <div class="mh-maintenance-issues-file-base">
              <a class="mh-link mh-link-maintenance-issues-delete-file" href="javascript:void(0);"
                title="<?php _e('Delete','myHome'); ?>">&times;</a>
            <span class="mh-button mh-button-maintenance-issues-select-file">
              <span><?php _e('Select File...','myHome'); ?></span>
              <input data-name="myHomeIssues[-1][files][]" type="file">
            </span>
              <span class="mh-file-name"></span>
            </div>
            <div class="mh-maintenance-issues-files-new"></div>
            <a class="mh-link mh-link-maintenance-issues-add-file" href="javascript:void(0);"><?php _e('Add More Files','myHome'); ?></a>
          </div>
          <!-- Submit -->
          <div class="mh-footer mh-footer-maintenance-issues margin-top-30">
            <div class="mh-row mh-row-maintenance-issues-buttons">
              <div class="mh-cell mh-cell-maintenance-issues-buttons">
                <?php if($moreIssuesAllowed): ?>
                  <button class="mh-button mh-button-maintenance-issues-submit" name="myHomeSubmit" type="submit"
                    value="addIssue"><?php _ex('Submit &amp; Add More','myHome'); ?></button>
                <?php endif; ?>
                <button class="mh-button mh-button-maintenance-issues-submit" name="myHomeSubmit" type="submit"
                  value="continue"><?php _ex('Submit &amp; Continue','myHome'); ?></button>
              </div>
            </div>
          </div>
	      </div>
	    </div>

	    <div class="margin-top-30">
        <!-- New Issues -->
        <?php if($issues): ?>
          <div class="mh-wrapper-notes">
            <div class="mh-notes-list margin-top-30">
              <h3>Current Issues</h3><br/>
              <?php if($error): ?>
                <div class="mh-error mh-error-maintenance-issues"><?php echo esc_html($error); ?></div>
              <?php endif; ?>
              <div class="mh-body mh-body-maintenance-issues">
                <?php foreach($issues as $issue): ?>
                  <div class="mh-note">
                    <div class="mh-note-subject"><?php echo esc_html($issue->name); ?></div>
                    <div class="mh-note-date"><?php echo $this->dateString(new DateTime($issue->dateraised)); ?></div>
                    <div class="mh-note-body"><?php echo nl2br(esc_html($issue->description)); ?></div>
                    <!-- Documents -->
                    <?php if(isset($issue->documents) && $issue->documents): ?>
                      <div class="mh-note-documents">
                        <i class="fa fa-paperclip"></i> Attached Files 
                        <div>
                          <?php foreach($issue->documents as $index => $document): ?> <!-- echo $documentId . ', '; ?> -->
                            <a href="<?php echo $this->photoDownloadUrl($document->url); ?>" title="<?php echo $document->title ?>" target="_blank" rel="noopener nofollow">
                              <div class="mh-thumb" style="background-image: url(<?php echo $this->photoDownloadUrl($document->url, true) ?>);"></div>
                              <p><?php echo $document->title; ?></p>
                            </a>
                            <?php //if($index < count($note->documents)-1) echo ', '; ?>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                  <?php /*
                    // Used to know which images have been deleted
                    $filesIds = array_map(function (array $file){
                      return $file['id'];
                    }, $issue->files);
                  ?>
                  <div class="mh-note mh-new-note">
                    <div class="mh-block mh-block-maintenance-issues mh-compose">
                      <?php if($issue->deletable): ?>
                        <div class="mh-row mh-row-maintenance-issues-delete">
                          <div class="mh-cell mh-cell-maintenance-issues-delete">
                            <a class="mh-link mh-link-maintenance-issues-delete"
                              href="javascript:void(0);"><?php _e('Delete this issue','myHome'); ?></a>
                          </div>
                        </div>
                      <?php endif; ?>
                      <input name="myHomeIssues[<?php echo $issue->id; ?>][existingFiles]" type="hidden" value="<?php echo implode(',',$filesIds); ?>">

                      <div class="mh-row mh-row-maintenance-issues-title">
                        <div class="mh-cell mh-cell-maintenance-issues-input">
                          <input maxlength="100" name="myHomeIssues[<?php echo $issue->id; ?>][title]" required type="text" value="<?php echo esc_attr($issue->name); ?>">
                        </div>
                      </div>
                      <div class="mh-row mh-row-maintenance-issues-description">
                        <div class="mh-cell mh-cell-maintenance-issues-input"><textarea maxlength="5000"
                            name="myHomeIssues[<?php echo $issue->id; ?>][description]" required
                            rows="5"><?php echo esc_html($issue->description); ?></textarea></div>
                      </div>
                      <div class="mh-row mh-row-maintenance-issues-files">
                        <div class="mh-cell mh-cell-maintenance-issues-input">
                          <div class="mh-maintenance-issues-file-base">
                            <a class="mh-link mh-link-maintenance-issues-delete-file" href="javascript:void(0);"
                              title="<?php _e('Delete','myHome'); ?>">&times;</a>
                          <span class="mh-button mh-button-maintenance-issues-select-file">
                            <span><?php _e('Select File...','myHome'); ?></span>
                            <input data-name="myHomeIssues[<?php echo $issue->id; ?>][files][]" type="file">
                          </span>
                            <span class="mh-file-name"></span>
                          </div>
                          <div class="mh-maintenance-issues-files-existing">
                            <?php foreach($issue->files as $file): ?>
                              <div class="mh-maintenance-issues-file">
                                <input name="myHomeIssues[<?php echo $issue->id; ?>][files][<?php echo $file['id']; ?>]"
                                  type="hidden" value="true">
                                <?php if(false): // Not working yet - skip ?>
                                  <a class="mh-link mh-link-maintenance-issues-delete-file" href="javascript:void(0);" title="<?php _e('Delete','myHome'); ?>">&times;</a>
                                <?php endif; ?>
                                <a class="mh-maintenance-issues-thumbnail-link"
                                  href="<?php echo $this->photoDownloadUrl($file['url'], false, true, 'client'); ?>" target="_blank"><img
                                    class="mh-image mh-image-maintenance-issues-thumbnail"
                                    src="<?php echo $this->photoDownloadUrl($file['url'], true, true, 'client'); ?>"></a>
                                <span class="mh-file-name"><?php echo $file['title']; ?></span>
                              </div>
                            <?php endforeach; ?>
                          </div>
                          <div class="mh-maintenance-issues-files-new"></div>
                          <a class="mh-link mh-link-maintenance-issues-add-file" href="javascript:void(0);"><?php _e('Add More Files','myHome'); ?></a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php */ ?>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
	    </div>
    </div>

    <!-- Existing (read-only) -->
    <?php if($existingIssues): ?>
      <div class="mh-wrapper-notes">
	      <div class="mh-notes-list margin-top-30">
          <h3>Existing Issues</h3><br/>
          <?php foreach($existingIssues as $issue): ?>
            <div class="mh-note">
	            <div class="mh-note-subject"><?php echo esc_html($issue->name); ?></div>
	            <div class="mh-note-date"><?php echo $this->dateString(new DateTime($issue->dateraised)); ?></div>
	            <div class="mh-note-body"><?php echo nl2br(esc_html($issue->description)); ?></div>
              <!-- Documents -->
              <?php if(isset($issue->documents) && $issue->documents): ?>
                <div class="mh-note-documents">
                  <i class="fa fa-paperclip"></i> Attached Files 
                  <div>
                    <?php foreach($issue->documents as $index => $document): ?> <!-- echo $documentId . ', '; ?> -->
                      <a href="<?php echo $this->photoDownloadUrl($document->url); ?>" title="<?php echo $document->title ?>" target="_blank" rel="noopener nofollow">
                        <div class="mh-thumb" style="background-image: url(<?php echo $this->photoDownloadUrl($document->url, true) ?>);"></div>
                        <p><?php echo $document->title; ?></p>
                      </a>
                      <?php //if($index < count($note->documents)-1) echo ', '; ?>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
	      </div>
      </div>
    <?php endif; ?>
  </div>
  <div class="mh-loading mh-loading-maintenance-issues-image" id="divMyHomeLoadingMaintenanceIssues"></div>
</form>

<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeMaintenanceIssues.js" type="text/javascript"></script>
<script type="text/javascript">
  jQuery(function($){
    _.extend(mh.maintenance.issues, {
      maxFiles: <?php echo $maxFiles; ?>
    });
  });
</script>
