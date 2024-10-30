<?php
/**
 * The progress view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeProgressController))
  die;

/**
 * @var ShortcodeProgressController $this
 * @var string                      $attMode
 * @var mixed[]                     $progress
 */

// The vertical mode begins in the bottom
if($attMode==='vertical')
  $progress=array_reverse($progress);
?>
<div class="mh-wrapper mh-wrapper-progress progress-layout-<?php echo $attMode; ?>">
  <div class="mh-body mh-body-progress">
    <?php foreach($progress as $task): ?>
      <?php
		$phaseCodeClass=' mh-progress-code-'.strtolower(sanitize_html_class($task['phaseCode']));
		$statusClass=' mh-status-'.strtolower(sanitize_html_class($task['status']));
      ?>
      <div class="mh-block mh-block-progress<?php echo $phaseCodeClass . $statusClass; ?>">
        <div class="mh-row mh-row-progress-status"></div>
        <div class="mh-row mh-row-progress-icon"></div>
        <div class="mh-row mh-row-progress-name">
          <?php echo esc_html($task['name']); ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
