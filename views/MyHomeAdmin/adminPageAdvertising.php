<?php
/**
 * The adminPageAdvertising view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageAdvertisingController))
  die;

/**
 * @var AdminPageAdvertisingController $this
 * @var int|false                      $nextCronjob
 * @var bool                           $databaseStatus
 * @var mixed[]                        $houseTypes
 * @var mixed[]                        $displays
 * @var string                         $defaultTemplateHouseTypes
 * @var string                         $defaultTemplateDisplays
 */

if($nextCronjob)
  $nextCronjobText=
    date_i18n(get_option('date_format').' '.get_option('time_format'),$nextCronjob+3600*get_option('gmt_offset'));
else
  $nextCronjobText='';

if($databaseStatus)
  $databaseStatusText='<span class="status-ok">'._x('OK','Advertising admin page','myHome').'</span>';
else if($databaseStatus===false)
  $databaseStatusText=
    '<span class="status-error">'.__('Error - click the Regenerate Database button','myHome').'</span>';

$formAttributes=myHome()->adminPostHandler->formAttributes('adminAdvertising','POST');

$saved=$this->restoreVar('saved');
$updated=$this->restoreVar('updated');
$regenerated=$this->restoreVar('regenerated');
$error=$this->restoreVar('error');
?>
<div class="wrap">
  <?php
  $this->writeHeaderTabs();
  ?>
  <?php if($saved): ?>
    <div class="updated"><p><?php _e('Settings updated successfully.','myHome'); ?></p></div>
  <?php elseif($updated): ?>
    <div class="updated"><p><?php _e('Update successful: ','myHome');
        echo esc_html($updated); ?>.</p></div>
  <?php elseif($regenerated): ?>
    <div class="updated"><p><?php _e('Database regenerated successfully. You should now update the database.',
          'myHome'); ?></p></div>
  <?php endif; ?>
  <?php if($error): ?>
    <div class="error"><p><?php printf(__('Error: %s.','myHome'),esc_html($error)); ?></p></div>
  <?php endif; ?>
  <form action="<?php $this->appendFormUrl($formAttributes); ?>" id="formMyHomeAdminPage" method="POST">
    <?php
    $this->appendFormParams($formAttributes,4);
    ?>
    <table id="tableMyHomeAdminPage">
      <tbody>
      <tr>
        <td class="table-row-title"><?php printf(__('House Types (%u)','myHome'),count($houseTypes)); ?></td>
        <td>
          <select class="page-ids" size="1">
            <option selected value="0"><?php _e('(House Types...)','myHome'); ?></option>
            <?php foreach($houseTypes as $houseType): ?>
              <option data-url="<?php echo esc_url($houseType['url']); ?>"
                value="<?php echo (int)$houseType['pageId']; ?>"><?php echo esc_html($houseType['name']).
                  ($houseType['pageId']?'':__(' (no page found)','myHome')); ?></option>
            <?php endforeach; ?>
          </select>
          <a class="page-edit" href="javascript:void(0);">Edit</a>
          <a class="page-view" href="javascript:void(0);">View</a>
        </td>
      </tr>
      <tr>
        <td class="table-row-title"><?php printf(__('Displays (%u)','myHome'),count($displays)); ?></td>
        <td>
          <select class="page-ids" size="1">
            <option selected value="0"><?php _e('(Displays...)','myHome'); ?></option>
            <?php foreach($displays as $display): ?>
              <option data-url="<?php echo esc_url($display['url']); ?>"
                value="<?php echo (int)$display['pageId']; ?>"><?php echo esc_html($display['name']).
                  ($display['pageId']?'':__(' (no page found)','myHome'));; ?></option>
            <?php endforeach; ?>
          </select>
          <a class="page-edit" href="javascript:void(0);">Edit</a>
          <a class="page-view" href="javascript:void(0);">View</a>
        </td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('Database Status','myHome'); ?></td>
        <td class="status"><?php echo $databaseStatusText; ?></td>
      </tr>
      <tr>
        <td class="table-row-title" colspan="2"><label><input<?php echo $nextCronjob?' checked':''; ?>
              name="myHomeCronjobEnabled" type="checkbox"><?php _e('Update Database Automatically','myHome'); ?></td>
      </tr>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('If this option is checked, the database will be updated on a daily basis starting 24 hours after the Save Changes button is clicked.',
            'myHome'); ?></td>
      </tr>
      <?php if($nextCronjob): ?>
        <tr>
          <td class="table-row-title"><?php _e('Next Automatic Update','myHome'); ?></td>
          <td class="status"><?php echo $nextCronjobText; ?></td>
        </tr>
      <?php endif; ?>
      <tr>
        <td class="table-row-title" colspan="2"><?php _e('Default Template for New Pages','myHome'); ?></td>
      </tr>
      <tr>
        <td class="table-row-title tip"
          colspan="2"><?php _e('Some templates may not be compatible. Existing pages won\'t be affected by this setting.',
            'myHome'); ?></td>
      </tr>
      <tr>
        <td class="table-row-title option-level-2"><?php _ex('House Types',
            'Advertising admin page (default template setting)','myHome'); ?></td>
        <td>
          <select name="myHomeDefaultTemplateHouseTypes" size="1">
            <option<?php echo $defaultTemplateHouseTypes===''?' selected':''; ?> value=""><?php _e('None',
                'myHome'); ?></option>
            <?php
            $templates=get_page_templates();

            foreach($templates as $name=>$filename)
              printf("              <option%s value=\"%s\">%s</option>\n",
                $filename===$defaultTemplateHouseTypes?' selected':'',esc_attr($filename),esc_html($name));
            ?>
          </select>
        </td>
      </tr>
      <tr>
        <td class="table-row-title option-level-2"><?php _ex('Displays',
            'Advertising admin page (default template setting)','myHome'); ?></td>
        <td>
          <select name="myHomeDefaultTemplateDisplays" size="1">
            <option<?php echo $defaultTemplateDisplays===''?' selected':''; ?> value=""><?php _e('None',
                'myHome'); ?></option>
            <?php
            $templates=get_page_templates();

            foreach($templates as $name=>$filename)
              printf("              <option%s value=\"%s\">%s</option>\n",
                $filename===$defaultTemplateDisplays?' selected':'',esc_attr($filename),esc_html($name));
            ?>
          </select>
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <input id="inputMyHomeSubmit" name="myHomeSubmit" type="hidden">
          <button class="button button-primary" id="buttonMyHomeSaveChanges" type="submit"
            value="save"><?php _e('Save Changes','myHome'); ?></button>
          <button class="button" id="buttonMyHomeUpdate" type="submit" value="update"><?php _e('Update Database Now',
              'myHome'); ?></button>
          <?php if(!$databaseStatus): ?>
            <button class="button" id="buttonMyHomeRegenerate" type="submit"
              value="regenerate"><?php _e('Regenerate Database','myHome'); ?></button>
          <?php endif; ?>
        </td>
      </tr>
      </tbody>
    </table>
  </form>
