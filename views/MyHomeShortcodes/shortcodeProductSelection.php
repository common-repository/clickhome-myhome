<?php
/**
 * The productSelection view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeProductSelectionController))
  die;

/**
 * @var ShortcodeProductSelectionController $this
 * @var string                              $attFilterPrice
 * @var string                              $attFilterSize
 * @var string                              $attFilterWidth
 * @var string                              $attFilterBedrooms
 * @var string                              $attFilterBathrooms
 * @var string                              $attFilterCarparks
 * @var string                              $attDisplayLinks
 * @var int                                 $maxMinPrice
 * @var int                                 $maxSize
 * @var int                                 $maxMinWidth
 * @var int                                 $maxBedrooms
 * @var int                                 $maxBathrooms
 * @var int                                 $maxGarage
 * @var MyHomeHouseType[]                   $houseTypes
 * @var string[]                            $houseTypesUrls
 * @var MyHomeDisplay[]                     $displays
 * @var string[]                            $displaysUrls
 * @var WP_Post[]                           $docsAttachments
 */

$rooms=MyHomeHouseTypeRoom::$STANDARD_ROOMS;
$roomsIds=array_keys($rooms);

$houseTypesRooms=[];

foreach($houseTypes as $houseType){
  $roomsList=$houseType->roomsList();
  $roomsList=array_intersect($roomsList,$roomsIds);

  $houseTypesRooms[$houseType->houseid]=$roomsList;
}
?>
<div class="row mh-wrapper mh-wrapper-product-selection">
  <div class="col-xs-3 col-md-3 mh-section mh-section-product-selection-filter">
    <?php if($attFilterPrice==='on'): ?>
      <div class="mh-block mh-block-product-selection-filter-price">
        <div class="mh-row mh-row-product-selection-filter-price-title"><?php _ex('Price: ','Product Selection', 'myHome'); ?>
          <div class="mh-text mh-text-product-selection-filter-price-value" id="divMyHomeProductSelectionFilterPriceValue"></div>
        </div>
        <div class="mh-row mh-row-product-selection-filter-price-slider mh-slider" id="divMyHomeProductSelectionFilterPriceSlider"></div>
      </div>
    <?php endif; ?>
    <?php if($attFilterSize==='on'): ?>
      <div class="mh-block mh-block-product-selection-filter-size">
        <div class="mh-row mh-row-product-selection-filter-size-title"><?php _ex('Size: ','Product Selection',
            'myHome'); ?>
          <div class="mh-text mh-text-product-selection-filter-size-value" id="divMyHomeProductSelectionFilterSizeValue"></div>
        </div>
        <div class="mh-row mh-row-product-selection-filter-size-slider mh-slider" id="divMyHomeProductSelectionFilterSizeSlider"></div>
      </div>
    <?php endif; ?>
    <?php if($attFilterWidth==='on'): ?>
      <div class="mh-block mh-block-product-selection-filter-width">
        <div class="mh-row mh-row-product-selection-filter-width-title"><?php _ex('Width: ','Product Selection',
            'myHome'); ?>
          <div class="mh-text mh-text-product-selection-filter-width-value" id="divMyHomeProductSelectionFilterWidthValue"></div>
        </div>
        <div class="mh-row mh-row-product-selection-filter-width-slider mh-slider" id="divMyHomeProductSelectionFilterWidthSlider"></div>
      </div>
    <?php endif; ?>
    <?php if($attFilterBedrooms==='on'): ?>
      <div class="mh-block mh-block-product-selection-filter-bedrooms">
        <div class="mh-row mh-row-product-selection-filter-bedrooms-title"><?php _ex('Bedrooms: ','Product Selection',
            'myHome'); ?>
          <div class="mh-text mh-text-product-selection-filter-bedrooms-value" id="divMyHomeProductSelectionFilterBedroomsValue"></div>
        </div>
        <div class="mh-row mh-row-product-selection-filter-bedrooms-slider mh-slider" id="divMyHomeProductSelectionFilterBedroomsSlider"></div>
      </div>
    <?php endif; ?>
    <?php if($attFilterBathrooms==='on'): ?>
      <div class="mh-block mh-block-product-selection-filter-bathrooms">
        <div class="mh-row mh-row-product-selection-filter-bathrooms-title"><?php _ex('Bathrooms: ','Product Selection',
            'myHome'); ?>
          <div class="mh-text mh-text-product-selection-filter-bathrooms-value" id="divMyHomeProductSelectionFilterBathroomsValue"></div>
        </div>
        <div class="mh-row mh-row-product-selection-filter-bathrooms-slider mh-slider" id="divMyHomeProductSelectionFilterBathroomsSlider"></div>
      </div>
    <?php endif; ?>
    <?php if($attFilterCarparks==='on'): ?>
      <div class="mh-block mh-block-product-selection-filter-carparks">
        <div class="mh-row mh-row-product-selection-filter-carparks-title"><?php _ex('Carparks: ','Product Selection',
            'myHome'); ?>
          <div class="mh-text mh-text-product-selection-filter-carparks-value" id="divMyHomeProductSelectionFilterCarparksValue"></div>
        </div>
        <div class="mh-row mh-row-product-selection-filter-carparks-slider mh-slider" id="divMyHomeProductSelectionFilterCarparksSlider"></div>
      </div>
    <?php endif; ?>
    <div class="mh-section mh-section-product-selection-rooms">
      <?php foreach($rooms as $id=>$name): ?>
        <label><input class="mh-room-checkbox" type="checkbox" value="<?php echo $id; ?>"><?php echo esc_html($name); ?>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="row col-xs-9 col-md-9 mh-section mh-section-product-selection-house-types">
    <?php foreach($houseTypes as $id=>$houseType): ?>
      <?php
      $filters=['price'=>(int)$houseType->pricefrom,
        'size'=>(int)$houseType->sizevalue,
        'width'=>(int)$houseType->minwidth,
        'bedrooms'=>(int)$houseType->bedqty,
        'bathrooms'=>(float)$houseType->bathqty,
        'carparks'=>(int)$houseType->garageqty,
        'rooms'=>implode(',',$houseTypesRooms[$houseType->houseid])];

      $dataAttrs='';

      foreach($filters as $filter=>$value)
        $dataAttrs.=sprintf(' data-%s="%s"',$filter,esc_attr($value));
        ?>
        <div class="col-xs-12 col-md-6">
          <div class="mh-block mh-prdcts-housetype"<?php echo $dataAttrs; ?>>
            <div class="mh-prdcts-housetype-imgs">
              <?php if(count($houseType->housedocs) && isset($docsAttachments[$houseType->housedocs[0]->url])): ?>
                <a href="<?php echo esc_url($houseTypesUrls[$houseType->houseid]); ?>">
                  <?php echo wp_get_attachment_image($docsAttachments[$houseType->housedocs[0]->url]->ID,[300,300]); ?>
                </a>
              <?php endif; ?>
              <?php //foreach($houseType->housedocs as $doc): ?>
                <?php //if(isset($docsAttachments[$doc->url])): ?>
                  <!-- <div><?php //echo wp_get_attachment_image($docsAttachments[$doc->url]->ID,[300,300]); ?></div> -->
                <?php //endif; ?>
              <?php //endforeach; ?>
            </div>
            <div class="mh-row mh-prdcts-housetype-title">
              <div class="mh-cell mh-prdcts-housetype-name">
                <a class="mh-link mh-link-product-selection-house-type" href="<?php echo esc_url($houseTypesUrls[$houseType->houseid]); ?>"><?php echo esc_html($houseType->housename); ?></a>
              </div>
              <div class="mh-cell mh-prdcts-housetype-size"><?php echo esc_html($houseType->size); ?></div>
            </div>
            <div class="mh-row mh-prdcts-housetype-features">
              <div class="mh-cell mh-prdcts-housetype-bedrooms"
                title="<?php _ex('Bedrooms','Product Selection','myHome'); ?>"><span
                  class="mh-icon">&nbsp;</span> <?php echo (int)$houseType->bedqty; ?></div>
              <div class="mh-cell mh-prdcts-housetype-bathrooms"
                title="<?php _ex('Bathrooms','Product Selection','myHome'); ?>"><span
                  class="mh-icon">&nbsp;</span> <?php echo (float)$houseType->bathqty; ?></div>
              <div class="mh-cell mh-prdcts-housetype-garages"
                title="<?php _ex('Carparks','Product Selection','myHome'); ?>"><span
                  class="mh-icon">&nbsp;</span> <?php echo (int)$houseType->garageqty; ?></div>
              <?php if($houseType->hasTheatreRoom()): ?>
                <div class="mh-cell mh-prdcts-housetype-theatre"
                  title="<?php _ex('Theatre','Product Selection','myHome'); ?>"><span class="mh-icon">&nbsp;</span></div>
              <?php endif; ?>
              <?php if($houseType->hasStudyRoom()): ?>
                <div class="mh-cell mh-prdcts-housetype-study"
                  title="<?php _ex('Study','Product Selection','myHome'); ?>"><span class="mh-icon">&nbsp;</span></div>
              <?php endif; ?>
            </div>
            <?php if(trim($houseType->description) != ''): ?>
              <div class="mh-row mh-row-product-selection-house-type-description">
                <div class="mh-cell mh-prdcts-housetype-description"><?php echo nl2br(esc_html($houseType->description)); ?></div>
              </div>
            <?php endif; ?>
            <?php if($attDisplayLinks==='on'&&isset($displays[$houseType->houseid])): ?>
              <?php foreach($displays[$houseType->houseid] as $display): ?>
                <div class="mh-row mh-row-product-selection-house-type-display">
                  <div class="mh-cell mh-prdcts-housetype-display">
                    <a class="mh-link mh-link-product-selection-display" href="<?php echo esc_url($displaysUrls[$houseType->houseid][$display->displayid]); ?>">
                      Display open <?php if($display->address) printf(__(' in %s', 'myHome'),str_replace(["\n","\r"],' ',$display->address)); ?>
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
  </div>
