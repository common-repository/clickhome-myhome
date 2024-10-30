<?php
/**
 * The adminPageTender view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 * @since      1.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageTenderController))
  die;

/**
 * @var AdminPageTenderController $this
 * @var int[]                     $tenderPages
 * @var int[]                     $suggestedTenderPages
 * @var mixed[]                   $tenderPagesDetails
 * @var bool                      $skipList
 * @var bool                      $skipSelectionOverview
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('adminTender','POST');

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
    <table id="tableMyHomeAdminPage" class="fixed-width">
      <tbody>
      <tr>
        <td class="table-row-title"><?php _e('Tender Pages','myHome'); ?></td>
        <td>&nbsp;</td>
      </tr>
      <!-- <tr>
        <td class="table-row-title"></td>
        <td>
          <label>
            <input <?php $skipList&&print('checked'); ?> name="myHomeSkipList" type="checkbox"><?php _e('Skip "Tender List" page if single tender','myHome'); ?>
          </label>
        </td>
      </tr> -->
      <?php foreach($tenderPagesDetails as $page=>$pageSettings): ?>
        <tr>
          <td class="table-row-title option-level-2"><?php echo $pageSettings['name'].
              ($pageSettings['required']?$requiredString:''); ?></td>
          <td>
            <?php
            $selectedPage=isset($tenderPages[$page])?$tenderPages[$page]:0;

            wp_dropdown_pages(['depth'=>0,
              'name'=>sprintf('myHomeTenderPages[%s]',$page),
              'id'=>sprintf('selectMyHomeTenderPage%s',ucfirst($page)),
              'show_option_none'=>__('Select a Page...','myHome'),
              'option_none_value'=>'0',
              'selected'=>$selectedPage]);
            ?>
          </td>
          <td>
            <button class="button myhome-autodetect-page" data-tender-page="<?php echo esc_attr($page); ?>"
              type="button"><?php _ex('Autodetect','Tender admin page','myHome'); ?></button>
          </td>
        <!-- </tr> -->
        <?php if(in_array($page, array('list', 'selections'))): ?>
          <!-- <tr>
            <td class="table-row-title"></td> -->
            <td>
              <label>
                <?php if($page == 'list'): ?>
                  <input <?php $skipList&&print('checked'); ?> name="myHomeSkipList" type="checkbox"><?php _e('Skip tenders list if single tender','myHome'); ?>
                <?php elseif($page == 'selections'): ?>
                  <input <?php $skipSelectionOverview&&print('checked'); ?> name="myHomeSkipSelectionOverview" type="checkbox"><?php _e('Skip selection overview','myHome'); ?>
                <?php endif; ?>
              </label>
            </td>
        <?php else: ?>
          <td></td>
        <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      <!-- <tr>
        <td colspan="2"></td>
      </tr> -->
      </tbody>
    </table>
    
    <table id="tableMyHomeAdminPage" style="margin-top: 0">
      <tbody>
      <tr>
        <td class="table-row-title option-level-2" colspan="4">
          <label>Variation Signature Declaration</label>
        </td>
      </tr>
      <tr>
        <td style="width:100%" colspan="4">
          <textarea name="myHomeTenderVariationDeclaration" style="width:100%; height:150px;"><?php echo $declaration; ?></textarea>
        </td>
      </tr>
      <tr>
        <td colspan="4">
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
  var suggestedTenderPages=<?php echo json_encode($suggestedTenderPages); ?>;
  var page=$(this).data("tender-page");
  var suggestedPage=suggestedTenderPages[page];

  if(suggestedPage!==0)
  $("#selectMyHomeTenderPage"+ucfirst(page)).val(suggestedPage);
  else
  alert("<?php _e('A valid Tender Page could not be found.','myHome'); ?>");
  });

  function ucfirst(string){
  var res=string.charAt(0).toUpperCase();
  res+=string.substr(1);

  return res;
  }
  });
</script>
