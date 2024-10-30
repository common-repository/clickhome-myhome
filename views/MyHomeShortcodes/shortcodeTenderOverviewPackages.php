<?php
/**
 * The tenderOverviewPackages view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.5.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderOverviewPackagesController))
  die;

/**
 * @var ShortcodeTenderOverviewPackagesController $this
 * @var string[][]                        $tender
 * @var string                            $atts
 */

//var_dump($content);
//echo '</br></br>';
//var_dump($details);
?>

<!-- Packages -->
<div class="mh-card mh-tender-section mh-section-tender-overview-packages">
  <i class="fa fa-cube"></i>
  <h2><?php _e('Packages','myHome'); ?></h2>
  <div class="mh-row mh-tender-options-description"><?php echo $atts['content']; ?></div>

	<div class="mh-tender-status mh-tender-option-isopen <?php echo($tender->isPackagesClientEditable ? 'is-open' : 'is-closed') ?> pull-left">
    <?php if($tender->isPackagesClientEditable): ?>
      <i class="fa fa-play"></i>
      <h4>Packages are open!</h4>
    <?php else: ?>
      <i class="fa fa-lock"></i>
      <h4>Packages are currently locked.</h4>
    <?php endif; ?>
	</div>

  <?php if(isset($tender->urls->packages)): ?>
    <div class="pull-right">
      <span class="mh-button-wrapper">
        <?php if($tender->isPackagesClientEditable): ?>
          <a class="mh-button" href="<?php echo esc_attr($tender->urls->packages); ?>"><?php _e('Choose Packages', 'myHome'); ?></a>
        <?php else: ?>
          <a class="mh-button" href="<?php echo esc_attr($tender->urls->packages); ?>"><?php _e('Review Packages', 'myHome'); ?></a>
        <?php endif; ?>
      </span>
    </div>
  <?php endif; ?>
  <div class="mh-clearfix"></div>
</div>