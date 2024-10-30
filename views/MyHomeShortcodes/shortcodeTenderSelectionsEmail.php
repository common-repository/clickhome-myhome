<?php
/**
 * The tenderSelectionsEmail view
 *
 * @selection    MyHome
 * @subselection ViewsShortcodes
 * @since      1.5.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeTenderSelectionsEmailController))
  die;

/**
 * @var ShortcodeTenderSelectionsEmailController $this
 * @var string                                  $content
 * @var int                                     $tenderId
 */

// PHP tags are hard against anchor tag to prevent leading/trailing spaces
?><a href="javascript:mh.tenders.selectionsEmail.confirmModal.open();"><?php echo $atts['content']; ?></a><div style='display:none'>
  <!-- Confirm -->
  <div id="mh-tender-selections-email-confirm">
    <div>
    <h2>Confirm Email</h2>

    <p class="mh-prompt">
      Send a report of your selections<br/>to your email?
    </p>

    <div id="cboxFooter" class="row">
      <div class="col-xs-12 text-right">
        <label class="mh-button-wrapper">
          <a class="mh-button" href="javascript:mh.tenders.selectionsEmail.sendReport();">Send Report</a>
        </label>
      </div>
    </div>
</div>
  </div>

  <!-- Response -->
  <div id="mh-tender-selections-email-response">
    <h2>Email Selection Report</h2>
    <div class="mh-prompt mh-waiting">
      <i class="mh-loading"></i><br/><br/>
      Please wait while your selections report is prepared...<br/><br/>
      <i class="fa fa-load"></i>
    </div>
    <div class="mh-prompt mh-success mh-hide">
        <i class="fa fa-check"></i><br/>
        <strong>Your selection report has been sent.</strong><br/><br/>
        It should arrive in your inbox in the near future.
    </div>
    <div class="mh-prompt mh-error mh-hide">
        Unfortunately we encountered an error while emailing your selections report.
    </div>
  </div>

  <script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeTenderSelectionsEmail.js" type="text/javascript"></script>
  <script>
    <?php
      $xhrAttributes = $this->xhrAttributes('emailMySelections');
      $xhrAttributes['params']['myHomeTenderId'] = $tenderId;
      //var_dump($xhrAttributes);
    ?>
    jQuery(function ($) {
      _.extend(mh.tenders.selectionsEmail, {
        xhr: {
          url: '<?php echo $xhrAttributes['url']; ?>',
          actions: <?php echo json_encode($xhrAttributes['actions']); ?>
        },
      
        vars: {
          tenderId: <?php echo $tenderId; ?>
        }
      });
    });
  </script>
</div>