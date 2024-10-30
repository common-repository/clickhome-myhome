<?php
/**
 * The adminPageMaintenance view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageMaintenanceController))
  die;

/**
 * @var AdminPageMaintenanceController $this
 * @var int[]                          $maintenancePages
 * @var int[]                          $suggestedMaintenancePages
 * @var float                          $maintenanceMaxFileSize
 * @var mixed[]                        $maintenancePagesDetails
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('adminMaintenance','POST');

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

    $requiredString=' <span class="required">'.__('(Required)','myHome').'</span>';
    ?>
    <table id="tableMyHomeAdminPage">
      <tbody>
      <tr>
        <td class="table-row-title"><?php _e('Maintenance Pages','myHome'); ?></td>
        <td>&nbsp;</td>
      </tr>
      <?php foreach($maintenancePagesDetails as $page=>$pageSettings): ?>
        <tr>
          <td class="table-row-title option-level-2"><?php echo $pageSettings['name'].
              ($pageSettings['required']?$requiredString:''); ?></td>
          <td>
            <?php
            $selectedPage=isset($maintenancePages[$page])?$maintenancePages[$page]:0;

            wp_dropdown_pages(['depth'=>0,
              'name'=>sprintf('myHomeMaintenancePages[%s]',$page),
              'id'=>sprintf('selectMyHomeMaintenancePage%s',ucfirst($page)),
              'show_option_none'=>__('Select a Page...','myHome'),
              'option_none_value'=>'0',
              'selected'=>$selectedPage]);
            ?>
            <button class="button myhome-autodetect-page" data-maintenance-page="<?php echo esc_attr($page); ?>"
              type="button"><?php _ex('Autodetect','Maintenance admin page','myHome'); ?></button>
          </td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('If Review is not set, the maintenance job is submitted and the Confirmed page is loaded after Issues without scheduling an appointment.',
            'myHome'); ?></td>
      </tr>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('It is advised to add a [MyHome.MaintenanceHeader] shortcode to Issues and Review pages.',
            'myHome'); ?></td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('Maximum File Size (in MiB)','myHome'); ?></td>
        <td><input class="input-narrow" max="100.00" min="0.25" name="myHomeMaintenanceMaxFileSize" step="0.25"
            type="number" value="<?php echo esc_attr($maintenanceMaxFileSize); ?>"></td>
      </tr>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('This value is ignored if it is greater than PHP\'s post_max_size and upload_max_filesize settings.',
            'myHome'); ?></td>
      </tr>
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
      })
      .find(".myhome-autodetect-page").click(function(){
        var suggestedMaintenancePages=<?php echo json_encode($suggestedMaintenancePages); ?>;
        var page=$(this).data("maintenance-page");
        var suggestedPage=suggestedMaintenancePages[page];

        if(suggestedPage!==0)
          $("#selectMyHomeMaintenancePage"+ucfirst(page)).val(suggestedPage);
        else
          alert("<?php _e('A valid Maintenance Page could not be found.','myHome'); ?>");
      });

    function ucfirst(string){
      var res=string.charAt(0).toUpperCase();
      res+=string.substr(1);

      return res;
    }
  });
</script>
