<?php
/**
 * The adminPageSettings view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageSettingsController))
  die;

/**
 * @var AdminPageSettingsController $this
 * @var string                      $endpoint
 * @var int                         $loginPage
 * @var string                      $logoffMenuLocation
 * @var string                      $logoffOptionName
 * @var int                         $mainPage
 * @var string                      $mainLogo
 * @var string                      $bgImage
 * @var bool                        $logEnabled
 * @var int                         $logLevel
 * @var int                         $logMethod
 * @var string                      $contactApiKey
 * @var string                      $advertisingApiKey
 * @var int                         $suggestedLoginPage
 * @var mixed[]                     $requirements
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('adminSettings','POST');

$saved=$this->restoreVar('saved');
$error=$this->restoreVar('error');
?>
<div class="wrap">
  <?php
  $this->writeHeaderTabs();
  ?>
  <?php if($saved): ?>
    <div class="updated"><p><?php _e('Settings updated successfully.','myHome'); ?></p></div>
  <?php elseif($error): ?>
    <div class="error"><p><?php printf(__('Error: %s.','myHome'),esc_html($error)); ?></p></div>
  <?php endif; ?>
  <form action="<?php $this->appendFormUrl($formAttributes); ?>" id="formMyHomeAdminPage" method="POST">
    <?php
    $this->appendFormParams($formAttributes,4);
    ?>
    <table id="tableMyHomeAdminPage">
      <tbody>
      <tr>
        <td class="table-row-title"><?php _e('API Endpoints Base URL','myHome'); ?></td>
        <td><input autofocus maxlength="255" name="myHomeApiEndpoint"
            placeholder="<?php _e('Example: http://test.clickhome.com.au/','myHome'); ?>" type="url"
            value="<?php echo esc_attr($endpoint); ?>"></td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('Login Page','myHome'); ?></td>
        <td>
          <?php
          wp_dropdown_pages(['depth'=>0,
            'name'=>'myHomeLoginPage',
            'id'=>'selectMyHomeLoginPage',
            'show_option_none'=>__('Select a Page...','myHome'),
            'option_none_value'=>'0',
            'selected'=>$loginPage]);
          ?>
          <button class="button" id="buttonMyHomeAutodetectLogin" type="button"><?php _ex('Autodetect', 'Login page option','myHome'); ?></button>
        </td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('Forgot Password Page','myHome'); ?></td>
        <td>
          <?php 
          wp_dropdown_pages(['depth'=>0,
            'name'=>'myHomeResetPasswordPage',
            'id'=>'selectMyHomeResetPasswordPage',
            'show_option_none'=>__('Select a Page...','myHome'),
            'option_none_value'=>'0',
            'selected'=>$resetPasswordPage]);
          ?>
          <button class="button" id="buttonMyHomeAutodetectResetPassword" type="button"><?php _ex('Autodetect', 'Reset password page option','myHome'); ?></button>
        </td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('Logoff Option','myHome'); ?></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('If no Logoff option is added to a menu, a Logoff button should be displayed instead using the Logoff widget or the [MyHome.Logoff] shortcode.',
            'myHome'); ?></td>
      </tr>
      <tr>
        <td class="table-row-title option-level-2"><?php _ex('Menu Location',
            'Settings admin page (logoff option setting)','myHome'); ?></td>
        <td>
          <select name="myHomeLogoffMenuLocation" size="1">
            <option value=""><?php _e('Select a Menu Location...','myHome'); ?></option>
            <?php
            $locations=get_registered_nav_menus();

            foreach($locations as $slug=>$name)
              printf("              <option%s value=\"%s\">%s%s</option>\n",$slug===$logoffMenuLocation?' selected':'',
                esc_attr($slug),$name,has_nav_menu($slug)?'':' (No menu assigned)');
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <td class="table-row-title option-level-2"><?php _ex('Option Name',
            'Settings admin page (logoff option setting)','myHome'); ?></td>
        <td><input maxlength="50" name="myHomeLogoffOptionName" placeholder="<?php _e('Example: Logoff','myHome'); ?>"
            type="text" value="<?php echo esc_attr($logoffOptionName); ?>"></td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('Main Page','myHome'); ?></td>
        <td>
          <?php
			      wp_dropdown_pages([
				      'depth'=>3,
				      'name'=>'myHomeMainPage',
				      'show_option_none'=>__('Select a Page...','myHome'),
				      'option_none_value'=>'0',
				      'selected'=>$mainPage,
				      //'exclude'=>$loginPage
			      ]); // The login page can't be the main page
          ?>
        </td>
      </tr>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('After logging in, the user will be redirected to the Main Page when no redirect URL is given. If no Main Page is set, the plugin will redirect to the home page (/).',
            'myHome'); ?></td>
      </tr>
      <tr>
        <td class="table-row-title">
			<?php _e('Main Logo','myHome'); ?>
        </td>
        <td>
			    <input name="myHomeMainLogo" placeholder="" type="text" value="<?php echo esc_attr($mainLogo); ?>">
        </td>
      </tr>
      <tr>
        <td class="table-row-title">
			<?php _e('Background Image','myHome'); ?>
        </td>
        <td>
			    <input name="myHomeBgImage" placeholder="" type="text" value="<?php echo esc_attr($bgImage); ?>">
        </td>
      </tr>
      <tr>
        <td class="table-row-title" colspan="2"><label><input<?php echo $logEnabled?' checked':''; ?> id="inputMyHomeLogEnabled" name="myHomeLogEnabled" type="checkbox"><?php _e('Log Enabled','myHome'); ?></label>
        </td>
      </tr>
      <tr>
        <td class="table-row-title" colspan="2">
        </td>
      </tr>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('Disabling logging does not remove the log. To clear the log, go to the Log tab after enabling this option.',
            'myHome'); ?></td>
      </tr>
      <tr class="log-enabled">
        <td class="table-row-title option-level-2"><?php _e('Logging Level','myHome'); ?></td>
        <td>
          <select name="myHomeLogLevel" size="1">
            <?php
            $logLevels=[MyHomeOptions::$LOG_LEVEL_ERROR=>_x('Error','Settings admin page','myHome'),
              MyHomeOptions::$LOG_LEVEL_INFO=>_x('Info','Settings admin page','myHome')];

            foreach($logLevels as $logLevelSelect=>$name)
              printf("              <option%s value=\"%s\">%s</option>\n",$logLevelSelect===$logLevel?' selected':'',
                esc_attr($logLevelSelect),$name);
            ?>
          </select>
        </td>
      </tr>
      <tr class="log-enabled">
        <td class="table-row-title option-level-2"><?php _e('Logging Method','myHome'); ?></td>
        <td>
          <select name="myHomeLogMethod" size="1">
            <?php
            $logMethods=[MyHomeOptions::$LOG_METHOD_FILE=>_x('File','Settings admin page','myHome'),
              MyHomeOptions::$LOG_METHOD_OPTION=>_x('WordPress Option','Settings admin page','myHome')];

            foreach($logMethods as $logMethodSelect=>$name)
              printf("              <option%s value=\"%s\">%s</option>\n",$logMethodSelect===$logMethod?' selected':'',
                esc_attr($logMethodSelect),$name);
            ?>
          </select>
        </td>
      </tr>
      <tr class="log-enabled">
        <td class="table-row-title option-level-2 tip"
          colspan="2"><?php _e('Choose WordPress Option if your WordPress installation doesn\'t have writing permissions on the plugin directory.',
            'myHome'); ?></td>
      </tr>
      <tr class="log-enabled">
        <td class="table-row-title option-level-2"><?php _e('Log Responses','myHome'); ?></td>
        <td>
          <input<?php echo $logResponses?' checked':''; ?> id="inputMyHomeLogResponses" name="myHomeLogResponses" type="checkbox"><?php _e('Log Responses','myHome'); ?>
        </td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('WebLeads API Key','myHome'); ?></td>
        <td><input maxlength="36" name="myHomeContactApiKey"
            placeholder="<?php _e('Example: 12345678-1234-1234-1234-123456789012','myHome'); ?>" type="text"
            value="<?php echo esc_attr($contactApiKey); ?>"></td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('MyHome API Key','myHome'); ?></td>
        <td><input maxlength="36" name="myHomeAdvertisingApiKey"
            placeholder="<?php _e('Example: 12345678-1234-1234-1234-123456789012','myHome'); ?>" type="text"
            value="<?php echo esc_attr($advertisingApiKey); ?>"></td>
      </tr>
      <!--<tr>
        <td class="table-row-title option-level-2 tip"
          colspan="2"><?php _e('Please note that this key is not related to the Facebook App. To set up the Facebook App, go to the Facebook tab.','myHome'); ?></td>
      </tr>-->
      <tr>
        <td colspan="2">
          <button class="button button-primary" id="buttonMyHomeSaveChanges" type="submit"><?php _e('Save Changes',
              'myHome'); ?></button>
        </td>
      </tr>
      </tbody>
    </table>
  </form>
  <hr>
  <div id="divRequirements">
    <h2>Requirements</h2>

    <div class="requirement requirement-title">
      <div><?php _e('Product/Library','myHome'); ?></div>
      <div><?php _e('Version/Detected','myHome'); ?></div>
      <div><?php _e('Status','myHome'); ?></div>
      <div><?php _e('Comment','myHome'); ?></div>
    </div>
    <?php foreach($requirements as $requirement): ?>
      <div class="requirement">
        <div class="requirement-product"><?php echo esc_html($requirement['product']); ?></div>
        <div class="requirement-version"><?php echo esc_html($requirement['version']); ?></div>
        <div class="requirement-status status-<?php echo $requirement['status']?'ok':
          'error'; ?>"><?php echo $requirement['status']?'OK':'Error'; ?></div>
        <div class="requirement-comment"><?php echo esc_html($requirement['comment']); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <hr>
  <p><?php printf(__('ClickHome.MyHome version %s','myHome'),MH_VERSION); ?></p>
</div>
<script type="text/javascript">
  jQuery(function($){
    $("#formMyHomeAdminPage").submit(function(){
      $("#buttonMyHomeSaveChanges")
        .prop("disabled",true)
        .empty()
        .append("<?php _e('Saving...','myHome'); ?>");
    });

    $("#buttonMyHomeAutodetectLogin").click(function(){
      var suggestedLoginPage=<?php echo $suggestedLoginPage; ?>;
      if(suggestedLoginPage!==0)
        $("#selectMyHomeLoginPage").val(suggestedLoginPage);
      else
        alert("<?php _e('A valid Login Page could not be found.','myHome'); ?>");
    })
    .change(function(){
      $("#formMyHomeAdminPage").find(".log-enabled").toggle($(this).is(":checked"));
    })
    .find(".log-enabled").toggle($("#inputMyHomeLogEnabled").is(":checked"));


    $("#buttonMyHomeAutodetectResetPassword").click(function(){
      var suggestedResetPasswordPage=<?php echo $suggestedResetPasswordPage; ?>;
      if(suggestedResetPasswordPage!==0)
        $("#selectMyHomeResetPasswordPage").val(suggestedResetPasswordPage);
      else
        alert("<?php _e('A valid Reset Password Page could not be found.','myHome'); ?>");
    });
  });
</script>
