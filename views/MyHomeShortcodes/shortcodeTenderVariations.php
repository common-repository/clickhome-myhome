<?php
/**
 * The tenderVariations view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.6
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderVariationsController))
  die;

/**
 * @var ShortcodeTenderVariationsController   $this
 * @var string[][]                         $tender
 * @var string[][]                         $selectedVariation
 * @var string[][]                         $packages
 */

global $mh_site_url;
//print_r($tender);
//print_r($tendersUrl);
?>


<div class="mh-wrapper mh-wrapper-tender-variations">
  <h2 class="entry-title">
    <a href="<?php echo esc_html($mh_site_url); ?>">Home</a> <i class="mh-breadcrumb-next">/</i> 
    <a href="<?php echo esc_html($tender->urls->overview); ?>"><?php echo esc_html($tender->housetypename); ?></a> <i class="mh-breadcrumb-next">/</i> 
    Variations
  </h2>

  <div class="row">
    <div class="col-xs-12">
      <div class="padding-15">
        <?php echo $atts['content']; ?>
      </div>
    </div>
  </div>
  
  <div class="row">
    <!-- <h4>Variations</h4> -->
    <div class="col-sm-12 mh-tender-variations">
      <?php if(!count($variations)): ?>
        <div class="mh-no-results">There are no selections to display.</div>
      <?php endif; ?>

      <?php if(isset($variations)): ?>
        <?php foreach($variations as $variation): ?>
          <?php if(count($variation->selections) == 0) continue; ?>
          <?php if($variation->variationStatusId == 0) continue; // Draft ?>

          <div class="mh-card mh-variation <?php echo ($variation->variationStatusId==10 || $variation->variationStatusId==11) ? 'mh-variation-rejected':''; ?> padding-bottom-0 margin-bottom-30 clearfix" data-variation-id="<?php echo($variation->tenderVariationId); ?>">
            <h3><?php echo esc_html($variation->variationName); ?></h3>
            <p class="mh-description">
              <?php 
                $date = new DateTime($variation->dateCreated);
                echo $date->format('jS M Y');
              ?>
            </p>
          
            <!-- Selections -->
            <div class="mh-products margin-right-0">
              <div class="row mh-products-body">
                <?php if(isset($variation->selections)) : ?>
                  <ul class="mh-products-list margin-15">
                      <li class="row mh-product-wrapper mh-product-head">
                        <label class="mh-product">
                          <span class="col-xs-8">Name</span>
                          <span class="col-xs-1"></span>
                          <span class="col-xs-1 text-center">Qty</span>
                          <span class="col-xs-2 text-right">Amount</span>
                        </label>
                      </li>
                    <?php foreach($variation->selections as $selection): //var_dump($selection); ?>
                      <li class="row mh-product-wrapper">
                        <label class="mh-product">
                          <span class="col-xs-8">
                            <!-- <input type="checkbox" class="mh-checkbox" onchange="mh.tenders.packages.sync()" <?php //echo($package->selected?'checked ':' '); ?> id="packageCheckbox[<?php //echo $package->id; ?>]" /> -->
                            <span class="mh-name"><?php echo esc_html($selection->name); ?></span>
                          </span>
                          <span class="col-xs-1">
                            <?php //echo esc_html($selection->description); ?>
                          </span>
                          <span class="col-xs-1 text-center">
                            <?php echo ($selection->qty > 0 ? '+' : '') . $selection->qty; ?>
                          </span>
                          <span class="col-xs-2 text-right">
                            <?php if($selection->sellPrice) echo myHome()->helpers->formatDollars(esc_html($selection->sellPrice)); ?>
                          </span>
                        </label>
                      </li>
                    <?php endforeach; ?>
                    <li class="text-right padding-top-10">
                      Total: <span class="mh-price"><strong><?php echo myHome()->helpers->formatDollars($variation->sellPrice); ?></strong></span>
                    </li>
                  </ul>
                <?php endif; ?>
              </div>
          
              <div class="row mh-products-footer">
	              <div class="col-sm-7 padding-left-30 mh-tender-status">
                  <?php
                    switch($variation->variationStatusId) {
                      case 1: // Client Review
                        echo '<i class="fa fa-play"></i>';
                        break;
                      case 2: // Pending Estimator Review
                        echo '<i class="fa fa-clock-o"></i>';
                        break;
                      case 4: // Accepted
                      case 6: // Contract
                        echo '<i class="fa fa-check"></i>';
                        break;
                      case 5: // Locked
                        echo '<i class="fa fa-lock"></i>';
                        break;
                      case 10: // Rejected by Client
                        break;
                      case 11: // Rejected by Builder
                        echo '<i class="fa fa-times"></i>';
                        break;
                      default:
                        echo '<i class="fa fa-info"></i>';
                        break;
                    }
                  ?>
                  <h4 class="mh-inline-block">
                    <?php
                      switch($variation->variationStatusId) {
                        case 1: // Client Review
                          echo 'Ready to Sign';
                          break;
                        case 2:
                          //echo 'Approved by Client</br>';
                          echo 'Pending Estimator Review';
                          break;
                        //case 3: // Edit Required
                        case 4: // Accepted
                          $date = new DateTime($variation->dateAccepted); //class="mh-approved-variation"
                          echo '<div class="padding-top-5">';
                          echo '  Accepted</br>';
                          echo '  <sup>' . $date->format('jS M Y') . '</sup>';
                          echo '</div>';
                          break;
                        //case 5: // Locked
                        case 6: // Contract
                          echo 'Complete';
                          break;
                        case 10: // Rejected by Client
                          break;
                        /*case 11: // Rejected by Builder
                          //$date = new DateTime($variation->dateAccepted);
                          echo '<span>Rejected by Builder</span>';
                          break;*/
                        case 12: // Expired
                        case 14: // Client Approved
                        default:
                          echo esc_html($variation->variationStatusName);
                          break;
                      }
                    ?>
                  </h4>
	              </div>
                <!-- <div class="col-sm-3 text-right">
                  Total: <span class="mh-price"><strong><?php echo myHome()->helpers->formatDollars($variation->sellPrice); ?></strong></span>
                </div> -->
                <div class="col-sm-5">
                  <?php if($variation->variationStatusId == 1) : ?>
                    <!-- Drafting -->
                    <div class="row">
                      <div class="col-xs-6">
                        <a class="mh-button mh-block mh-reject-variation" href="javascript:mh.tenders.variations.rejectModal.open(<?php echo($variation->variationId); ?>);">
                          <i class="fa fa-times"></i> Reject
                        </a>
                      </div>
                      <div class="col-xs-6 padding-left-0">
                        <a class="mh-button mh-block mh-approve-variation" href="javascript:mh.tenders.variations.addSigModal.open(<?php echo($variation->variationId); ?>);">
                          <i class="fa fa-check"></i> Approve
                        </a>
                      </div>
                    </div>
                  <?php elseif($variation->variationStatusId == 2 || $variation->variationStatusId == 4 || $variation->variationStatusId == 6) : //count($variation->signatureDocuments) > 0) : ?>
                    <?php if(count($variation->signatureDocuments) && end($variation->signatureDocuments)->dateSigned) : ?>
                      <div class="row mh-approved-variation">
                        <div class="col-xs-4 padding-top-5">
                          <i class="fa fa-check"></i>
                          <div class="mh-inline-block text-center text-italic">
                            Approved</br>
                            <?php $date = new DateTime(end($variation->signatureDocuments)->dateSigned); ?>
                            <sup><?php echo $date->format('jS M Y'); ?></sup>
                          </div>
                        </div>
                        <div class="col-xs-8">
                          <div class="mh-button-block mh-button-wrapper text-center">
                            <a class="mh-button" href="javascript:mh.tenders.variations.viewSigModal.open(<?php echo($variation->variationId); ?>);">View Signature<?php if(count($variation->signatureDocuments) > 1) echo 's'; ?></a>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php elseif($variation->variationStatusId == 10): ?>
                    <div class="row mh-rejected-variation">
                      <div class="col-xs-12">
                        <div class="text-center text-italic text-error"><i class="fa fa-times"></i> Rejected</div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Modals -->
	  <div style='display:none'>
      <!-- Approval -->
		  <div id="mh-variation-approve">
        <h2>Approval - <span data-name></span></h2>

        <div class="mh-declaration">
          <?php echo $declaration; ?>
        </div>

        <canvas class="mh-sig-pad"></canvas>

        <div id="cboxFooter">
          <div class="row mh-package-info">
            <div class="col-xs-3 col-sm-2">
              <div class="mh-button-block mh-button-wrapper">
                <a class="mh-button mh-button-sub padding-left-5 padding-right-5" href="javascript:mh.tenders.variations.addSigModal.sigPad.api.clear()">Clear</a>
              </div>
            </div>
            <div class="col-xs-9 col-sm-4 pull-right">
              <div class="mh-button-block mh-button-wrapper">
                <a class="mh-button mh-approve-variation" href="javascript:mh.tenders.variations.addSigModal.send()">Send Approval</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reject -->
		  <div id="mh-variation-reject" class="mh-tender-variations">
        <h2>Reject <span data-name></span></h2>

        <div class="padding-30 text-center">
          Are you sure you want to Reject this Variation?
        </div>

        <div id="cboxFooter" class="text-right">
          <!-- <div class="mh-button-block mh-button-wrapper"> -->
            <a class="mh-button mh-button-sub margin-right-15" href="javascript:jQuery.colorbox.close();">Cancel</a>
          <!-- </div>
          <div class="mh-button-block mh-button-wrapper"> -->
            <a class="mh-button mh-reject-variation" href="javascript:mh.tenders.variations.rejectModal.send()">Reject Variation</a>
          <!-- </div> -->

          <!-- <div class="row mh-package-info">
            <div class="col-xs-3 col-sm-2">
              <div class="mh-button-block mh-button-wrapper">
                <a class="mh-button mh-button-sub padding-left-5 padding-right-5" href="javascript:jQuery.colorbox.close();">Cancel</a>
              </div>
            </div>
            <div class="col-xs-9 col-sm-4 pull-right">
              <div class="mh-button-block mh-button-wrapper">
                <a class="mh-button mh-send-approval" href="javascript:mh.tenders.variations.addSigModal.send()">Reject Variation</a>
              </div>
            </div>
          </div> -->
        </div>
      </div>
      
      <!-- View Signatures -->
		  <div id="mh-variation-sig-view">
        <h2 data-title>&nbsp;</h2>

        <!-- <div class="mh-package-slideshow">
          <div class="slider mh-slideshow-main" data-slideshow-images></div>
        </div> -->
        <div class="mh-slideshow" data-slideshow-images></div>

        <div id="cboxFooter">
          <div class="row mh-package-info">
            <div class="col-xs-3 col-sm-8"></div>
            <div class="col-xs-9 col-sm-4">
              <div class="mh-button-block mh-button-wrapper">
                <a class="mh-button" href="javascript:jQuery.colorbox.close();">Close</a>
              </div>
            </div>
          </div>
        </div>
		  </div>
	  </div>
  </div>
</div>

<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeTenderVariations.js" type="text/javascript"></script>
<script type="text/javascript">
  <?php $xhrAttributes=$this->xhrAttributes(array('variationApprove', 'variationReject', 'clientDocument')); ?>
  jQuery(function ($) {
    _.extend(mh.tenders.variations, {
      xhr: {
        url: '<?php echo $xhrAttributes['url']; ?>',
        actions: <?php echo json_encode($xhrAttributes['actions']); ?>
      },
      
      data: {
        tender: <?php echo json_encode($tender) ?>,
        variations: <?php echo json_encode($variations) ?>
      }
    });
    mh.tenders.variations.init();
  });
</script>