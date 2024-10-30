<?php
/**
 * The contract view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeContractController))
  die;

/**
 * @var ShortcodeContractController $this
 * @var string                      $attMode
 * @var string[]                    $attHideFields
 * @var stdClass                    $jobDetails
 * @var string[]                    $fieldNames
 */

// Filter any field whose name is unknown
foreach($jobDetails as $field=>$value)
  if(!isset($fieldNames[$field]))
    unset($jobDetails->$field);

// Remove hidden fields
foreach($attHideFields as $field)
  if(property_exists($jobDetails,$field)) // Some values may be null - isset() should not be used here
    unset($jobDetails->$field);

// Keep only some fields if simple mode is selected
if($attMode==='simple'){
  $allowedFields=[
    'job',
    'clienttitle',
    'lotaddress',
    'housetype',
    'facade'
  ];

  foreach($jobDetails as $field=>$value)
    if(!in_array($field,$allowedFields))
      unset($jobDetails->$field);
}
?>
<div class="mh-wrapper mh-wrapper-contract">
  <?php foreach($fieldNames as $key=>$name): // echo($key . ': ' . $name); ?>
    <?php if(!isset($jobDetails->$key)) continue; ?>
    <div class="mh-row mh-row-contract">
      <?php switch($key):
        case 'salesContact': ?>
          <div class="mh-cell mh-cell-contract-name"><?php echo esc_html($name); ?></div>
          <div class="mh-cell mh-cell-contract-value"><?php echo @$this->profilePic($jobDetails->$key) . ' ' . @esc_html($jobDetails->$key->name); ?></div>
        <?php break; ?>
        <?php default: ?>
          <div class="mh-cell mh-cell-contract-name"><?php echo esc_html($name); ?></div>
          <div class="mh-cell mh-cell-contract-value" title="<?php echo @$jobDetails->$key ?>"><?php echo @esc_html($jobDetails->$key); ?></div>
        <?php break; ?>
      <?php endswitch; ?>
    </div>
  <?php endforeach; ?>
</div>