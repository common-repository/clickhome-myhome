<?php
/**
 * The notes view
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
 * @var int                      $attLimit
 * @var bool                     $attHideNew
 * @var string[]                 $attHideFields
 * @var bool                     $attShowDocuments
 * @var mixed[]                  $notes
 */

// Remove (empty) hidden fields
foreach($attHideFields as $field)
  $notes=array_map(function (array $note) use ($field){
    $note[$field]='';

    return $note;
  },$notes);
?>


<div class="mh-wrapper-notes">
  <?php if(!$attHideNew): ?>
    <div class="mh-note mh-new-note">
      <?php $this->loadView(['shortcodeNotes','compose'], 'MyHomeShortcodes', compact('attTitle','attPreSubjects','preSubjects','attShowDocuments')); ?>
    </div>
	<?php endif; ?>
	<div class="mh-notes-list" id="divMyHomeNotesList">
		<?php
		  foreach($notes as $note) // Same effect, but more efficient: include __DIR__.'/shortcodeNotes.note.php';
		    $this->loadView(['shortcodeNotes','note'], 'MyHomeShortcodes', compact('note','attHideNew','attPreSubjects','preSubjects','attShowDocuments'));
		?>
	</div>
</div>

<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeNotes.js" type="text/javascript"></script>
<script>
  jQuery(function ($) {
    mh.notes.vars.attPreSubjects = <?php echo $attPreSubjects ? 'true' : 'false'; ?>;
    mh.notes.init();
  });
</script>
