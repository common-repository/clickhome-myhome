<?php
/**
 * The notes.note subview
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeNotesController))
  die;

 $topNote = $note;
/**
 * @var ShortcodeNotesController $this
 * @var string[]                 $note
 */
?>
<div id="note-<?php echo esc_html($note->noteid); ?>" class="mh-note">
	<div class="mh-note-subject"><?php echo esc_html($note->subject); ?></div>
	<div class="mh-note-date"><?php echo esc_html($note->authorname) . ' on ' . esc_html($note->notedate); ?></div>
	<div class="mh-note-body"><?php echo nl2br(esc_html($note->body)); ?></div>

  <!-- Documents -->
	<?php if(isset($note->documents) && $note->documents): ?>
	<div class="mh-note-documents">
    <i class="fa fa-paperclip"></i> Attached Files 
    <div>
      <?php foreach($note->documents as $index => $document): ?> <!-- echo $documentId . ', '; ?> -->
        <a href="<?php echo $this->photoDownloadUrl($document->url); ?>" title="<?php echo $document->title ?>" target="_blank" rel="noopener nofollow">
          <?php if($document->hasThumbnail): ?>
            <div class="mh-thumb" style="background-image: url(<?php echo $this->photoDownloadUrl($document->thumbnailUrl) ?>);"></div>
          <?php else: ?>
            <div class="mh-thumb"><?php echo substr($document->fileExtension, 1) ?></div>
          <?php endif; ?>
          <p><?php echo $document->title; ?></p>
        </a>
        <?php //if($index < count($note->documents)-1) echo ', '; ?>
      <?php endforeach; ?>
    </div>
  </div>
	<?php endif; ?>

  <!-- Replies -->
	<?php if(isset($note->replies)): 
		foreach($note->replies as $note)
		  $this->loadView(['shortcodeNotes','note'], 'MyHomeShortcodes', compact('note','attHideNew','attPreSubjects','preSubjects','attShowDocuments'));
    $note = $topNote; // Re-assign $note to parentNote to prevent RE: RE:
  endif; ?>

  <?php if(!$attHideNew): ?>
    <div class="mh-note-reply-btn"><a href="javascript:void(0);">reply</a></div>
  <?php endif; ?>
  
  <!-- Compose -->
	<?php if(!$attHideNew): ?>
    <div class="mh-note-reply">
      <!-- <a class="mh-reply" onclick="mh.notes.openReply(<?php echo esc_html($note->noteid); ?>)">Reply</a> -->
      <?php $this->loadView(['shortcodeNotes','compose'], 'MyHomeShortcodes', compact('note','attPreSubjects','preSubjects','attShowDocuments')); ?>
    </div>
  <?php endif; ?>
</div>
