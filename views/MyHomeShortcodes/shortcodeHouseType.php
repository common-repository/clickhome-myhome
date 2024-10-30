<?php
/**
 * The houseType view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeHouseTypeController))
  die;

/**
 * @var ShortcodeHouseTypeController $this
 * @var MyHomeHouseType              $houseType
 */
?>
<div class="mh-wrapper mh-wrapper-house-type">
  <div class="mh-wrapper mh-wrapper-tender-overview">
    <div class="mh-section mh-card mh-tender-overview-details mh-show-info">
      <div class="mh-slideshow-main">
	      <?php foreach($houseType->housedocs as $doc): //echo(json_encode($doc->url, JSON_PRETTY_PRINT)); ?>
          <?php $attachment = myHome()->advertising->docFindAttachment($doc->url); ?>
          <?php if($attachment): //echo(json_encode($attachment, JSON_PRETTY_PRINT)); ?>
			      <div style="background-image: url('<?php echo esc_url(wp_get_attachment_image_src($attachment->ID,'full')[0]); ?>');"></div>
          <?php endif; ?>
        <?php endforeach; ?>
		  </div>
      <div class="mh-slideshow-carousel">
	      <?php foreach($houseType->housedocs as $doc): ?>
          <?php $attachment = myHome()->advertising->docFindAttachment($doc->url); ?>
          <?php if($attachment): ?>
            <div style="background-image: url(<?php echo esc_url(wp_get_attachment_image_src($attachment->ID,'full')[0]); ?>);">
              <!-- <img src="<?php echo esc_url(wp_get_attachment_image_src($attachment->ID,'full')[0]); ?>" /> -->
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      
      <div class="mh-info-overlay">
        <a class="mh-toggle" onclick="jQuery('.mh-tender-overview-details').toggleClass('mh-show-info')"><i class="fa fa-arrow-right" aria-hidden="true"></i></a>
        <?php //foreach($details as $field=>$value): ?>
		      <?php //if($value != '' && $field != 'houseDesign' && $field != 'description'): ?>
			    <!--<div class="mh-row">
			      <div class="mh-cell mh-name"><?php //echo esc_html($fieldNames[$field]); ?></div>
			      <div class="mh-cell mh-value"><?php //echo $value; ?></div>
			    </div>-->
		      <?php //endif; ?>
        <?php //endforeach; ?>

        <?php if(isset($houseType->size)): ?>
          <div class="mh-row">
            <div class="mh-cell mh-name">Size</div>
            <div class="mh-cell mh-value"><?php echo esc_html($houseType->size); ?></div>
          </div>
        <?php endif; ?>
			  <?php if(isset($houseType->bedqty)): ?>
          <div class="mh-row">
            <div class="mh-cell mh-name">Bedrooms</div>
            <div class="mh-cell mh-value"><?php echo esc_html($houseType->bedqty); ?></div>
          </div>
        <?php endif; ?>
			  <?php if(isset($houseType->bathqty)): ?>
          <div class="mh-row">
            <div class="mh-cell mh-name">Bathrooms</div>
            <div class="mh-cell mh-value"><?php echo esc_html($houseType->bathqty); ?></div>
          </div>
        <?php endif; ?>
			  <?php if(isset($houseType->garageqty)): ?>
          <div class="mh-row">
            <div class="mh-cell mh-name">Carparks</div>
            <div class="mh-cell mh-value"><?php echo esc_html($houseType->garageqty); ?></div>
          </div>
        <?php endif; ?>
        <?php if($houseType->hasTheatreRoom()): ?>
			    <div class="mh-row">
			      <div class="mh-cell mh-name">Theatre</div>
			      <div class="mh-cell mh-value"><i class="fa fa-check"></i></div>
			    </div>
        <?php endif; ?>
        <?php if($houseType->hasStudyRoom()): ?>
			    <div class="mh-row">
			      <div class="mh-cell mh-name">Study</div>
			      <div class="mh-cell mh-value"><i class="fa fa-check"></i></div>
			    </div>
        <?php endif; ?>
      </div>
      
      <i class="mh-fullscreen fa fa-arrows-alt" onclick="mh.houseType.slideshows.fullscreen()"></i>
      
      <?php if(myHome()->session->guest()): ?>
        <span class="mh-button-wrapper mh-call-to-action">
          <a class="mh-button" onclick="mh.houseType.expressInterest.open()">
            <i class="fa fa-plus margin-right-10"></i>
            Register Interest
          </a>
        </span>
      <?php endif; ?>
    </div>
  </div>