</div>
<script type="text/javascript">
  jQuery(function($){
    $(document).ready(function(){
      filterHouseTypes();
    });

    var maxPrice=<?php echo (int)$maxMinPrice; ?>;
    var maxSize=<?php echo (int)$maxSize; ?>;
    var maxWidth=<?php echo (int)$maxMinWidth; ?>;
    var maxBedrooms=<?php echo (int)$maxBedrooms; ?>;
    var maxBathrooms=<?php echo (int)$maxBathrooms; ?>;
    var maxCarparks=<?php echo (int)$maxGarage; ?>;
	
    if(typeof $.fn.slider==="function")
    {
      var slider=$("#divMyHomeProductSelectionFilterPriceSlider").slider(
        {
          range:true,
          min:0,
          max:maxPrice,
          step:10000,
          values:[0,maxPrice],
          slide:function(e,ui){
            $("#divMyHomeProductSelectionFilterPriceValue")
              .empty()
              .append("$"+ui.values[0]+" <?php _ex('to','Product Selection','myHome'); ?> $"+ui.values[1]);

            setTimeout(function(){
              filterHouseTypes();
            },0);
          }
        });
      $("#divMyHomeProductSelectionFilterPriceValue").append("$"+slider.slider("values",0)+
        " <?php _ex('to','Product Selection','myHome'); ?> $"+slider.slider("values",1));

      slider=$("#divMyHomeProductSelectionFilterSizeSlider").slider(
        {
          min:0,
          max:maxSize,
          step:10,
          value:maxSize,
          slide:function(e,ui){
            $("#divMyHomeProductSelectionFilterSizeValue")
              .empty()
              .append("<?php _ex('up to','Product Selection','myHome'); ?> "+ui.value+
              "<?php _ex('sqm','Product Selection','myHome'); ?>");

            setTimeout(function(){
              filterHouseTypes();
            },0);
          }
        });
      $("#divMyHomeProductSelectionFilterSizeValue").append("<?php _ex('up to','Product Selection','myHome'); ?> "+
        slider.slider("value")+"<?php _ex('sqm','Product Selection','myHome'); ?>");

      slider=$("#divMyHomeProductSelectionFilterWidthSlider").slider(
        {
          min:0,
          max:maxWidth,
          step:10,
          value:maxWidth,
          slide:function(e,ui){
            $("#divMyHomeProductSelectionFilterWidthValue")
              .empty()
              .append("<?php _ex('up to','Product Selection','myHome'); ?> "+ui.value+
              "<?php _ex('m','Product Selection','myHome'); ?>");

            setTimeout(function(){
              filterHouseTypes();
            },0);
          }
        });
      $("#divMyHomeProductSelectionFilterWidthValue").append("<?php _ex('up to','Product Selection','myHome'); ?> "+
        slider.slider("value")+"<?php _ex('m','Product Selection','myHome'); ?>");

      slider=$("#divMyHomeProductSelectionFilterBedroomsSlider").slider(
        {
          min:0,
          max:maxBedrooms,
          value:0,
          slide:function(e,ui){
            $("#divMyHomeProductSelectionFilterBedroomsValue")
              .empty()
              .append(ui.value+"+");

            setTimeout(function(){
              filterHouseTypes();
            },0);
          }
        });
      $("#divMyHomeProductSelectionFilterBedroomsValue").append(slider.slider("value")+"+");

      slider=$("#divMyHomeProductSelectionFilterBathroomsSlider").slider(
        {
          min:0,
          max:maxBathrooms,
          value:0,
          slide:function(e,ui){
            $("#divMyHomeProductSelectionFilterBathroomsValue")
              .empty()
              .append(ui.value+"+");

            setTimeout(function(){
              filterHouseTypes();
            },0);
          }
        });
      $("#divMyHomeProductSelectionFilterBathroomsValue").append(slider.slider("value")+"+");

      slider=$("#divMyHomeProductSelectionFilterCarparksSlider").slider(
        {
          min:0,
          max:maxCarparks,
          value:0,
          slide:function(e,ui){
            $("#divMyHomeProductSelectionFilterCarparksValue")
              .empty()
              .append(ui.value+"+");

            setTimeout(function(){
              filterHouseTypes();
            },0);
          }
        });
      $("#divMyHomeProductSelectionFilterCarparksValue").append(slider.slider("value")+"+");
    }
    else
      $(".mh-wrapper-product-selection .mh-section-product-selection-filter").hide();

    $(".mh-wrapper-product-selection .mh-room-checkbox").click(function(){
      filterHouseTypes();
    });

    /*if(typeof $.fn.slick==="function")
      $(".mh-wrapper-product-selection .mh-prdcts-housetype-imgs").slick(
        {
          infinite:true,
          slidesToShow:1,
          slidesToScroll:1
        });
    else
      $(".mh-wrapper-product-selection .mh-prdcts-housetype-imgs").addClass("no-carousel");*/

    function filterHouseTypes(){
      var slider=typeof $.fn.slider==="function";

      if(slider)
      {
        var prices=$("#divMyHomeProductSelectionFilterPriceSlider").slider("values");
        var size=$("#divMyHomeProductSelectionFilterSizeSlider").slider("value");
        var width=$("#divMyHomeProductSelectionFilterWidthSlider").slider("value");
        var bedrooms=$("#divMyHomeProductSelectionFilterBedroomsSlider").slider("value");
        var bathrooms=$("#divMyHomeProductSelectionFilterBathroomsSlider").slider("value");
        var carparks=$("#divMyHomeProductSelectionFilterCarparksSlider").slider("value");
      }

      var rooms=$(".mh-wrapper-product-selection .mh-room-checkbox:checked").map(function(){
        return $(this).val();
      }).get();

      $(".mh-section-product-selection-house-types .mh-prdcts-housetype").each(function(){
        var filterRooms=true;

        var houseTypeRooms=($(this).data("rooms")+"").split(",");

        $.each(rooms,function(key,room){
          if(houseTypeRooms.indexOf(room)=== -1)
          {
            filterRooms=false;

            return false;
          }
        });

        var filterConditions=true;
        if(slider)
        {
          if(prices[0]!==undefined&& !isNaN(prices[0]))
            filterConditions=$(this).data("price")>=prices[0]&&$(this).data("price")<=prices[1];

          if(!isNaN(size)&&filterConditions)
            filterConditions=$(this).data("size")<=size;

          if(!isNaN(width)&&filterConditions)
            filterConditions=$(this).data("width")<=width;

          if(!isNaN(bedrooms)&&filterConditions)
            filterConditions=$(this).data("bedrooms")>=bedrooms;

          if(!isNaN(bathrooms)&&filterConditions)
            filterConditions=$(this).data("bathrooms")>=bathrooms;

          if(!isNaN(carparks)&&filterConditions)
            filterConditions=$(this).data("carparks")>=carparks;
        }

        if(filterRooms&&filterConditions)
        {
          $(this).show();

          var images=$(this).children(".mh-prdcts-housetype-imgs");
          //console.log(images.find('img'));
          //if(images.find('img').length > 0) 
          	//images.slickGoTo(images.slickCurrentSlide());
        }
        else
          $(this).hide();
      });
    }
  });
</script>
