<?php
/**
 * The display view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeDisplayController))
  die;

/**
 * @var ShortcodeDisplayController $this
 * @var MyHomeDisplay              $display
 * @var MyHomeHouseType            $houseType
 */

$houseTypePage=$houseType->findPage();

if($houseTypePage)
  $houseTypeUrl=get_permalink($houseTypePage);
else
  $houseTypeUrl='#';
?>
<div class="mh-wrapper mh-wrapper-display">
  <div class="mh-section mh-section-display-map" id="divMyHomeDisplayMap"></div>

  <div class="mh-section mh-section-display-images-description-wrapper">
    <div class="mh-section mh-section-display-images">
      <div class="mh-block mh-block-display-images carousel" id="divMyHomeDisplayImages">
        <?php foreach($houseType->housedocs as $doc): //var_dump($doc); ?>
          <?php $attachment=myHome()->advertising->docFindAttachment($doc->url); ?>
          <?php if($attachment): ?>
            <?php
              $imageSrc=wp_get_attachment_image_src($attachment->ID,[150,150]);
            ?>
            <div><img class="mh-image mh-image-display-image" src="<?php echo esc_url($imageSrc[0]); ?>"></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <div class="mh-row mh-row-display-images-title">
        <div class="mh-cell mh-cell-display-images-name">
          <a class="mh-link mh-link-display-house-type" href="<?php echo esc_url($houseTypeUrl); ?>">
            <?php echo esc_html($houseType->housename); ?>
          </a>
        </div>
        <div class="mh-cell mh-cell-display-images-size"><?php echo esc_html($houseType->size); ?></div>
      </div>
      <div class="mh-row mh-row-display-images-features">
        <div class="mh-cell mh-cell-display-images-bedrooms" title="<?php _ex('Bedrooms','Display','myHome'); ?>"><span
            class="mh-icon">&nbsp;</span> <?php echo (int)$houseType->bedqty; ?></div>
        <div class="mh-cell mh-cell-display-images-bathrooms" title="<?php _ex('Bathrooms','Display','myHome'); ?>">
          <span class="mh-icon">&nbsp;</span> <?php echo (float)$houseType->bathqty; ?></div>
        <div class="mh-cell mh-cell-display-images-garages" title="<?php _ex('Carparks','Display','myHome'); ?>"><span
            class="mh-icon">&nbsp;</span> <?php echo (int)$houseType->garageqty; ?></div>
        <?php if($houseType->hasTheatreRoom()): ?>
          <div class="mh-cell mh-cell-display-images-theatre" title="<?php _ex('Theatre','Display','myHome'); ?>"><span
              class="mh-icon">&nbsp;</span></div>
        <?php endif; ?>
        <?php if($houseType->hasStudyRoom()): ?>
          <div class="mh-cell mh-cell-display-images-study" title="<?php _ex('Study','Display','myHome'); ?>"><span
              class="mh-icon">&nbsp;</span></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="mh-section mh-section-display-description">
      <?php if($display->opentimessimple): ?>
        <div class="mh-block mh-block-display-opening-hours">
          <div class="mh-header mh-header-display-opening-hours"><?php _e('Opening Hours:','myHome'); ?></div>
          <div
            class="mh-body mh-body-display-opening-hours"><?php echo nl2br(esc_html($display->opentimessimple)); ?></div>
        </div>
      <?php endif; ?>
      <?php if($display->address||$display->salesperson): ?>
        <div class="mh-block mh-block-display-address-contact">
          <?php if($display->address): ?>
            <div class="mh-header mh-header-display-address"><?php _ex('Address:','Display','myHome'); ?></div>
            <div class="mh-body mh-body-display-address"><?php echo nl2br(esc_html($display->address)); ?></div>
          <?php endif; ?>
          <?php if($display->salesperson): ?>
            <div class="mh-header mh-header-display-contact"><?php _ex('Contact:','Display','myHome'); ?></div>
            <div class="mh-body mh-body-display-contact"><?php echo nl2br(esc_html($display->salesperson."\n".
                $display->phone1)); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div class="mh-block mh-block-display-description"><?php echo nl2br(esc_html($houseType->description)); ?></div>
    </div>
  </div>
</div>


<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeDisplayHome.js" type="text/javascript"></script>
<script type="text/javascript">
  jQuery(function ($) {
    mh.displayHome.address = '<?php echo str_replace(["\n","\r"],' ', $display->address); ?>';
    mh.displayHome.init();
  });
</script>