<!--   <div class="mh-section mh-section-house-type-description-images-wrapper">
    <div class="col-xs-12 col-sm-4 mh-section mh-section-house-type-features">
      <div class="mh-block mh-block-house-type-size"><?php echo esc_html($houseType->size); ?></div>
      <div class="mh-block mh-block-house-type-bedrooms" title="<?php _ex('Bedrooms','House Type','myHome'); ?>"><span
          class="mh-icon">&nbsp;</span> <?php echo (int)$houseType->bedqty; ?></div>
      <div class="mh-block mh-block-house-type-bathrooms" title="<?php _ex('Bathrooms','House Type','myHome'); ?>"><span
          class="mh-icon">&nbsp;</span> <?php echo (float)$houseType->bathqty; ?></div>
      <div class="mh-block mh-block-house-type-garages" title="<?php _ex('Carparks','House Type','myHome'); ?>"><span
          class="mh-icon">&nbsp;</span> <?php echo (int)$houseType->garageqty; ?></div>
      <?php if($houseType->hasTheatreRoom()): ?>
        <div class="mh-block mh-block-house-type-theatre" title="<?php _ex('Theatre','House Type','myHome'); ?>"><span
            class="mh-icon">&nbsp;</span></div>
      <?php endif; ?>
      <?php if($houseType->hasStudyRoom()): ?>
        <div class="mh-block mh-block-house-type-study" title="<?php _ex('Study','House Type','myHome'); ?>"><span
            class="mh-icon">&nbsp;</span></div>
      <?php endif; ?>
    </div>

    <div class="col-xs-12 col-sm-8 mh-section mh-section-house-type-images">
      <div class="mh-block mh-block-house-type-images carousel">
        <?php foreach($houseType->housedocs as $doc): ?>
          <?php
          $attachment=myHome()->advertising->docFindAttachment($doc->url);
          ?>
          <?php if($attachment): ?>
            <?php
            $imageSrc=wp_get_attachment_image_src($attachment->ID,'full');
            ?>
            <div><img class="mh-image mh-image-house-type-image" src="<?php echo esc_url($imageSrc[0]); ?>"></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div> -->

  <?php if($houseType->description): ?>
    <div class="mh-section mh-section-house-type-description"><?php echo nl2br(esc_html($houseType->description)); ?></div>
  <?php endif; ?>
  
  <!-- Plan Options -->
  <?php if($houseType->planoptions): ?>
    <div class="mh-section mh-section-house-type-planoptions margin-top-60">
      <div class="mh-header mh-header-house-type-planoptions"><?php _e('Floor Plan Options','myHome'); ?></div>
      <div class="mh-body mh-body-house-type-planoptions">
        <?php foreach($houseType->planoptions as $planoption): ?>
          <div class="mh-block mh-block-house-type-planoption">
            <div class="mh-block mh-block-house-type-planoption-images-wrapper">
              <div class="mh-block mh-block-house-type-planoption-images carousel">
                <?php // echo json_encode($planoption, JSON_PRETTY_PRINT);
                  if(count($planoption->planoptiondocs)) foreach($planoption->planoptiondocs as $doc) {
                    $attachment = myHome()->advertising->docFindAttachment($doc->url);
                    if($attachment) echo '<a class="mh-thumb plans-lightbox" style="background-image: url(' . esc_url(wp_get_attachment_image_src($attachment->ID, [150,150])[0]) . ');" href="' . esc_url(wp_get_attachment_image_src($attachment->ID, 'full')[0]) . '" title="' . esc_html($planoption->name) . '"></a>';
                  } else echo '<div class="mh-thumb" style="background-image: url(' . MH_URL_IMAGES . '/noPhoto.gif)"></div>';
                ?>
              </div>
              <div class="mh-block mh-block-house-type-planoption-description">
                <div class="mh-row mh-row-house-type-planoption-title"><?php echo esc_html($planoption->name); ?></div>
                <div class="mh-row mh-row-house-type-planoption-description"><?php echo nl2br(esc_html($planoption->description)); ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <!-- Facades -->
  <?php if($houseType->facades): ?>
    <div class="mh-section mh-section-house-type-facades margin-top-30">
      <div class="mh-header mh-header-house-type-facades"><?php _e('Facade Options','myHome'); ?></div>
      <div class="mh-body mh-body-house-type-facades">
        <?php foreach($houseType->facades as $facade): ?>
          <div class="mh-block mh-block-house-type-facade">
            <div class="mh-block mh-block-house-type-facade-images carousel">
              <?php
                if(count($facade->facadedocs)) foreach($facade->facadedocs as $doc) { // echo(json_encode($doc, JSON_PRETTY_PRINT)); 
                  $attachment = myHome()->advertising->docFindAttachment($doc->url); // echo(json_encode($attachment, JSON_PRETTY_PRINT)); 
                  if($attachment) echo '<a class="mh-thumb facade-lightbox" style="background-image: url(' . esc_url(wp_get_attachment_image_src($attachment->ID, [150,150])[0]) . ');" href="' . esc_url(wp_get_attachment_image_src($attachment->ID, 'full')[0]) . '" title="' . esc_html($facade->name) . '"></a>';
                } else echo '<div class="mh-thumb" style="background-image: url(' . MH_URL_IMAGES . '/noPhoto.gif)"></div>';
              ?>
            </div>
            <div class="mh-block mh-block-house-type-facade-title"><?php echo esc_html($facade->name); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if(myHome()->session->guest()): ?>
    <span class="mh-button-wrapper pull-right margin-top-30">
      <a class="mh-button" onclick="mh.houseType.expressInterest.open()">
        <i class="fa fa-plus margin-right-10"></i>
        Register Interest
      </a>
    </span>
  <?php endif; ?>