</div>
<script type="text/javascript">
  jQuery(function($){
    var form=$("#formMyHomeAdminPage");

    form.find(".page-ids").change(function(){
      var option=$(this).children("option:selected");

      var editUrl="<?php echo admin_url('post.php?post={postId}&action=edit'); ?>";

      if(option.val()!=="0"){
        $(this).parent().children(".page-edit").attr("href",editUrl.replace("{postId}",option.val()));
        $(this).parent().children(".page-view").attr("href",option.data("url"));

        $(this).parent().children("a").attr("target","_blank");
      }
      else
        $(this).parent().children("a")
          .attr("href","javascript:void(0);")
          .removeAttr("target");
    });

    form.find("button").click(function(){
      $(this).data("clicked","true");
    });

    form.submit(function(){
      var button=$("#buttonMyHomeSaveChanges");
      var message="<?php _e('Saving...','myHome'); ?>";

      var buttonUpdate=$("#buttonMyHomeUpdate");
      var buttonRegenerate=$("#buttonMyHomeRegenerate");

      if(buttonUpdate.data("clicked")==="true"){
        button=buttonUpdate;
        message="<?php _e('Updating...','myHome'); ?>";
      }
      else if(buttonRegenerate.data("clicked")==="true"){
        button=buttonRegenerate;
        message="<?php _e('Regenerating...','myHome'); ?>";
      }

      $("#inputMyHomeSubmit").val(button.val());

      button
        .prop("disabled",true)
        .empty()
        .append(message);
    });
  });
</script>
