<?php
/**
 * The login.jobsList subview
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
 * @var ShortcodeLoginController              $this
 * @var string                                $email
 * @var Facebook\GraphNodes\GraphPicture|null $picture
 * @var string[]                              $jobsList
 * @var string                                $loginRedirect
 */

$formAttributesJobsList = myHome()->adminPostHandler->formAttributes('jobs','POST', $loginRedirect);
$error=$this->restoreVar('error');
?>

<div class="mh-wrapper mh-wrapper-login">
  <form action="<?php $this->appendFormUrl($formAttributesJobsList); ?>" class="mh-section-login-jobs" method="POST">
    <?php
      $this->appendFormParams($formAttributesJobsList,4);
    ?>
    <?php if($error): ?>
      <div class="mh-error mh-error-login-jobs"><?php echo esc_html($error); ?></div>
    <?php endif; ?>
    <div class="mh-body mh-login-jobs">
      <div class="mh-login-jobs-email">
        <div class="mh-login-jobs-field"><?php _e('Authenticated using Facebook:','myHome'); ?></div>
        <div class="mh-login-jobs-input">
          <?php if($picture): ?>
            <?php //if($picture->getHeight() && $picture->getWidth()): ?>
              <?php echo $this->profilePic(['profilePhoto' => ['medium' => $picture->getUrl()]]); ?>
              <!-- <img src="<?php echo esc_url($picture->getUrl()); ?>" style="<?php printf('height:%upx;width:%upx;', $picture->getHeight(),$picture->getWidth()); ?>"> -->
            <?php /*else: ?>
              <img src="<?php echo esc_url($picture->getUrl()); ?>">
            <?php endif;*/ ?>
          <?php endif; ?>
          <strong class="margin-left-10"><?php echo esc_attr($email); ?></strong>
          <input name="myHomeEmail" type="hidden" value="<?php echo esc_attr($email); ?>">
        </div>
      </div>
      <div class="mh-login-jobs-number">
        <div class="mh-login-jobs-field"><?php _e('Job Number:','myHome'); ?></div>
        <div class="mh-login-jobs-input">
          <select name="myHomeJobNumber" required size="1">
            <?php foreach($jobsList as $job): ?>
              <option><?php echo $job; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
    <div class="mh-footer mh-footer-login">
      <button class="mh-button mh-button-login-submit" type="submit"><?php _e('Select Job', 'myHome'); ?></button>
    </div>
  </form>
</div>
