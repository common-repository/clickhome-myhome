<?php
/**
 * The documents view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeDocumentsController))
  die;

/**
 * @var ShortcodeDocumentsController $this
 * @var string[]                     $attExclude
 * @var mixed[]                      $documents
 */

$documents=array_filter($documents,function (array $document) use ($attExclude){
  return !in_array($document['type'],$attExclude);
});
?>
<div class="mh-wrapper mh-wrapper-documents">
  <?php if($documents): ?>
    <?php foreach($documents as $document): ?>
      <div class="mh-block mh-block-documents documents-type-<?php echo strtolower($document['type']); ?>">
        <a class="mh-row mh-row-documents-icon" href="<?php echo $this->documentDownloadUrl($document['url']); ?>">&nbsp;</a>
        <div class="mh-row mh-row-documents-title">
          <a class="mh-link mh-link-documents-title" href="<?php echo $this->documentDownloadUrl($document['url']); ?>">
            <?php echo esc_html($document['title']); ?>
          </a><br/><br/>
  <!-- </div>
        <div class="mh-row mh-row-documents-date"> -->
          <?php echo esc_html($document['date']); ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="mh-no-results">No documents to display.</div>
  <?php endif; ?>
</div>
