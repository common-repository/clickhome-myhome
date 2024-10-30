<?php
/**
 * The login view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeLoginController))
  die;

/**
 * @var ShortcodeLoginController $this
 * @var string                   $attFacebook
 * @var string                   $loginRedirect
 * @var string                   $facebookAppId
 * @var string                   $facebookError
 */

$loginFormAttributes=myHome()->adminPostHandler->formAttributes('login','POST',$loginRedirect);

$facebook=$attFacebook==='yes';

$error=$this->restoreVar('error');
$job=$this->restoreVar('job','');
$username=$this->restoreVar('username','');

// If there is no error but a Facebook error, display it
// Also, if $facebookError is not empty, the page doesn't reload if a Facebook session is detected - this prevents
// infinite reload loops if the job list associated with the email is empty
if(!$error&&$facebookError)
  $error=$facebookError;
?>
<div class="mh-wrapper mh-wrapper-login">
	<div class="mh-header">
		<h2>Log In</h2>
	</div>
	<form action="<?php $this->appendFormUrl($loginFormAttributes); ?>" class="mh-section mh-section-login-login" method="POST">
		<?php
		  $this->appendFormParams($loginFormAttributes,4);
		?>
		<?php if($error): ?>
		<div class="mh-error"><?php echo esc_html($error); ?></div>
		<?php endif; ?>
		<div class="mh-body mh-body-login-login">
			<div class="mh-row mh-row-login-login-job-number">
				<!--<div class="mh-cell mh-cell-login-login-field"><?php _e('Job Number:','myHome'); ?></div>-->
				<div class="mh-cell mh-cell-login-login-input">
					<input maxlength="20" name="myHomeJobNumber" required type="text" placeholder="<?php _e('Job Number','myHome'); ?>" title="Job Number"
						   value="<?php echo esc_attr($job); ?>" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="1" />
				</div>
			</div>
			<div class="mh-row mh-row-login-login-username">
				<!--<div class="mh-cell mh-cell-login-login-field"><?php _e('Username:','myHome'); ?></div>-->
				<div class="mh-cell mh-cell-login-login-input">
					<input maxlength="50" name="myHomeUsername" required type="text" placeholder="<?php _e('Username','myHome'); ?>" title="Username"
						   value="<?php echo esc_attr($username); ?>" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="1" />
				</div>
			</div>
			<div class="mh-row mh-row-login-login-password">
				<!--<div class="mh-cell mh-cell-login-login-field"><?php _e('Password:','myHome'); ?></div>-->
				<div class="mh-cell mh-cell-login-login-input">
					<input maxlength="20" name="myHomePassword" required placeholder="<?php _e('Password','myHome'); ?>" title="Password"
						   type="password" autocorrect="off" autocapitalize="off" spellcheck="false" tabindex="1" />
				</div>
			</div>
			<a class="mh-login-loginhelp" href="javascript:mh.login.forgotPassword.open()" tabindex="2"><?php _ex('Trouble logging in?','Login Form','myHome'); ?></a>
			<br/>
		<!--</div>
		<div class="mh-body mh-footer-login-login">-->
			<div class="mh-row mh-row-login-login-button">
				<div class="mh-cell mh-cell-login-login-button">
					<span class="mh-button-wrapper mh-button-block"><button class="mh-button mh-button-login-login-submit mh-show-loading" type="submit" tabindex="1" /><?php _ex('Login','Login Form','myHome'); ?></button></span>
				</div>
			</div>
		</div>
	</form>

	<!-- If [MyHome.Login facebook=yes] aatr -->
	<?php if($facebook): ?>
		<div class="mh-footer mh-section-login-facebook">
			<a class="btn btn-block myhome_facebook_btn" onclick="loginWithFacebook()">
				<?php _e('Continue with Facebook', 'myHome'); ?>
			</a>

			<div id="fb-root"></div>
			<script type="text/javascript">
				window.fbAsyncInit = function() {
					FB.init({
							status: true,
							cookie: true,
							appId: "<?php echo $facebookAppId; ?>",
							xfbml: true,
							version: "v2.10"
					});
					/*FB.Event.subscribe("auth.login",function(res) { console.log('auth.login subscription changed', res);
						//location.reload();
					});*/
				};

				function loginWithFacebook() {
					if(window.FB) {
						FB.login(
							function(res) {
								if(res.status === "connected") { // console.log('loggedIn', res);
									//location.reload();
									//jQuery('.mh-section-login-facebook form').submit();
									location.href = location.href = '?facebook=true';
								}
							}, {
								scope: 'public_profile,email'
							}
						);
					} else {
						console.log('FB not yet loaded, re-trying...');
						setTimeout(loginWithFacebook, 1000);
					}
				}

				(function(d, s, id){
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) {return;}
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/en_US/sdk.js";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));
			</script>
		</div>
	<?php endif; ?>

	<div class="mh-login-loading"></div>

	<!-- Modals -->
	<div style="display:none;">
		<div id="mh-help-logging-in" class="mh-wrapper">
			<h2>Help Logging In</h2>

			<form class="padding-30" novalidate>
				<input type="hidden" name="callbackUrl" value="<?php echo get_permalink(myHome()->options->getResetPasswordPage()) ?>" />

				<div class="form-row margin-bottom-15">
					<div class="form-group margin-bottom-15">
						<label for="email">Job Number</label>
						<div class="input-icon right">
							<input id="job" name="job" type="text" class="form-control" required />
							<i class="fa fa-asterisk"></i>
						</div>
						<span class="invalid-feedback">A valid Job Number is required</span>
					</div>
					<div class="form-group">
						<label for="email">Username</label>
						<div class="input-icon right">
							<input id="username" name="username" type="text" class="form-control" required />
							<i class="fa fa-asterisk"></i>
						</div>
						<span class="invalid-feedback">A valid Username is required</span>
					</div>
				</div>

				<div class="mh-loading"></div>
			</form>

			<div class="mh-hide mh-response">
				<i class="fa fa-check"></i>
				<p></p>
			</div>

			<div id="cboxFooter" class="mh-bottom">
				<div class="error-text text-center margin-bottom-15" style="display: none;"></div>
				<div class="text-right">
					<a class="mh-button" onclick="mh.login.forgotPassword.submit()">Reset Password</a>
				</div>
			</div>
		</div>
	</div>

	<script src="<?php echo MH_URL_SCRIPTS; ?>/shortcodeLogin.js" type="text/javascript"></script>
	<script type="text/javascript">
		jQuery(function($){
			//mh.login.init();

			$('.mh-show-loading').on('click', function() {
				var $loginWrapper = jQuery('.mh-wrapper-login');
				var willValidate = true;
				$loginWrapper.find('input[required]').each(function() {
					if($(this).val() == '') willValidate = false;
					//console.log($(this).val());
				});

				if( willValidate) {
					$loginWrapper.addClass('mh-logging-in');
				}
			});

			//if(typeof($.fn.validate==="function"))
			//  $(".mh-wrapper-login").validate({
			//	  message:"<?php _e('Please fill in all required fields','myHome'); ?>",
			//	  feedbackClass:"mh-error"
			//  });
		});
	</script>
</div>
