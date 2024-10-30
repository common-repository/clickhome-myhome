<?php
/**
 * The contractHeader view
 *
 * @package    MyHome
 * @subpackage ViewsShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof ShortcodeContractHeaderController))
  die;

/**
 * @var ShortcodeContractHeaderController $this
 * @var stdClass                          $jobDetails
 */

$client=sprintf('Job %s %s for %s',$jobDetails->job,$jobDetails->housetype,$jobDetails->clienttitle);
$address=$jobDetails->lotaddress;
?>
<div class="mh-wrapper mh-wrapper-contract-header">
  <div class="mh-row mh-row-contract-header-client"><?php echo esc_html($client); ?></div>
  <div class="mh-row mh-row-contract-header-address"><?php echo esc_html($address); ?></div>
</div>
