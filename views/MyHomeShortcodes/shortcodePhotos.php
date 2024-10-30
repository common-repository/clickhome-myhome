<?php
/**
 * The photos view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodePhotosController))
  die;

/**
 * @var ShortcodePhotosController $this
 * @var int                       $attColumns
 * @var mixed[]                   $photos
 * @var string                    $attFacebook
 * @var string                    $facebookAppId
 * @var string                    $facebookError
 */

$facebook=$attFacebook!=='no';

$numPhoto=1;
$numPhotos=count($photos);

$numColumn=1;
$newRow=true;

$columnWidth=sprintf('%.02f',100/$attColumns);
?>


<div id="fb-root"></div>
<div class="mh-wrapper mh-photos">
  <div class="row">
      <?php if($photos): ?>
        <?php if($attSlideshow): ?>
        <div class="col-sm-4 col-md-3 mh-photo">
          <a class="photos-gallery-group play-slideshow" rel="nofollow" onclick="mh.photos.slideshowModal.play();">
            <i class="fa fa-play"></i>
            <div class="mh-block mh-block-photos-title-date-wrapper">
              <div class="mh-block mh-block-photos-title">Play Slideshow</div>
            </div>
          </a>
        </div>
        <?php endif; ?>
        <?php foreach($photos as $i=>$photo): ?>
          <div class="col-sm-4 col-md-3 mh-photo">
            <a class="photos-gallery-group" rel="nofollow" onclick="mh.photos.slideshowModal.open(<?php echo $i; ?>);"> <!-- href="<?php //echo $photoUrl; ?>" target="_blank"> -->
              <img class="mh-image mh-image-photos-thumbnail" src="<?php echo $this->photoDownloadUrl($photo['url'], true, true, 'client'); ?>" />

              <div class="mh-block mh-block-photos-title-date-wrapper">
                <div class="mh-block mh-block-photos-title"><?php echo esc_html($photo['title']); ?></div>
                <div class="mh-block mh-block-photos-date"><?php echo esc_html($photo['date']); ?></div>
              </div>
            </a>
          </div>
          <?php //endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="mh-no-results">No photos to display.</div>
      <?php endif; ?>
  </div>
</div>

<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodePhotos.js" type="text/javascript"></script>
<script type="text/javascript">
  <?php $xhrAttributes = $this->xhrAttributes('share'); ?>

  jQuery(function($){
    _.extend(mh.photos, {
  	  xhr: {
        url: '<?php echo $xhrAttributes['url']; ?>',
        actions: <?php echo json_encode($xhrAttributes['actions']); ?>
      },

      url: '<?php echo get_permalink() ?>',

      data: <?php echo json_encode($photos, JSON_PRETTY_PRINT) ?>,

      facebook: {
        exists: <?php echo $facebook || 'false'; ?>,
        appId: <?php echo $facebookAppId ? "'" . $facebookAppId . "'" : 'null'; ?>,
        page: <?php echo $attFacebook==='page'?'true':'false'; ?>
      }
    });
    mh.photos.init();
  });
</script>
