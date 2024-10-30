<?php
/**
 * The loginHelp view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.5 before this version, password recovery was displayed using [MyHome.Login lostpassword=recovery]
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeLoginHelpController))
  die;

/**
 * @var ShortcodeLoginHelpController $this
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('recovery','POST');
$captchaUrl=esc_url(MH_URL_VENDOR.'/cool-php-captcha/captcha.php');

$message=$this->restoreVar('recoveryMessage');
$error=$this->restoreVar('recoveryError');
?>
<div class="mh-wrapper">
  <form action="<?php $this->appendFormUrl($formAttributes); ?>" class="mh-section mh-loginhelp" method="POST">
    <?php
    $this->appendFormParams($formAttributes,4);
    ?>
    <div class="mh-body mh-body-loginhelp">
		<h4><?php _e('Forgotten, lost or never had a username &amp; password?','myHome'); ?></h4>
		<div class="mh-text mh-loginhelp-instructions">
		  <?php _e('Fill in as much information as possible below and we will contact you within 48 hours...','myHome'); ?>
		</div>
		<?php if($message): ?>
		  <div class="mh-message mh-message-loginhelp"><?php echo esc_html($message); ?></div>
		<?php elseif($error): ?>
		  <div class="mh-error mh-error-loginhelp"><?php echo esc_html($error); ?></div>
		<?php endif; ?>

      <div class="mh-row mh-loginhelp-username">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Username','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input maxlength="50" name="myHomeUsername" type="text">
        </div>
      </div>
      <div class="mh-row mh-loginhelp-job-number">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Job Number','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input maxlength="20" name="myHomeJobNumber" type="text">
        </div>
      </div>
      <div class="mh-row mh-loginhelp-surname">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Surname','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input maxlength="50" name="myHomeSurname" type="text">
        </div>
      </div>
      <div class="mh-row mh-loginhelp-street-number">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Street Number','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input class="mh-input-small" maxlength="10"
            name="myHomeStreetNumber" type="text">
        </div>
      </div>
      <div class="mh-row mh-loginhelp-street-name">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Street Name','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input maxlength="100" name="myHomeStreetName" type="text">
        </div>
      </div>
      <div class="mh-row mh-loginhelp-suburb">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Suburb','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input maxlength="100" name="myHomeSuburb" type="text">
        </div>
      </div>
      <div class="mh-row mh-loginhelp-postcode">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Postcode','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input class="mh-input-small" maxlength="10"
            name="myHomePostcode" type="text">
        </div>
      </div><br /><br />

      <h4><?php _e('How can we contact you?','myHome'); ?></h4><br/><br/>
      <div class="mh-row mh-loginhelp-name">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Name','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input"><input maxlength="100" name="myHomeName" type="text"></div>
      </div>
      <div class="mh-row mh-loginhelp-phone">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Phone','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input"><input maxlength="20" name="myHomePhone" type="text"></div>
      </div>
      <div class="mh-row mh-loginhelp-email">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Email','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <input maxlength="50" name="myHomeEmail" type="email">

          <div class="mh-info">
            <?php _e('If we can match your details and you provide the email address, then we can send a password reset link instantly.',
              'myHome'); ?>
          </div>
        </div>
      </div>
      <div class="mh-row mh-loginhelp-captcha">
        <div class="mh-cell mh-loginhelp-field"><?php _e('Type the above word','myHome'); ?></div>
        <div class="mh-cell mh-loginhelp-input">
          <img class="mh-image mh-image-loginhelp-captcha" id="imgMyHomeCaptcha"
            src="<?php echo $captchaUrl; ?>">
          <input autocomplete="off" maxlength="20" name="myHomeCaptcha" type="text">
          <a class="mh-link mh-link-loginhelp-captcha-change" id="aMyHomeChangeCaptcha"
            href="javascript:void(0);"><?php _e('Not readable? Change text','myHome'); ?></a>
        </div>
      </div>
    </div>
    <div class="mh-row mh-loginhelp-button">
      <button class="mh-button" type="submit">
        <?php _ex('Submit','Recovery Form', 'myHome'); ?>
      </button>
    </div>
  </form>
  <script type="text/javascript">
    jQuery(function($){
      $("#aMyHomeChangeCaptcha").click(function(){
        $("#imgMyHomeCaptcha").attr("src","<?php echo $captchaUrl; ?>?"+Math.random());
      });
    });
  </script>

  <div class="mh-login-loading"></div>

  <script type="text/javascript">
    jQuery(function($){
      jQuery('.mh-show-loading').on('click',function(){
        var $loginWrapper=jQuery('.mh-wrapper-login');
        var willValidate=true;
        $loginWrapper.find('input[required]').each(function(){
          if($(this).val()=='') willValidate=false;
          //console.log($(this).val());
        });

        if(willValidate){
          $loginWrapper.addClass('mh-logging-in');
        }
      });

      if(typeof($.fn.validate==="function"))
        $(".mh-wrapper-login").validate(
          {
            message:"<?php _e('Please fill in all required fields','myHome'); ?>",
            feedbackClass:"mh-error"
          });
    });
  </script>
</div>
