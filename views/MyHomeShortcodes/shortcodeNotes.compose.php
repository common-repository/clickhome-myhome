<?php
/**
 * The notes.compose/reply subview
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

/**
 * @var ShortcodeNotesController $this
 * @var string[]                 $note
 */
?>
<form class="mh-compose <?php if(isset($note->noteid)) echo 'mh-show' ?>" method="post" action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php" enctype="multipart/form-data">
  <div class="mh-note-title">
    <?php if(isset($note->noteid)): ?>
      Reply
    <?php else: ?>
      <?php echo $attTitle ?>
    <?php endif; ?>
  </div>
  <div style="display: none;">
    <?php wp_nonce_field('myhome-nonce-client', 'myHomeNonce', false) ?>
    <input type="hidden" name="action" value="myhome">
    <input type="hidden" name="myHomeAction" value="notes">
    <input type="hidden" name="myHomeRedirect" value="<?php echo get_permalink(); ?>">
    <input type="hidden" name="myHomeReplyToId" value="<?php echo isset($note->noteid) ? $note->noteid : null ?>">
  </div>
	<div class="mh-note-subject">
    <?php if(!isset($note->noteid)): ?>
      <?php if($attPreSubjects): ?>
        <select name="myHomeSubject">
          <option>Choose your subject...</option>
          <?php foreach($preSubjects as $subject): ?>
            <option><?php echo $subject; ?></option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
		    <input name="myHomeSubject" maxlength="250" placeholder="<?php _e(!isset($note) ? 'Type here to start a new note...' : 'Reply...', 'myHome'); ?>" type="text" />
	    <?php endif; ?>
    <?php else: ?> <?php // echo '[hidden] RE: ' . $note->subject ?>
      <input name="myHomeSubject" maxlength="250" type="hidden" value="RE: <?php echo $note->subject ?>" />
	  <?php endif; ?>
	  <div class="mh-error">Please enter your subject</div>
  </div>
	<div class="mh-note-body">
		<textarea name="myHomeBody" maxlength="10000" <?php if(!isset($note->noteid)) echo 'disabled' ?>></textarea>
	  <div class="mh-error">Please enter your message</div>
	</div>
	<div class="mh-actions mh-clearfix">
    <?php if($attShowDocuments): ?>
      <input type="file" name="myHomeDocument" class="pull-left" disabled />
    <?php endif; ?>
    <!--<button class="mh-button mh-button-new-note-cancel" id="buttonMyHomeNewNoteCancel" type="button"><?php _ex('Cancel','New Note','myHome'); ?></button>
		<button class="mh-button mh-button-new-note-ok" id="buttonMyHomeNewNoteOk" type="button"><?php _ex('OK', 'New Note','myHome'); ?></button> -->
		<button class="mh-button" type="submit" disabled><?php _ex('OK', 'New Note','myHome'); ?></button>
	</div>
	<div class="mh-loading"></div>
</form>