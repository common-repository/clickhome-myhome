<?php
/**
 * The ResetPassword view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.5 before this version, password recovery was displayed using [MyHome.Login lostpassword=recovery]
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeResetPasswordController))
  die;

/**
 * @var ShortcodeResetPasswordController $this
 */

//$error=$this->restoreVar('error'); //echo 'ERRROR:' . $error;
$loginFormAttributes=myHome()->adminPostHandler->formAttributes('login','POST',get_permalink(myHome()->options->getMainPage()));

?>


<div class="mh-wrapper mh-wrapper-login">
	<div class="mh-header">
		<h2>Reset Password</h2>
	</div>
	<form class="mh-reset-password" method="POST" onsubmit="return mh.resetPassword.submit();">
		<div class="mh-body">
			<?php /*if($error): ?>
				<div class="mh-error"><?php echo esc_html($error); ?></div>
			<?php endif;*/ ?>

      <div class="mh-row">
        <div class="mh-cell">
          <input maxlength="20" name="myHomePassword" required placeholder="<?php _e('Password','myHome'); ?>"
              type="password" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="1" onkeyup="mh.resetPassword.isMatch()" />
        </div>
      </div>
			<div class="mh-row">
				<div class="mh-cell">
					<input maxlength="20" name="myHomePassword2" required placeholder="<?php _e('Verify Password','myHome'); ?>"
						   type="password" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="1" onkeyup="mh.resetPassword.isMatch()" />
					<i class="fa fa-times"></i>
				</div>
			</div>

			<br/>
			<div class="mh-row mh-row-login-login-button">
				<div class="mh-cell mh-cell-login-login-button">
					<span class="mh-button-wrapper mh-button-block"><button class="mh-button mh-button-login-login-submit mh-show-loading" type="submit" tabindex="1" /><?php _ex('Set Password','Login Form','myHome'); ?></button></span>
				</div>
			</div>
		</div>
	</form>

	<div class="mh-login-loading"></div>
</div>

<form id="mh-login" action="<?php $this->appendFormUrl($loginFormAttributes); ?>" method="POST" style="display:none">
	<?php $this->appendFormParams($loginFormAttributes,4); ?>
	<input maxlength="20" name="myHomeJobNumber" required type="text" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="-1" />
	<input maxlength="50" name="myHomeUsername" required type="text" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="-1" />
	<input maxlength="20" name="myHomePassword" required type="password" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="-1" />
</form>

<!-- Scripts -->
<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeResetPassword.js" type="text/javascript"></script>
<script>
  jQuery(function ($) {
    _.extend(mh.resetPassword, {
      data: {
        token: '<?php echo $_GET['token'] ?>',
      },
    });
    //mh.resetPassword.init();
  });
</script>