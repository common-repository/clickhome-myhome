<?php
/**
 * The tenderSelections view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderSelectionsController))
  die;

/**
 * @var ShortcodeTenderSelectionsController $this
 * @var string[][]                         $atts
 * @var string[][]                         $selections
 */

global $mh_site_url;
?>

<div class="mh-wrapper mh-tender-selection">
  <h2 class="entry-title">
    <a href="<?php echo esc_html($mh_site_url); ?>">Home</a> <i class="mh-breadcrumb-next">/</i> 
    <a href="<?php echo esc_html($tender->urls->overview); ?>"><?php echo esc_html($tender->housetypename); ?></a> <i class="mh-breadcrumb-next">/</i> 
    Selections
  </h2>

  <?php //var_dump($tender); ?>
  <!-- <div class="row mh-sub-title">
    <div class="col-sm-6">
      <?php if( $tender->isSelectionsClientEditable ): ?>
      <?php endif; ?>
    </div>
    <div class="col-sm-6 text-right">
      <?php if( $tender->isSelectionsClientEditable ): ?>
        <span>Selection period expires <?php //echo esc_attr($tender->selectionExpiryDate); ?></span>
      <?php else: ?>
        <span>Selections are currently closed</span>
      <?php endif; ?>
    </div>
  </div> -->

  <div class="row mh-description">
    <div class="col-xs-12">
      <div class="padding-15">
        <?php echo $atts['content']; ?>
      </div>
    </div>
  </div>

  <div>
    <?php if(!count($categories)): ?>
      <div class="mh-no-results">There are no selections to display.</div>
    <?php endif; ?>

    <div class="mh-masonry row">
      <?php if(count($categories)) foreach($categories as $category): ?>
        <!-- <div class="col-md-12 col-lg-6 margin-bottom-30"> -->
          <div class="mh-card">
            <div class="mh-card-header">
              <!-- Categories -->
              <?php if($category->image): ?>
                <div class="mh-thumb">
                  <img src="<?php echo $this->photoDownloadUrl($category->image, true, null); ?>">
                </div>
              <?php endif; ?>
              <h3><?php echo($category->name) ?></h3>
              <?php echo($category->description) ?>
            </div>

            <div class="mh-card-content">
              <!-- Sub-Categories -->
              <?php foreach($category->subCategories as $subCategory): ?>
                <div class="mh-sub-category">
                  <?php if($subCategory->categoryImage): ?>
                    <div class="mh-thumb">
                      <img src="<?php echo $this->photoDownloadUrl($subCategory->categoryImage, true, null); ?>">
                    </div>
                  <?php endif; ?>
                  <h4><?php echo esc_html($subCategory->categoryName); ?></h4>
                  <?php echo esc_html($subCategory->description); ?>

                  <!-- Selections -->
                  <?php foreach($subCategory->selections as $selection): ?>
                    <div class="mh-selection">
                      <h5><a href="<?php echo $editUrl . '#cat=' . $category->primaryCategoryId . '&subCat=' . $subCategory->optionCategoryId . '&placeholder=' . $selection->placeholderSelectionId ?>"><?php echo esc_html($selection->placeholderName); ?></a></h5>
                      <?php echo esc_html($selection->description); ?>

                      <!-- Options -->
                      <?php if($atts['showsummaries']): ?>
                        <ul class="mh-options">
                          <?php foreach($selection->substitutionOptions as $option): ?>
                            <?php if($option->selectCount): ?>
                              <li>
                                <?php 
                                  if($atts['showitemquantities']) echo('x' . $option->selectCount . ' - '); 
                                  echo($option->optionName); 
                                ?>
                              </li>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                    </div>

                    <div class="mh-selection-footer mh-flex-row">
                      <div class="mh-flex-1 mh-summary <?php echo ($selection->outStandingCount <= 0) ? 'mh-complete' : 'mh-incomplete' ?>">
                        <?php if($atts['showrunningquantities']): ?>
                          <?php if($selection->outStandingCount <= 0): ?>
                            Selection Complete <small><i class="fa fa-check"></i></small>
                          <?php else: ?>
                            <?php echo $selection->selectedCount . '/' . $selection->totalCount . ' Selections. <strong>' . $selection->outStandingCount . ' Remain</strong>' ?>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                      <!-- <div class="col-xs-12 col-sm-6 text-right"> -->
                        <span class="mh-button-wrapper margin-left-15">
                          <a class="mh-button" href="<?php echo $editUrl . '#cat=' . $category->primaryCategoryId . '&subCat=' . $subCategory->optionCategoryId . '&placeholder=' . $selection->placeholderSelectionId ?>">
                            <div class="text-ellipsis"><?php echo ($tender->isSelectionsClientEditable ? 'Edit' : 'Review') . ' ' . ($selection->placeholderName) ?></div>
                          </a>
                        </span>
                      <!-- </div> -->
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <!-- </div> -->
      <?php endforeach; ?>
    </div>
  </div>
	<!--<div class="mh-bottom-nav clearfix">
		<a class="mh-button mh-button-sub pull-right" href="<?php echo esc_html($tender->urls->overview); ?>"><?php _e('Back to Overview','myHome'); ?></a>
	</div>
</div>-->

<!-- Scripts -->
<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeTenderSelections.js" type="text/javascript"></script>
<script>
  jQuery(function ($) {
    _.extend(mh.tenders.selections, {
      data: {
        tender: <?php echo json_encode($tender) ?>,
        categories: <?php echo json_encode($categories) ?> // As XHR func does not yet return
      },
    });
    //mh.tenders.selections.init();
  });
</script>
