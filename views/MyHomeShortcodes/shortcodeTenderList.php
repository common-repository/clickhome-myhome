<?php
/**
 * The tenderList view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderListController))
  die;

/**
 * @var ShortcodeTenderListController $this
 * @var string                        $attIncludeNonActive
 * @var string[][]                    $tenders
 */

//if($attIncludeNonActive==='true')
  $filteredTenders=$tenders;
//else
//  $filteredTenders=array_filter($tenders,function(array $tender){
//    return !$tender->expired;
//  });
?>
<div class="mh-wrapper mh-wrapper-tender-list">
  <?php foreach($filteredTenders as $tender): ?>
    <?php //var_dump($tender); ?>
    <div class="mh-block-tender">
      <?php if($tender->thumbnailUrl): ?>
        <a href="<?php echo esc_url($tender->overviewUrl); ?>" class="mh-section mh-section-tender-list-tender-image" style="background-image: url(<?php echo esc_url($tender->thumbnailUrl); ?>);">
          <!--<img class="mh-image mh-image-tender-list-tender-thumbnail" src="<?php echo esc_url($tender->thumbnailUrl); ?>" />-->
        </a>
      <?php endif; ?>
      <div class="mh-section mh-section-tender-list-tender-text">
        <div class="mh-row mh-row-tender-list-tender-title-status">
          <div class="mh-cell mh-cell-tender-list-tender-title">
            <?php if($tender->overviewUrl): ?>
              <a class="mh-link mh-link-tender-list-tender-overview" href="<?php echo esc_url($tender->overviewUrl); ?>"><?php echo esc_html($tender->title); ?></a>
            <?php else: ?>
              <span class="mh-link-tender-list-tender-overview" href="<?php echo esc_url($tender->overviewUrl); ?>"><?php echo esc_html($tender->title); ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="mh-row mh-row-tender-list-tender-house-type">
          <?php if($tender->houseTypeUrl): ?>
            <a class="mh-link mh-link-tender-list-tender-house-type" href="<?php echo esc_url($tender->houseTypeUrl); ?>">
              <?php printf($tender->houseType); ?>
            </a>
          <?php else: ?>
            <?php printf($tender->houseType); ?>
          <?php endif; ?>
        </div>

        <div class="mh-row mh-row-tender-list-tender-dates">
          <div class="mh-cell mh-cell-tender-list-tender-created"><?php printf(__('Created on: %s','myHome'),
              $tender->createDate); ?></div>
          <!-- <div class="mh-cell mh-cell-tender-list-tender-expires"><?php printf(__('Expires on: %s','myHome'),
              $tender->expiryDate); ?></div> -->
        </div>

        <?php //echo($tender->statusId); ?>
      </div>
      <div class="mh-section mh-section-tender-list-tender-text text-right">
        <?php if($attShowRunningPrices=='true'): ?>
          <div class="mh-cell mh-cell-tender-list-tender-title">
            <?php echo $tender->price; ?>
          </div>
        <?php endif; ?>

        <div class="mh-cell mh-cell-tender-list-tender-status">
          <?php //var_dump($tender); ?>
          <?php /* if($tender->selectionUrl): ?>
            <?php if($tender->selectionsOpen): ?>
              <span class="mh-button-wrapper mh-button-block mh-button-tender-selections-edit"><a class="mh-button" href="<?php echo esc_attr($tender->selectionUrl); ?>"><?php _e('Edit','myHome'); ?></a></span>
            <?php else:*/ ?>
          <?php  if(!empty($tender->overviewUrl)): ?>
              <span class="mh-button-wrapper mh-button-block"><a class="mh-button" href="<?php echo esc_url($tender->overviewUrl); ?>"><?php _e('View Overview','myHome'); ?></a></span>
            <?php endif; ?>
          <?php /*endif;*/ ?>

          <!--<?php if($tender->selectionsOpen): ?>
            Selections are now open!
          <?php else: ?>
            Selections closed
          <?php endif; ?>-->
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
