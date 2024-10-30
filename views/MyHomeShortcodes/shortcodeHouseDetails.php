<?php
/**
 * The houseDetails view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeHouseDetailsController))
  die;

/**
 * @var ShortcodeHouseDetailsController $this
 * @var string                          $attMode
 * @var mixed[]                         $houseDetails
 * @var string[]                        $fieldNames
 */

$details=$houseDetails->details;
$photos=$houseDetails->photos;

// Filter any field whose name is unknown
foreach($details as $field=>$value)
  if(!isset($fieldNames[$field]))
    unset($details[$field]);
?>
<div class="mh-wrapper mh-wrapper-house-details house-details-layout-<?php echo $attMode; ?>">
  <div class="mh-section mh-section-house-details-details-image-wrapper">
    <div class="mh-section mh-section-house-details-details">
      <?php foreach($details as $field=>$value): ?>
        <div class="mh-row mh-row-house-details-details">
          <div
            class="mh-cell mh-cell-house-details-details-name"><?php echo esc_html($fieldNames[$field]); ?></div>
          <div class="mh-cell mh-cell-house-details-details-value"><?php echo esc_html($value); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if($attMode==='full'): ?>
      <div class="mh-section mh-section-house-details-image">
        <img class="mh-image mh-image-house-details-image" id="imgMyHomeHouseDetailsImage"
          src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">

        <div class="mh-loading mh-loading-house-details-image" id="divMyHomeLoadingHouseDetails"></div>
      </div>
    <?php endif; ?>
  </div>
  <?php if($attMode==='full'): ?>
    <div class="mh-section mh-section-house-details-thumbnails" id="divMyHomeHouseDetailsThumbnails">
      <?php foreach($photos as $photoUrl): ?>
        <a class="mh-block mh-block-house-details-thumbnails-thumbnail"
          href="<?php echo $this->photoDownloadUrl($photoUrl, false, null); ?>" target="_blank"><img
            class="mh-image mh-image-house-details-thumbnails-thumbnail"
            src="<?php echo $this->photoDownloadUrl($photoUrl, true, null); ?>"></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php if($attMode==='full'): ?>
  <script type="text/javascript">
    jQuery(function($){
      var thumbnails=$(".mh-block-house-details-thumbnails-thumbnail");
      var image=$("#imgMyHomeHouseDetailsImage");

      var firstImage=thumbnails.first().attr("href");
      if(firstImage!==undefined)
      {
        image.attr("src",firstImage);
        $("#divMyHomeLoadingHouseDetails").css("display","inline-block");
      }

      if(typeof $.fn.slick==="function")
        $("#divMyHomeHouseDetailsThumbnails").slick(
          {
            dots:true,
            slide:"a",
            slidesToShow:4,
            slidesToScroll:4,
            responsive:[
              {
                breakpoint:1152,
                settings:{
                  dots:true,
                  slide:"a",
                  slidesToShow:3,
                  slidesToScroll:3
                }
              },
              {
                breakpoint:960,
                settings:{
                  dots:true,
                  slide:"a",
                  slidesToShow:2,
                  slidesToScroll:2
                }
              },
              {
                breakpoint:760,
                settings:{
                  dots:true,
                  slide:"a",
                  slidesToShow:1,
                  slidesToScroll:1
                }
              }]
          });
      else
        $("#divMyHomeHouseDetailsThumbnails").addClass("no-carousel");

      thumbnails.click(function(){
        $("#imgMyHomeHouseDetailsImage").attr("src",$(this).attr("href"));
        $("#divMyHomeLoadingHouseDetails").css("display","inline-block");

        return false;
      });

      image.on("load",function(){
        $("#divMyHomeLoadingHouseDetails").hide();
      });
    });
  </script>
<?php endif; ?>
