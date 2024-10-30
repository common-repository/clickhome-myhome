<?php
/**
 * The maintenanceConfirmation view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeMaintenanceConfirmationController))
  die;

/**
 * @var ShortcodeMaintenanceConfirmationController $this
 * @var stdClass                                   $jobDetails
 * @var string                                     $redirectUrl
 * @var string                                     $redirectUrlError
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('maintenanceConfirmation','POST',$redirectUrl,$redirectUrlError);

$requiredFields=['contactname',
  'contactphone',
  'contactemail',
  'contactagent',
  'agentname',
  'agentcompany',
  'agentphone',
  'agentemail',
  'lotstreetno',
  'lotunitno',
  'lotlevelno'];

foreach($requiredFields as $requiredField)
  if(!isset($jobDetails->$requiredField))
    $jobDetails->$requiredField='';

$error=$this->restoreVar('error');
?>
<form action="<?php $this->appendFormUrl($formAttributes); ?>" class="mh-wrapper mh-wrapper-maintenance-confirmation"
  method="POST">
  <?php
  $this->appendFormParams($formAttributes,2);
  ?>
  <?php if($error): ?>
    <div class="mh-error mh-error-maintenance-confirmation"><?php echo esc_html($error); ?></div>
  <?php endif; ?>
  <div class="mh-body mh-body-maintenance-confirmation">
    <div class="mh-row mh-row-maintenance-confirmation-contact-name">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Contact Name','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="100" name="myHomeContactName"
          type="text" value="<?php echo esc_attr($jobDetails->contactname); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-contact-phone">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Contact Phone','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="20" name="myHomeContactPhone"
          type="text" value="<?php echo esc_attr($jobDetails->contactphone); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-contact-email">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Contact Email','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="50" name="myHomeContactEmail"
          type="email" value="<?php echo esc_attr($jobDetails->contactemail); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-contact-agent">
      <div class="mh-cell mh-cell-maintenance-confirmation-field">&nbsp;</div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><label><input<?php echo $jobDetails->contactagent?
            ' checked':''; ?> name="myHomeContactAgent" type="checkbox"> <?php _e('Agent is primary contact',
            'myHome'); ?></label></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-agent-name">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Agent Name','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="100" name="myHomeAgentName"
          type="text" value="<?php echo esc_attr($jobDetails->agentname); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-agent-company">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Agent Company','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="50" name="myHomeAgentCompany"
          type="text" value="<?php echo esc_attr($jobDetails->agentcompany); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-agent-phone">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Agent Phone','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="20" name="myHomeAgentPhone"
          type="text" value="<?php echo esc_attr($jobDetails->agentphone); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-agent-email">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Agent Email','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="50" name="myHomeAgentEmail"
          type="email" value="<?php echo esc_attr($jobDetails->agentemail); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-property-street-no">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Property Street No.',
          'myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="10" name="myHomePropertyStreetNo"
          type="text" value="<?php echo esc_attr($jobDetails->lotstreetno); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-property-unit-no">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Property Unit No.','myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="10" name="myHomePropertyUnitNo"
          type="text" value="<?php echo esc_attr($jobDetails->lotunitno); ?>"></div>
    </div>
    <div class="mh-row mh-row-maintenance-confirmation-property-level-no">
      <div class="mh-cell mh-cell-maintenance-confirmation-field"><?php _e('Property Level No.',
          'myHome'); ?></div>
      <div class="mh-cell mh-cell-maintenance-confirmation-input"><input maxlength="10" name="myHomePropertyLevelNo"
          type="text" value="<?php echo esc_attr($jobDetails->lotlevelno); ?>"></div>
    </div>
  </div>
  <div class="mh-footer mh-footer-maintenance-confirmation">
    <div class="mh-row mh-row-maintenance-confirmation-buttons">
      <div class="mh-cell mh-cell-maintenance-confirmation-buttons">
        <button class="mh-button mh-button-maintenance-confirmation-submit" name="myHomeSubmit" type="submit"
          value="update"><?php _ex('Update','Maintenance Confirmation Form','myHome'); ?></button>
        <button class="mh-button mh-button-maintenance-confirmation-submit" name="myHomeSubmit" type="submit"
          value="continue"><?php _ex('Continue','Maintenance Confirmation Form','myHome'); ?></button>
      </div>
    </div>
  </div>
</form>
