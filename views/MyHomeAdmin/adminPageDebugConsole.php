<?php
/**
 * The adminPageDebugConsole view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageDebugConsoleController))
  die;

/**
 * @var AdminPageDebugConsoleController $this
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('adminDebug','POST');

$jobNumber=$this->restoreVar('jobNumber','');
$username=$this->restoreVar('username','');
$password=$this->restoreVar('password','');
$response=$this->restoreVar('response');
$error=$this->restoreVar('error');
$url=$this->restoreVar('url','');
$output=$this->restoreVar('output');
?>
<div class="wrap">
  <?php
  $this->writeHeaderTabs();
  ?>
  <?php if($response): ?>
    <div class="updated"><p><?php _e('Response received from the API.','myHome'); ?></p></div>
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
        <td class="table-row-title"><?php _e('API Function','myHome'); ?></td>
        <td>
          <select autofocus id="selectMyHomeCommand" name="myHomeCommand" size="1">
            <option selected value=""><?php _e('(Choose a Function...)','myHome'); ?></option>
            <optgroup label="Jobs">
              <option data-login="true" value="job"><?php _e('Job Details: job','myHome'); ?></option>
            </optgroup>
            <optgroup label="Maintenance">
              <option data-login="true" value="maintenancejobs"><?php _e('Maintenance Jobs: maintenancejobs',
                  'myHome'); ?></option>
              <option data-login="true" data-params="jobid"
                value="maintenancejobs/{jobid}"><?php _e('Maintenance Job Details: maintenancejobs/{jobid}',
                  'myHome'); ?></option>
              <option data-login="true" data-params="jobid"
                value="maintenancejobs/{jobid}/appointments"><?php _e('Maintenance Job Appointments: maintenancejobs/{jobid}/appointments',
                  'myHome'); ?></option>
              <option data-login="true" data-params="jobid"
                value="maintenancejobs/{jobid}/appointmentexclusions"><?php _e('Maintenance Job Appointment Exclusions: maintenancejobs/{jobid}/appointmentexclusions',
                  'myHome'); ?></option>
              <option data-login="true" data-params="jobid"
                value="maintenancejobs/{jobid}/maintenanceissues"><?php _e('Maintenance Job Issues: maintenancejobs/{jobid}/maintenanceissues',
                  'myHome'); ?></option>
              <option data-login="true" data-params="jobid,issueid"
                value="maintenancejobs/{jobid}/maintenanceissues/{issueid}"><?php _e('Maintenance Job Issue Details: maintenancejobs/{jobid}/maintenanceissues/{issueid}',
                  'myHome'); ?></option>
              <option data-login="true" data-params="jobid,issueid"
                value="maintenancejobs/{jobid}/maintenanceissues/{issueid}/documents"><?php _e('Maintenance Job Issue Documents: maintenancejobs/{jobid}/maintenanceissues/{issueid}/documents',
                  'myHome'); ?></option>
            </optgroup>
            <optgroup label="MyHome">
              <option data-login="true" value="jobphases"><?php _e('Job Phases: jobphases','myHome'); ?></option>
              <option data-login="true" value="jobsteps"><?php _e('Job Steps: jobsteps','myHome'); ?></option>
              <option data-login="true" value="jobprogress"><?php _e('Job Progress: jobprogress','myHome'); ?></option>
              <option data-login="true" value="stories"><?php _e('Stories: stories','myHome'); ?></option>
              <option data-login="true" value="questions"><?php _e('FAQ: questions','myHome'); ?></option>
              <option data-login="true" value="documents"><?php _e('Documents: documents','myHome'); ?></option>
              <option data-login="true" value="notes"><?php _e('Notes: notes','myHome'); ?></option>
              <option data-login="true" value="house"><?php _e('House Details: house','myHome'); ?></option>
            </optgroup>
            <optgroup label="Sales">
              <option data-login="false" value="housedetails"><?php _e('House Details: housedetails',
                  'myHome'); ?></option>
              <option data-login="false" data-params="houseid" value="housedetails/{houseid}"><?php _e('House Details single House: housedetails/{houseid}',
                  'myHome'); ?></option>
              <option data-login="false" value="displays"><?php _e('Display Homes: displays','myHome'); ?></option>
            </optgroup>
            <optgroup label="System">
              <option data-login="false" value="maintenancetypes"><?php _e('Maintenance Types: maintenancetypes',
                  'myHome'); ?></option>
            </optgroup>
            <optgroup label="Tender">
              <option data-login="true" value="tenders"><?php _e('Tender List: tenders','myHome'); ?></option>
              <option data-login="true" data-params="tenderid"
                value="tenders/{tenderid}"><?php _e('Tender Details: tenderdetails/{tenderid}',
                  'myHome'); ?></option>
              <option data-login="true" data-params="tenderid"
                value="tenders/{tenderid}/packages"><?php _e('Tender Packages: tenderdetails/{tenderid}/packages',
                  'myHome'); ?></option>
              <option data-login="true" data-params="tenderid"
                value="tenders/{tenderid}/selections"><?php _e('Tender Selections: tenderdetails/{tenderid}/selections',
                  'myHome'); ?></option>
              <option data-login="true" data-params="tenderid,categoryid"
                value="tenders/{tenderid}/selections/{categoryid}"><?php _e('Tender Selections from Tender and Category: tenderdetails/{tenderid}/selections/{categoryid}',
                  'myHome'); ?></option>
              <option data-login="true" data-params="tenderid"
                value="tenders/{tenderid}/variations"><?php _e('Tender Variations: tenderdetails/{tenderid}/variations',
                  'myHome'); ?></option>
            </optgroup>
          </select>
        </td>
      </tr>
      <tr class="debug-param debug-param-login">
        <td class="table-row-title"><?php _e('Job Number','myHome'); ?></td>
        <td><input class="input-narrow" maxlength="10" name="myHomeJobNumber" type="text"
            value="<?php echo esc_attr($jobNumber); ?>"></td>
      </tr>
      <tr class="debug-param debug-param-login">
        <td class="table-row-title"><?php _e('Username','myHome'); ?></td>
        <td><input class="input-narrow" maxlength="20" name="myHomeUsername" type="text"
            value="<?php echo esc_attr($username); ?>"></td>
      </tr>
      <tr class="debug-param debug-param-login">
        <td class="table-row-title"><?php _e('Password','myHome'); ?></td>
        <td><input class="input-narrow" maxlength="20" name="myHomePassword" type="text"
            value="<?php echo esc_attr($password); ?>"></td>
      </tr>
      <tr class="debug-param debug-param-other" data-param="jobid">
        <td class="table-row-title option-level-2"><?php _e('jobid parameter','myHome'); ?></td>
        <td><input name="myHomeParams[jobid]" type="number"></td>
      </tr>
      <tr class="debug-param debug-param-other" data-param="issueid">
        <td class="table-row-title option-level-2"><?php _e('issueid parameter','myHome'); ?></td>
        <td><input name="myHomeParams[issueid]" type="number"></td>
      </tr>
      <tr class="debug-param debug-param-other" data-param="houseid">
        <td class="table-row-title option-level-2"><?php _e('houseid parameter','myHome'); ?></td>
        <td><input name="myHomeParams[houseid]" type="number"></td>
      </tr>
      <tr class="debug-param debug-param-other" data-param="leaderid">
        <td class="table-row-title option-level-2"><?php _e('leaderid parameter','myHome'); ?></td>
        <td><input name="myHomeParams[leaderid]" type="number"></td>
      </tr>
      <tr class="debug-param debug-param-other" data-param="tenderid">
        <td class="table-row-title option-level-2"><?php _e('tenderid parameter','myHome'); ?></td>
        <td><input name="myHomeParams[tenderid]" type="number"></td>
      </tr>
      <tr class="debug-param debug-param-other" data-param="categoryid">
        <td class="table-row-title option-level-2"><?php _e('categoryid parameter','myHome'); ?></td>
        <td><input name="myHomeParams[categoryid]" type="number"></td>
      </tr>
      <tr>
        <td colspan="2">
          <button class="button button-primary" id="buttonMyHomeSubmit" type="submit"><?php _e('Query',
              'myHome'); ?></button>
        </td>
      </tr>
      <?php if($response): ?>
        <tr>
          <td class="table-row-title"><?php _e('URL','myHome'); ?></td>
          <td><?php echo esc_html($url); ?></td>
        <tr>
          <td class="table-row-title"><?php _e('Output','myHome'); ?></td>
          <td class="output">
            <?php if($output): ?>
              <textarea readonly style="resize: both;"><?php print_r($output); ?></textarea>
            <?php else: ?>
              <span>Empty response</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </form>
</div>
<script type="text/javascript">
  jQuery(function($){
  var page=$("#formMyHomeAdminPage");
  var command=$("#selectMyHomeCommand");

  command.val("");

  command.change(function(){
  page.find("tr.debug-param").hide();

  var option=$(this).find("option:selected");

  var login=option.data("login");
  var params=option.data("params");

  if(login)
  page.find("tr.debug-param-login").show();

  if(params!==undefined)
  {
  params=params.split(",");

  $.each(params,function(key,param){
  page.find("tr.debug-param-other[data-param=\""+param+"\"]").show();
  });
  }
  });

  page.submit(function(){
  $("#buttonMyHomeSubmit")
  .prop("disabled",true)
  .empty()
  .append("<?php _e('Querying...','myHome'); ?>");
  });
  });
</script>
