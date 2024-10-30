<?php
  /**
   * The TenderSelectionsEdit view
   *
   * @package    MyHome
   * @subpackage ViewsShortcodes
   * @since      1.5
   */

  // Exit if the script is accessed directly
  if(!defined('ABSPATH'))
    die;

  // Exit if not called from the controller
  if(!isset($this)||!($this instanceof ShortcodeTenderSelectionsEditController))
    die;

  /**
   * @var ShortcodeTenderSelectionsEditController  $this
   * @var mixed[]                                 $category
   */

  global $mh_site_url;

  $optionSectionsTitles = [
    'current'=>__('Current Selections','myHome'),
    'alternatives'=>__('Alternatives','myHome'),
    'upgrades'=>__('Upgrades','myHome')
  ];
?>

<form class="mh-wrapper mh-wrapper-tender-selection-edit">
  <h2 class="entry-title">
    <a href="<?php echo esc_html($mh_site_url); ?>">Home</a> <i class="mh-breadcrumb-next">/</i> 
	  <a href="<?php echo esc_html($tender->urls->overview); ?>"><?php echo esc_html($tender->housetypename); ?></a> <i class="mh-breadcrumb-next">/</i> 
	  <a href="<?php echo esc_html($tender->urls->selections); ?>">Selections</a> <i class="mh-breadcrumb-next">/</i> 
	  <span data-category-name></span> <?php //echo esc_html($category->name); ?>
  </h2>

  <div class="row">
    <div class="col-xs-12">
      <div class="mh-header padding-bottom-15">
        <?php echo $atts['content']; ?>
      </div>

      <div class="mh-header-category padding-bottom-30">
        <span data-category-description></span><?php //echo esc_html($category->description); ?>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="col-sm-3 mh-product-categories">
      <div data-loading-categories class="mh-loading"></div>
      <ul data-categories></ul>
      <div data-no-categories class="mh-no-results mh-hide">No categories to display.</div>
    </div>

    <div class="col-sm-9 mh-products">
      <div class="mh-card margin-bottom-15">
        <div class="margin-bottom-15">
          <div class="mh-products-header">
            <?php if(!$tender->isSelectionsClientEditable): ?>
            <div class="mh-alert mh-tender-status margin-bottom-15">
                <i class="fa fa-lock"></i>
                <h4>Selections are currently locked.</h4><br/>
                Editing of selections is disabled.
            </div>
            <?php endif; ?>

            <h3 data-placeholder-name class="mh-row"></h3>
            <p data-placeholder-description></p>
          </div>

          <!-- Selections -->
          <div class="mh-products-body">
            <div data-products class="mh-products-grid row">
              <div data-loading-products class="mh-loading"></div>
              <div data-no-results class="mh-no-results mh-hide">No options to display.</div>

              <!-- Product -->
              <div data-base="product" class="col-sm-6 col-lg-4 mh-product-wrapper">
                <input type="checkbox" data-option-id class="mh-checkbox" onchange="mh.tenders.selectionsEdit.sync()" />
                <div class="mh-product">
                  <div class="mh-top" data-open-modal>
                    <!-- Type -->
                    <div class="mh-option-type mh-isalternate"><span>Alternate</span></div>
                    <div class="mh-option-type mh-isupgrade"><span>Upgrade</span></div>
                    <!-- Tools -->
                    <div class="mh-icons">
                      <div class="mh-icon">
                        <a class="mh-icon-note"><i class="fa fa-sticky-note" onclick="mh.tenders.selectionsEdit.toggleNote();"></i><span class="tip">Add / Edit Note</span></a>
                      </div>
                    </div>
                    <!-- Thumbnail -->
                    <div class="mh-img">
                      <div data-photo-count class="mh-photos"><i class="fa fa-photo"></i><span></span></div>
                      <img data-photo />
                    </div>
                    <!-- Name & Description-->
                    <a data-name class="mh-name"></a>
                    <div data-description class="mh-description"></div>
                  </div>
                  <div class="mh-specs">
                    <!-- Price
                    <input type="hidden" data-upgrade-price> -->
                    <div data-price class="mh-price"></div>
                    
                    <div class="mh-flex-row">
                      <!-- Quantity -->
                      <div class="mh-quantity">
                        <!-- <small>qty</small> -->
                        <!-- <input type="number" class="mh-quantity-input" data-parent="#" max="100" min="0" name="myHomeQuantity[][]" step="1" value=""  /> -->
                        <div class="mh-quantity-input">
                          <input type="number" data-quantity onchange="mh.tenders.selectionsEdit.sync();" max="100" min="0" step="1" value="0"> <!-- name="myHomeQuantity[2042][42]"> -->
                          <a class="decrement" onclick="mh.events.stopPropagation(event); mh.tenders.selectionsEdit.adjustQuantityBy(-1);">-</a>
                          <a class="increment" onclick="mh.events.stopPropagation(event); mh.tenders.selectionsEdit.adjustQuantityBy(1);">+</a>
                        </div>
                      </div>
                    
                      <!-- Select -->
                      <div class="mh-select">
                        <label data-select class="mh-button-wrapper mh-button-block">
                          <span class="mh-button">Select<span>ed</span><i></i></span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mh-back mh-flex-column">
                  <h6>Area</h6>
                  <i class="fa fa-times" onclick="mh.events.stopPropagation(event); mh.tenders.selectionsEdit.toggleNote(41);"></i>
                  <select data-area class="form-control margin-bottom-5" onchange="mh.tenders.selectionsEdit.sync();">
                    <option selected disabled hidden value="">Select Area...</option>
                  </select>
                  <textarea data-area-note class="mh-flex-1 margin-bottom-15" placeholder="Area Note..." maxlength="250" onchange="mh.tenders.selectionsEdit.sync();"></textarea>
                  
                  <h6>Note</h6>
                  <textarea data-note class="mh-flex-1" placeholder="Other Notes..." maxlength="250" onchange="mh.tenders.selectionsEdit.sync();"></textarea>

                  <a class="mh-button mh-button-md" onclick="mh.events.stopPropagation(event); mh.tenders.selectionsEdit.sync()">Save</a>
                  <i class="fa fa-times" onclick="mh.events.stopPropagation(event); mh.tenders.selectionsEdit.toggleNote(41);"></i>
                </div>

                <!-- <i class="mh-loading"></i> -->
              </div>
            </div>
          </div>
        </div>
        
        <div class="mh-sticky-footer">
          <!-- <span class="mh-running-price mh-row mh-hide">Total: $<span data-running-price class="mh-total-price"></span></span> -->

          <div class="mh-flex-row">
            <div class="mh-remain mh-flex-1">
              <span class="mh-row mh-hide">
                <span class="mh-selections-remain">
                  <span data-quantity-selected></span> / <span data-quantity-total></span> options selected, 
                </span>
                <span data-quantity-remain></span> remain</span>
              </span>
    
              <span class="mh-row mh-hide">
                <span class="mh-selections-complete">Selection Complete</span>
                <span class="mh-quantity-extra"></span>
              </span>
            </div>

            <a id="save-changes" class="mh-button-wrapper" href="javascript:mh.tenders.selectionsEdit.save()" disabled>
              <span class="mh-button">Save Changes</span>
            </a>
            <!-- <a class="mh-button mh-button-md" href="<?php echo esc_html($tender->urls->overview); ?>"><?php _e('Done','myHome'); ?></a> -->
          </div>
        </div>
      </div>
    </div>

  <!-- <a class="mh-button mh-button-sub pull-right" href="<?php echo esc_html($tender->urls->selections); ?>"><?php _e('Back to Selections','myHome'); ?></a> -->
