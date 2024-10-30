<?php
/**
 * The tenderOverviewSelections view
 *
 * @selection    MyHome
 * @subselection ViewsShortcodes
 * @since      1.5.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderOverviewSelectionsController))
  die;

/**
 * @var ShortcodeTenderOverviewSelectionsController $this
 * @var string[][]                        $tender
 * @var string                            $atts
 */

?>

<!-- Selections -->
<div class="mh-card mh-tender-section mh-section-tender-overview-selections">
  <i class="fa fa-random"></i>
  <h2><?php _e('Selections','myHome'); ?></h2>
  <div class="mh-row mh-tender-options-description"><?php echo $atts['content']; ?></div>

	<div class="mh-tender-status mh-tender-option-isopen <?php echo($tender->isSelectionsClientEditable ? 'is-open' : 'is-closed') ?> pull-left">
    <?php if($tender->isSelectionsClientEditable): ?>
      <i class="fa fa-play"></i>
      <h4>Selections are open!</h4>
    <?php else: ?>
      <i class="fa fa-lock"></i>
      <h4>Selections are currently locked.</h4>
    <?php endif; ?>
	</div>

  <?php if(isset($tender->urls->selections)): ?>
    <div class="pull-right">
      <span class="mh-button-wrapper">
        <?php if($tender->isSelectionsClientEditable): ?>
          <a class="mh-button" href="<?php echo esc_attr($tender->urls->selections); ?>"><?php _e('Make Selections', 'myHome'); ?></a>
        <?php else: ?>
          <a class="mh-button" href="<?php echo esc_attr($tender->urls->selections); ?>"><?php _e('Review Selections', 'myHome'); ?></a>
        <?php endif; ?>
      </span>
    </div>
  <?php endif; ?>
  <div class="mh-clearfix"></div>
</div>

