<?php
/**
 * The tenderOverviewVariations view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.5.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderOverviewVariationsController))
  die;

/**
 * @var ShortcodeTenderOverviewVariationsController $this
 * @var string[][]                        $tender
 * @var string                            $atts
 */

?>

<?php if(count($tender->variations) > 0): ?>
  <!-- Variations -->
  <div class="mh-card mh-tender-section mh-section-tender-overview-variations">
    <i class="fa fa-cubes"></i>
    <h2><?php _e('Variations','myHome'); ?></h2>
    <div class="mh-row mh-tender-options-description"><?php echo $atts['content']; ?></div>

	  <!--<div class="mh-tender-option-isopen <?php //echo($tender->isVariationsClientEditable ? 'is-open' : 'is-closed') ?> pull-left">
      <?php //if($tender->isVariationsClientEditable): ?>
        <i class="fa fa-play"></i>
        <h4>Variations are open!</h4>
      <?php //else: ?>
        <i class="fa fa-info"></i>
        <h4>Variations are currently locked.</h4>
      <?php //endif; ?>
	  </div>-->

    <?php if(isset($tender->urls->variations)): ?>
      <div class="pull-right">
        <span class="mh-button-wrapper">
          <!--<?php //if($tender->isVariationsClientEditable): ?>
            <a class="mh-button" href="<?php //echo esc_attr($tender->urls->variations); ?>"><?php //_e('Choose Variations', 'myHome'); ?></a>
          <?php //else: ?>-->
            <a class="mh-button" href="<?php echo esc_attr($tender->urls->variations); ?>"><?php _e('Review Variations', 'myHome'); ?></a>
          <!--<?php //endif; ?>-->
        </span>
      </div>
    <?php endif; ?>
    <div class="mh-clearfix"></div>
  </div>
<?php endif; ?>