<?php
/**
 * The faq view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeFaqController))
  die;

/**
 * @var ShortcodeFaqController $this
 * @var string                 $attMode
 * @var mixed[]                $questions
 */

if($attMode==='current')
  $questions=array_filter($questions,function (array $question){
    return $question['current'];
  });
?>
<div class="mh-wrapper mh-wrapper-faq">
  <?php foreach($questions as $question): ?>
    <div class="mh-block mh-block-faq <?php echo $question['current']?'question-current':''; ?>">
      <div class="mh-row mh-row-faq-question"><?php echo esc_html($question['question']); ?></div>
      <div class="mh-row mh-row-faq-answer"><?php echo nl2br(esc_html($question['answer'])); ?></div>
    </div>
  <?php endforeach; ?>
</div>
