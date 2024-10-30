<?php
/**
 * The adminPageContactForm view
 *
 * @package    MyHome
 * @subpackage ViewsAdmin
 * @since      1.1
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Exit if not called from the controller
if(!isset($this)||!($this instanceof AdminPageContactFormController))
  die;

/**
 * @var AdminPageContactFormController $this
 */

$shortcodeBasic=
  '[MyHome.Contact field1="Contact 1,contact1Title,dropdown,(Title)" field2=",contact1FirstName,text,50,First name" field3=",contact1LastName,text,50,Last name" field4="newline" field5="Contact 2,contact2Title,dropdown,(Title)" field6=",contact2FirstName,text,50,First name" field7=",contact2LastName,text,50,Last name" field8="newline" field9="Phone,phone1,text,20" field10="newline" field11="Email,email,text,50" field12="newline" field13="Subject,enquirySubject,text,50" field14="newline" field15="Message,enquiryBody,note" dropdown1="Mr,Mrs,Ms,Miss,Dr" dropdown5="Mr,Mrs,Ms,Miss,Dr"]';
$shortcodeFlexfields=
  '[MyHome.Contact field1="Contact 1,contact1Title,dropdown,(Title)" field2=",contact1FirstName,text,50,First name" field3=",contact1LastName,text,50,Last name" field4="newline" field5="Contact 2,contact2Title,dropdown,(Title)" field6=",contact2FirstName,text,50,First name" field7=",contact2LastName,text,50,Last name" field8="newline" field9="Phone,phone1,text,20" field10="newline" field11="Email,email,text,50" field12="newline" field13="Subject,enquirySubject,text,50" field14="newline" field15="Flexfield Caption,flexField01,dropdown" field16="newline" field17="Flexfield 2 Caption,flexField02,dropdown" field18="newline" field19="Flexfield 3 Caption,flexField03,dropdown" field20="newline" field21="Message,enquiryBody,note" dropdown1="Mr,Mrs,Ms,Miss,Dr" dropdown5="Mr,Mrs,Ms,Miss,Dr" dropdown15="Mr,Mrs,Ms,Miss,Dr" dropdown17="Mr,Mrs,Ms,Miss,Dr" dropdown19="Mr,Mrs,Ms,Miss,Dr"]';
?>
<div class="wrap">
  <?php
  $this->writeHeaderTabs();
  ?>
  <table id="tableMyHomeAdminPage">
    <tbody>
    <tr>
      <td class="table-row-title"><?php _e('Basic Contact Form','myHome'); ?></td>
      <td class="shortcode">
        <img src="<?php echo MH_URL_IMAGES; ?>/cf1.png">
        <button class="button view-shortcode" type="button"><?php _e('View Shortcode'); ?></button>
        <textarea class="shortcode" readonly rows="5"><?php echo esc_html($shortcodeBasic); ?></textarea>
      </td>
    </tr>
    <tr>
      <td class="table-row-title"><?php _e('Flexfields Contact Form','myHome'); ?></td>
      <td class="shortcode">
        <img src="<?php echo MH_URL_IMAGES; ?>/cf2.png">
        <button class="button view-shortcode" type="button"><?php _e('View Shortcode'); ?></button>
        <textarea class="shortcode" readonly rows="7"><?php echo esc_html($shortcodeFlexfields); ?></textarea>
      </td>
    </tr>
    <tr>
      <td class="table-row-title"><?php _e('Shortcode Syntax','myHome'); ?></td>
      <td class="syntax with-list">
        <div>
          <pre>[MyHome.Contact field1="<i>field1Settings</i>" field2="<i>field2Settings</i>" ... dropdownM="<i>dropdownMOptions</i>" dropdownN="<i>dropdownNOptions</i>"]</pre>
        </div>
        <div>Each field can have the following settings (<pre>*</pre> denotes optional settings):</div>
        <ul>
          <li>Text:
            <pre><i>title</i>,<i>post field</i>,text,<i>maximum length*</i>,<i>placeholder*</i></pre>
          </li>
          <li>Number:
            <pre><i>title</i>,<i>post field</i>,number,<i>placeholder*</i></pre>
          </li>
          <li>Dropdown:
            <pre><i>title</i>,<i>post field</i>,dropdown,<i>placeholder*</i></pre>
          </li>
          <li>Date:
            <pre><i>title</i>,<i>post field</i>,date,<i>placeholder*</i></pre>
          </li>
          <li>Note (text area):
            <pre><i>title</i>,<i>post field</i>,note,<i>placeholder*</i></pre>
          </li>
          <li>New line:
            <pre>newline</pre>
          </li>
        </ul>
        <div>Notes:</div>
        <ul>
          <li>Titles can be left empty (eg
            <pre>,contact1FirstName,text,50,First name</pre>
            )
          </li>
          <li>Commas in texts must be escaped with an underscore character (eg
            <pre>A_, B_, C,contact1FirstName,text</pre>
            )
          </li>
          <li>Each dropdown requires a
            <pre>dropdown</pre>
            attribute (eg
            <pre>dropdown5="Option 1,Option 2,Option 3"</pre>
            if
            <pre>field5</pre>
            is a dropdown)
          </li>
          <li>POST fields cannot be used more than once per form</li>
          <li>Maximum length ranges from 1 to 100</li>
        </ul>
        <div>Available POST fields:</div>
        <ul>
          <li>clientReference</li>
          <li>interfaceType</li>
          <li>contact1Title</li>
          <li>contact1FirstName</li>
          <li>contact1LastName</li>
          <li>contact2Title</li>
          <li>contact2FirstName</li>
          <li>contact2LastName</li>
          <li>phone1</li>
          <li>phone2</li>
          <li>phone3</li>
          <li>email</li>
          <li>contactMethod</li>
          <li>contactRule</li>
          <li>homeAddress1</li>
          <li>homeAddress2</li>
          <li>homeAddressState</li>
          <li>homeAddressSuburb</li>
          <li>homeAddressPostCode</li>
          <li>homeAddressCountry</li>
          <li>buildLot</li>
          <li>buildStreetNo</li>
          <li>buildState</li>
          <li>buildSuburb</li>
          <li>buildPostCode</li>
          <li>buildCouncil</li>
          <li>enquiryType</li>
          <li>enquirySubject</li>
          <li>enquiryBody</li>
          <li>enquiryAction</li>
          <li>enquiryDate</li>
          <li>referralSource</li>
          <li>referralData</li>
          <li>marketingSource</li>
          <li>newsLetterPermission</li>
          <li>flexField01</li>
          <li>flexField02</li>
          <li>flexField03</li>
          <li>flexField04</li>
          <li>flexField05</li>
          <li>flexField06</li>
          <li>flexField07</li>
          <li>flexField08</li>
          <li>flexField09</li>
          <li>flexField10</li>
          <li>flexField11</li>
          <li>flexField12</li>
          <li>flexField13</li>
          <li>flexField14</li>
          <li>flexField15</li>
          <li>flexField16</li>
          <li>flexField17</li>
          <li>flexField18</li>
          <li>flexField19</li>
          <li>flexField20</li>
        </ul>
      </td>
    </tr>
    </tbody>
  </table>
</div>
<script type="text/javascript">
  jQuery(function($){
    $(".view-shortcode").click(function(){
      $("#tableMyHomeAdminPage").find("textarea.shortcode").hide();
      $(this).parent().children("textarea").show().select();
    });

    $(".shortcode").click(function(){
      $(this).focus().select();
    });
  });
</script>
