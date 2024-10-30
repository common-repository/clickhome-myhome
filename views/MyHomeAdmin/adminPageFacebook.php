<?php
/**
 * The adminPageFacebook view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageFacebookController))
  die;

/**
 * @var AdminPageFacebookController        $this
 * @var string                             $appId
 * @var string                             $appSecret
 * @var Facebook\GraphNodes\GraphNode|null $appDetails
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('adminFacebook','POST');

$saved=$this->restoreVar('saved');

$webSupported=false;

if($appDetails!==null)
  foreach($appDetails['supported_platforms'] as $platform)
    if($platform==='WEB')
      $webSupported=true;
?>
<div class="wrap">
  <?php
  $this->writeHeaderTabs();
  ?>
  <?php if($saved): ?>
    <div class="updated"><p><?php _e('Settings updated successfully.','myHome'); ?></p></div>
  <?php endif; ?>
  <form action="<?php $this->appendFormUrl($formAttributes); ?>" id="formMyHomeAdminPage" method="POST">
    <?php
    $this->appendFormParams($formAttributes,4);
    ?>
    <table id="tableMyHomeAdminPage">
      <tbody>
      <tr>
        <td class="table-row-title"><?php _e('App ID','myHome'); ?></td>
        <td><input autofocus maxlength="50" name="myHomeAppId"
            placeholder="<?php _e('Example: 123456789012345','myHome'); ?>" type="text"
            value="<?php echo esc_attr($appId); ?>"></td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('App Secret','myHome'); ?></td>
        <td><input maxlength="100" name="myHomeAppSecret"
            placeholder="<?php _e('Example: 0123456789abcdef0123456789abcdef','myHome'); ?>" type="text"
            value="<?php echo esc_attr($appSecret); ?>"></td>
      </tr>
      <?php if($appId&&$appSecret): ?>
        <tr>
          <td class="table-row-title"><?php _e('App Details','myHome'); ?></td>
          <?php if($appDetails!==null): ?>
            <td class="with-list">
              <ul>
                <li>App Name: <b><?php echo $appDetails['name']; ?></b></li>
                <li>Contact Email: <b><?php echo $appDetails['contact_email']; ?></b></li>
                <li>Web Platform?: <b><?php echo $webSupported?'Yes':'<strong style="color:red">No</strong>'; ?></b></li>
                <li>Website URL: <b><?php printf('<a href="%s" target="_blank">%s</a>',
                      esc_url($appDetails['website_url']),$appDetails['website_url']); ?></b></li>
              </ul>
            </td>
          <?php else: ?>
            <td><?php _e('The App Details could not be retrieved. Check the App ID and App Secret and try again.',
                'myHome'); ?></td>
          <?php endif; ?>
        </tr>
        <?php if($appDetails!==null): ?>
          <tr>
            <td class="table-row-title tip"
              colspan="2"><?php _e('Don\'t forget to make your App public if you haven\'t done it yet.',
                'myHome'); ?></td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>
      <?php if($appDetails===null): ?>
        <tr>
          <td class="table-row-title"><?php _e('Set Up your Facebook App','myHome'); ?></td>
          <td class="with-list">
            <div>
              <p><?php _e('To create your Facebook App, you must follow these instructions:','myHome'); ?></p>
              <ul>
                <li>Go to <a href="https://developers.facebook.com/"
                    target="_blank">https://developers.facebook.com/</a>.
                </li>
                <li>Go to <b>My Apps</b>.</li>
                <li>Log in with your Facebook account.</li>
                <li>If you haven't done it yet, click <b>Register Now</b>.</li>
                <li>Once you have registered as a developer, click <b>+ Add a New App</b>.</li>
                <li>Select <b>Website</b> as the platform for the new App.</li>
                <li>Enter the App name (e.g. "MyHome") and click <b>Create New Facebook App ID</b>.</li>
                <li>Choose <b>Apps for Pages</b> under the <b>Category</b> dropdown and click <b>Create App ID</b>.</li>
                <li>You'll see the Quick Start screen, with four steps. Click <b>Skip Quick Start</b> on the top right
                  corner.
                </li>
                <li>You should now see the App dashboard, with several setting tabs on the left menu. You need to copy
                  the
                  <b>App ID</b> and the <b>App Secret</b> values to the form above. (To see the App Secret, you must
                  click
                  the <b>Show</b> button next to it.)
                </li>
                <li>Click <b>Settings</b> on the left. Then, enter a contact email and click <b>+ Add Platform</b>,
                  choose <b>Website</b> and enter the URL of the website where the ClickHome.MyHome plugin is installed
                  (e.g. "<?php echo home_url() ?>"). Click <b>Save Changes</b>.
                </li>
                <li>Click <b>Status & Review</b> on the left. Select <b>YES</b> to make the App public and click
                  <b>Confirm</b>.
                </li>
              </ul>
            </div>
          </td>
        </tr>
      <?php endif; ?>
      <tr>
        <td colspan="2">
          <button class="button button-primary" id="buttonMyHomeSaveChanges" type="submit"><?php _e('Save Changes',
              'myHome'); ?></button>
        </td>
      </tr>
      </tbody>
    </table>
  </form>
</div>
<script type="text/javascript">
  jQuery(function($){
  $("#formMyHomeAdminPage")
  .submit(function(){
  $("#buttonMyHomeSaveChanges")
  .prop("disabled",true)
  .empty()
  .append("<?php _e('Saving...','myHome'); ?>");
  });
  });
</script>
