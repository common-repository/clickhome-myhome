<?php
/**
 * The stories view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeStoriesController))
  die;

/**
 * @var ShortcodeStoriesController $this
 * @var string                     $attMode
 * @var mixed[]                    $stories
 */

$sortedStories=[];
for($i=0;$i<3;$i++)
  $sortedStories=array_merge($sortedStories,$stories[$attMode[$i]]);
?>
<div class="mh-wrapper mh-wrapper-stories">
  <?php foreach($sortedStories as $story): ?>
    <div class="mh-block mh-block-stories">
      <div class="mh-row mh-row-stories-name"><?php echo esc_html($story['name']); ?></div>
      <div class="mh-row mh-row-stories-story"><?php echo nl2br(esc_html($story['story'])); ?></div>
    </div>
  <?php endforeach; ?>
</div>
