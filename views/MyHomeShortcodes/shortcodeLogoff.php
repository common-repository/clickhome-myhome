<?php
/**
 * The logoff view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeLogoffController))
  die;

/**
 * @var ShortcodeLogoffController $this
 * @var string                    $attRedirect
 */

$formAttributes=myHome()->adminPostHandler->formAttributes('logoff','GET',$attRedirect);
$url=add_query_arg($formAttributes['params'],$formAttributes['url']);
?>
<div class="mh-wrapper mh-wrapper-logoff">
  <div class="mh-row mh-row-logoff-button"><a class="mh-button mh-button-logoff"
      href="<?php echo esc_url($url); ?>"><?php _ex('Logoff','Logoff Button','myHome'); ?></a></div>
</div>