</div>

<!-- Modals -->
<div style="display:none;">
  <div id="mh-photo-slideshow" class="mh-slideshow">
    <?php foreach($houseType->housedocs as $doc): //echo(json_encode($doc->url, JSON_PRETTY_PRINT)); ?>
      <?php $attachment = myHome()->advertising->docFindAttachment($doc->url); ?>
      <?php if($attachment): // echo(json_encode($attachment, JSON_PRETTY_PRINT)); ?>
        <div><img src="<?php echo esc_url(wp_get_attachment_image_src($attachment->ID,'full')[0]); ?>" /></div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <div id="mh-register-interest" class="mh-wrapper">
    <h2>Register Interest</h2>

    <form class="padding-30" novalidate>
      <input type="hidden" name="houseTypeId" value="<?php echo $houseType->houseid ?>" />
      <input type="hidden" name="callbackUrl" value="<?php echo get_permalink(myHome()->options->getResetPasswordPage()) ?>" />

      <div class="form-row margin-bottom-15">
        <div class="form-group col-sm-2">
          <label for="title">Title</label>
          <select id="title" name="title" class="form-control">
            <option>Mr.</option>
            <option>Mrs.</option>
            <option>Ms.</option>
            <option>Dr.</option>
            <option>Miss</option>
            <option>Sir</option>
            <option>Madam</option>
            <option>Mayor</option>
            <option>President</option>
            <option>None</option>
          </select>
        </div>
        <div class="form-group col-sm-5">
          <label for="firstName">First Name</label>
          <div class="input-icon right">
            <input id="firstName" name="firstName" type="text" class="form-control" required />
            <i class="fa fa-asterisk"></i>
          </div>
          <span class="invalid-feedback">First Name is required</span>
        </div>
        <div class="form-group col-sm-5">
          <label for="lastName">Last Name</label>
          <div class="input-icon right">
            <input id="lastName" name="lastName" type="text" class="form-control" required />
            <i class="fa fa-asterisk"></i>
          </div>
          <span class="invalid-feedback">Last Name is required</span>
        </div>
      </div>
      
      <div class="form-row margin-bottom-15">
        <div class="form-group col-sm-7">
          <label for="email">Email</label>
          <div class="input-icon right">
            <input id="email" name="email" type="text" class="form-control" required />
            <i class="fa fa-asterisk"></i>
          </div>
          <span class="invalid-feedback">A valid E-mail is required</span>
        </div>
        <!-- <div class="form-group col-sm-4">
          <label for="password">Password</label>
          <div class="input-icon right">
            <input id="password" name="password" type="password" class="form-control" required />
            <i class="fa fa-asterisk"></i>
          </div>
          <span class="invalid-feedback">Password is required</span>
        </div> -->
        <div class="form-group col-sm-5">
          <label for="phone">Phone</label>
          <input id="phone" name="phone" type="text" class="form-control" />
        </div>
      </div>

      <div class="mh-loading"></div>
    </form>

    <div class="mh-hide mh-response">
      <i class="fa fa-check"></i>
      <p></p>
    </div>

    <div id="cboxFooter" class="mh-bottom mh-flex-row">
      <div class="mh-flex-1 error-text"></div>
      <a class="mh-button" onclick="mh.houseType.expressInterest.submit()">Submit</a>
    </div>
  </div>
</div>

<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeHouseType.js" type="text/javascript"></script>
<script type="text/javascript">
  jQuery(function($){
    mh.houseType.init();
  });
</script>