</form>

<!-- Modals -->
<div style="display:none;">
  <div id="mh-selection-details" class="mh-products">
    <h2 data-title>&nbsp;</h2>
    <div class="mh-products-grid">
      <div class="mh-product-wrapper">
        <input type="checkbox" id="modalCheckbox" class="mh-checkbox" data-checkbox data-option-id onchange="mh.tenders.selectionsEdit.sync()" /> <!-- data-option-id="" data-placeholder-id="" /> -->
        <div class="mh-product mh-flex-row">
          <div class="mh-flex-1 mh-flex-shrink mh-overflow-hidden">
            <div class="mh-slideshow" data-slideshow-images></div>
          </div>

          <div id="cboxSide" class="mh-specs mh-flex-column">
            <div class="mh-description mh-flex-1">
              <h6>Description</h6>
              <p data-description>&nbsp;</p>
            </div>
            <div class="mh-price">
              <h6>Price</h6>
              <p data-price>&nbsp;</p>
            </div>

            <div class="mh-area">
              <h6>Area</h6>
              <select data-area class="form-control margin-bottom-5" onchange="mh.tenders.selectionsEdit.sync();"><option selected disabled hidden value="">Select Area...</option></select>
              <textarea data-area-note placeholder="Area Note..." onchange="mh.tenders.selectionsEdit.sync();"></textarea>
            </div>

            <div class="mh-note">
              <h6>Note</h6>
              <textarea data-note placeholder="Other Note..." onchange="mh.tenders.selectionsEdit.sync();"></textarea>
            </div>
                    
            <div class="mh-flex-row">
              <!-- Quantity -->
              <div class="mh-quantity">
                  <h6>Qty</h6>
                  <!-- <small>qty</small> -->
                  <div class="mh-quantity-input">
                    <input type="number" data-quantity onchange="mh.tenders.selectionsEdit.sync();" max="100" min="0" step="1" />
                    <a class="decrement" onclick="mh.events.stopPropagation(event); mh.tenders.selectionsEdit.adjustQuantityBy(-1);">-</a>
                    <a class="increment" onclick="mh.events.stopPropagation(event); mh.tenders.selectionsEdit.adjustQuantityBy(1);">+</a>
                  </div>
                </div>

              <!-- Select -->
              <div class="mh-select">
                <label for="modalCheckbox" class="mh-button-wrapper mh-button-block">
                  <span class="mh-button">Select<span>ed</span><i></i></span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeTenderSelectionsEdit.js" type="text/javascript"></script>
<script src="<?php echo MH_URL_SCRIPTS; ?>/stickyFooter.js" type="text/javascript"></script>
<script>
  jQuery(function ($) {
    _.extend(mh.tenders.selectionsEdit, {
      options: {
        showItemQuantities: <?php echo $atts['showitemquantities'] ? 'true' : 'false' ?>,
        showItemPrices: <?php echo $atts['showitemprices'] ? 'true' : 'false' ?>,
        showRunningQuantities: <?php echo $atts['showrunningquantities'] ? 'true' : 'false' ?>,
        showRunningPrices: <?php echo $atts['showrunningprices'] ? 'true' : 'false' ?>
      },
      
      data: _.extend(mh.tenders.selectionsEdit.data, {
        tender: <?php echo json_encode($tender) ?>
      })
    });
    mh.tenders.selectionsEdit.init();
  });
</script>