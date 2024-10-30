<?php
/**
 * The contact view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 * @since      1.1
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeContactController))
  die;

/**
 * @var ShortcodeContactController $this
 * @var mixed[]                    $attFields
 * @var mixed[]                    $attDropdowns
 */

// Used to close the last row
$maxNumField=@max(array_keys($attFields)) || 0;
// Open a row before the first field
$newLine=true;

$formAttributesContact=myHome()->adminPostHandler->formAttributes('contact','POST');

$error=$this->restoreVar('error');
$message=$this->restoreVar('message');
?>
<form action="<?php $this->appendFormUrl($formAttributesContact); ?>" class="mh-wrapper mh-wrapper-contact"
  method="POST">
  <?php
  $this->appendFormParams($formAttributesContact,2);
  ?>
  <?php if($error): ?>
    <div class="mh-error mh-error-contact"><?php echo esc_html($error); ?></div>
  <?php elseif($message): ?>
    <div class="mh-message mh-message-contact"><?php echo esc_html($message); ?></div>
  <?php endif; ?>
  <div class="mh-body mh-body-contact">
    <?php foreach($attFields as $numField=>$field): ?>
      <?php if($newLine): ?>
        <div class="mh-row mh-row-contact">
        <div class="mh-cell mh-cell-contact-field"><?php echo esc_html($field[0]!=='newline'?$field[0]:''); ?></div>
        <div class="mh-cell mh-cell-contact-input <?php echo str_replace(' ', '-', $field[0]) ?>">
      <?php endif; ?>
      <?php if($field[0]!=='newline'): ?>
        <?php
        $fieldName=sprintf('myHomeField[%s]',esc_attr($field[1]));

        $type=$field[2];

        if($type!=='text'){
          $maxLength='';
          $placeholder=isset($field[3])?$field[3]:'';
        }
        else{
          $maxLength=isset($field[3])?$field[3]:100;
          $placeholder=isset($field[4])?$field[4]:'';
        }

        $placeholder=esc_html($placeholder);
        ?>
        <?php switch($type): ?>
<?php case 'dropdown': ?>
            <select name="<?php echo $fieldName; ?>" size="1">
              <?php if($placeholder!==''): ?>
                <!--<option value=""><?php echo $placeholder; ?></option>-->
              <?php endif; ?>
              <?php foreach($attDropdowns[$numField] as $option): ?>
                <option><?php echo esc_html($option); ?></option>
              <?php endforeach; ?>
            </select>
            <?php break; ?>
          <?php case 'text': ?>
            <input maxlength="<?php echo $maxLength; ?>" name="<?php echo $fieldName; ?>"
              placeholder="<?php echo $placeholder; ?>" type="text">
            <?php break; ?>
          <?php case 'number': ?>
            <input name="<?php echo $fieldName; ?>" placeholder="<?php echo $placeholder; ?>" type="number">
            <?php break; ?>
          <?php case 'date': ?>
            <input class="datepicker" maxlength="10" name="<?php echo $fieldName; ?>"
              placeholder="<?php echo $placeholder; ?>" type="text">
            <?php break; ?>
          <?php case 'note': ?>
            <textarea maxlength="5000" name="<?php echo $fieldName; ?>" placeholder="<?php echo $placeholder; ?>"
              rows="5"></textarea>
            <?php break; ?>
          <?php endswitch; ?>
      <?php endif; ?>
      <?php
      $newLine=$field[0]==='newline'||$numField===$maxNumField;
      ?>
      <?php if($newLine): ?>
        </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <div class="mh-footer mh-footer-contact">
    <div class="mh-row mh-row-contact-submit-button">
      <div class="mh-cell mh-cell-contact-submit-button">
        <button class="mh-button mh-button-contact-submit" type="submit"><?php _ex('Submit','Contact Form',
            'myHome'); ?></button>
      </div>
    </div>
  </div>
</form>
<script type="text/javascript">
  jQuery(function($){
    if(typeof $.fn.datepicker==="function")
      $(".datepicker")
        .prop("readonly",true)
        .datepicker({dateFormat:"dd/mm/yy"}); // Australian format
  });
</script>
