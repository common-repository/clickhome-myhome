<?php
/**
 * The adminPageLog view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageLogController))
  die;

/**
 * @var AdminPageLogController $this
 * @var bool|null              $logStatus
 * @var string                 $logContents
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('adminLog','POST');

$logStatusText='';

if($logStatus===true)
  $logStatusText='<span class="status-ok">'._x('Working','Log admin page','myHome').'</span>';
else if($logStatus===false)
  $logStatusText='<span class="status-error">'.
    sprintf(__('Not working - check writing permissions on %s</span>','myHome'),MH_PATH_HOME).'</span>';
else if($logStatus===null)
  $logStatusText='<span class="status-unknown">'.__('Unknown - nothing has been logged yet','myHome').'</span>';

$cleared=$this->restoreVar('cleared');
$error=$this->restoreVar('error');

echo get_option('plugin_error');
?>
<div class="wrap">
  <?php
  $this->writeHeaderTabs();
  ?>
  <?php if($cleared): ?>
    <div class="updated"><p><?php _e('The log has been cleared.','myHome'); ?></p></div>
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
        <td class="table-row-title"><?php _e('Log Status','myHome'); ?></td>
        <td class="status"><?php echo $logStatusText; ?></td>
      </tr>
      <tr>
        <td class="table-row-title"><?php _e('Log Contents','myHome'); ?></td>
        <td class="output">
          <?php if($logContents): ?>
            <textarea readonly><?php echo $logContents; ?></textarea>
          <?php else: ?>
            <span>Empty log</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <button class="button" id="buttonMyHomeClearLog" type="submit"><?php _e('Clear Log','myHome'); ?></button>
        </td>
      </tr>
      </tbody>
    </table>
  </form>
</div>
<script type="text/javascript">
  jQuery(function($){
    $("#formMyHomeAdminPage").submit(function(){
      $("#buttonMyHomeClearLog")
        .prop("disabled",true)
        .empty()
        .append("<?php _e('Clearing Log...','myHome'); ?>");
    });
  });
</script>
