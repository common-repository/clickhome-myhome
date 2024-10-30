<?php
/**
 * The tenderPackages view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.6
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderPackagesController))
  die;

/**
 * @var ShortcodeTenderPackagesController   $this
 * @var string[][]                         $selectedPackage
 * @var string[][]                         $packages
 */

global $mh_site_url;
?>

<div class="mh-wrapper mh-wrapper-tender-package">
  <h2 class="entry-title">
    <a href="<?php echo esc_html($mh_site_url); ?>">Home</a> <i class="mh-breadcrumb-next">/</i> 
    <a href="<?php echo esc_html($tender->urls->overview); ?>"><?php echo esc_html($tender->housetypename); ?></a> <i class="mh-breadcrumb-next">/</i> 
    Packages
  </h2>
  
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
            <?php if(!$tender->isPackagesClientEditable): ?>
            <div class="mh-alert mh-tender-status margin-bottom-15">
                <i class="fa fa-lock"></i>
                <h4>Packages are currently locked.</h4><br/>
                Editing of packages is disabled.
            </div>
            <?php endif; ?>

            <h3 data-category-name class="mh-row"></h3>
            <p data-category-description></p>
          
            <!-- <div class="mh-alert">
              <div>
                Selections successfully changed! <a href="javascript:window.location.reload(true);">Refresh to update order</a>.
              </div>
            </div>

            <h5><?php //echo $optionSectionsTitles[$section]; ?></h5> -->
          </div>

          <!-- Packages -->
          <div class="mh-products-body">
            <div data-packages class="row">
              <div data-loading-packages class="mh-loading"></div>
              <div data-no-results class="mh-no-results mh-hide">No packages to display.</div>

              <!-- Grid -->
              <div class="mh-products-grid">
                <div data-base="package-grid" class="col-sm-6 col-lg-4 mh-product-wrapper">
                  <input type="checkbox" data-package-id class="mh-checkbox" onchange="mh.tenders.packages.sync()" />
                  <div class="mh-product">
                    <div class="mh-top" data-open-modal>
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
                      <!-- Price -->
                      <div data-price class="mh-price"></div>

                      <div class="mh-flex-row">
                        <!-- Select -->
                        <div class="mh-select">
                          <label data-select class="mh-button-wrapper mh-button-block">
                            <span class="mh-button">Select<span>ed</span><i></i></span>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- <i class="mh-loading"></i> -->
                </div>
              </div>
              
              <!-- List -->
              <ul class="mh-products-list">
                <li data-base="package-list" class="col-xs-12 mh-product-wrapper">
                  <label class="mh-product">
                    <span class="col-xs-4">
                      <input type="checkbox" data-package-id class="mh-checkbox" onchange="mh.tenders.packages.sync()" />
                      <a data-name class="mh-name"></a>
                    </span>
                    <span class="col-xs-6" data-description></span>
                    <span class="col-xs-2" data-price></span>
                  </label>
                </li>
              </ul>
            </div>
          </div>
        </div>
        
        <div class="mh-sticky-footer">
          <div class="text-right">
            <a id="save-changes" class="mh-button-wrapper" href="javascript:mh.tenders.packages.save()" disabled>
              <span class="mh-button">Save Changes</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
<div style='display:none'>
  <div id="mh-package-details" class="mh-products">
    <h2 data-title>&nbsp;</h2>
    <div class="mh-products-grid">
      <div class="mh-product-wrapper">
        <input type="checkbox" id="modalCheckbox" class="mh-checkbox" data-checkbox data-package-id onchange="mh.tenders.packages.sync()" />
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

<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeTenderPackages.js" type="text/javascript"></script>
<script src="<?php echo MH_URL_SCRIPTS; ?>/stickyFooter.js" type="text/javascript"></script>
<script type="text/javascript">
  jQuery(function ($) {
    _.extend(mh.tenders.packages, {
      options: {
        showItemPrices: <?php echo $atts['showitemprices'] ? 'true' : 'false' ?>
      },
      
      vars: {
        tenderId: <?php echo $tender->tenderid; ?>,
        categoryId: <?php echo isset($category) ? $category->id : 'null'; ?>
      },
      
      data: _.extend(mh.tenders.packages.data, {
        tender: <?php echo json_encode($tender) ?>
      })
    });
    mh.tenders.packages.init();
  });
</script>