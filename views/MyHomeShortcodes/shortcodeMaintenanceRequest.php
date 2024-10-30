<?php
/**
 * The maintenanceRequest view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeMaintenanceRequestController))
  die;

/**
 * @var ShortcodeMaintenanceRequestController $this
 * @var bool[]                                $availableMaintenanceTypes
 * @var string[]                              $maintenanceTypes
 * @var string                                $redirectUrl
 * @var string                                $redirectUrlError
 * @var string                                $paramPostId
 */
$formAttributes = myHome()->adminPostHandler->formAttributes('maintenanceRequest','POST',$redirectUrl,$redirectUrlError);
$formAttributes['params'][$paramPostId] = get_the_ID();
/*$formAttributes['params']['myHomeAllowMultipleJobs'] = json_encode($attrMultipleJobs);
$formAttributes['params']['myHomeAllowMoreIssues'] = json_encode($attrMoreIssues);
$formAttributes['params']['myHomeAllowMoreIssuesLimit'] = $attrMoreIssuesLimit;*/

?>
<form action="<?php $this->appendFormUrl($formAttributes); ?>" class="mh-wrapper mh-wrapper-maintenance-request" method="POST">
  <?php $this->appendFormParams($formAttributes,2); ?>
  <?php foreach($maintenanceTypes as $code=>$type): ?>
    <?php if($type->job == null) continue; ?>
    <div class="text-center margin-bottom-15">
      <div class="mh-button-wrapper mh-button-block">
        <button class="mh-button mh-button-maintenance-request" name="myHomeMaintenanceType" type="submit" value="<?php echo esc_attr($type->name); ?>"><?php echo esc_html($type->title); ?></button>
      </div>
      <p class="margin-bottom-15"><?php echo($type->description); ?></p>
    </div>
  <?php endforeach; ?>
</form>
