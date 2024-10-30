<?php

/**
 * The ShortcodeContactController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 * @since      1.1
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeContactController'))
  return;

/**
 * The ShortcodeContactController class
 *
 * Controller for the Contact shortcode
 *
 * @since 1.1
 */
class ShortcodeContactController extends MyHomeShortcodesBaseController{
  /**
   * Name prefix for the field attributes
   */
  private static $ATT_PREFIX_FIELD='field';

  /**
   * Name prefix for the dropdown attributes
   */
  private static $ATT_PREFIX_DROPDOWN='dropdown';

  /**
   * The valid field names to be sent to the webinquiry API call
   */
  private static $POST_FIELDS=['clientReference',
    'interfaceType',
    'contact1Title',
    'contact1FirstName',
    'contact1LastName',
    'contact2Title',
    'contact2FirstName',
    'contact2LastName',
    'phone1',
    'phone2',
    'phone3',
    'email',
    'contactMethod',
    'contactRule',
    'homeAddress1',
    'homeAddress2',
    'homeAddressState',
    'homeAddressSuburb',
    'homeAddressPostCode',
    'homeAddressCountry',
    'buildLot',
    'buildStreetNo',
    'buildState',
    'buildSuburb',
    'buildPostCode',
    'buildCouncil',
    'enquiryType',
    'enquirySubject',
    'enquiryBody',
    'enquiryAction',
    'enquiryDate',
    'referralSource',
    'referralData',
    'marketingSource',
    'newsLetterPermission',
    'flexField01',
    'flexField02',
    'flexField03',
    'flexField04',
    'flexField05',
    'flexField06',
    'flexField07',
    'flexField08',
    'flexField09',
    'flexField10',
    'flexField11',
    'flexField12',
    'flexField13',
    'flexField14',
    'flexField15',
    'flexField16',
    'flexField17',
    'flexField18',
    'flexField19',
    'flexField20'];

  /**
   * Maximum number of fields
   *
   * Note that it is equal to the number of different POST fields - ie count($POST_FIELDS)
   */
  private static $MAX_FIELDS=55;

  /**
   * Valid field types
   */
  private static $VALID_TYPES=['dropdown',
    'text',
    'number',
    'date',
    'note'];

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    myHome()->log->info('POST contact form');

    if(empty($params['myHomeField'])){
      $this->flashVar('error',__('No data has been received','myHome'));
      return;
    }

    $values=$params['myHomeField'];

    $params=[];
    foreach(self::$POST_FIELDS as $postField)
      $params[$postField]=isset($values[$postField])?$values[$postField]:'';
    $params['webAPIKey']=myHome()->options->getContactApiKey();

    $contactResponse=myHome()->api->post('webinquiry',$params,null,false);
    myHome()->log->info('$contactResponse->status: ' . $contactResponse->status);
    //myHome()->log->info(serialize($contactResponse));

    if(!empty($contactResponse->status)&&$contactResponse->status===200)
      $this->flashVar('message',__('Message sent','myHome'));
    else
      $this->flashVar('error',__('The message could not be sent','myHome'));
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $allowedAtts=[];

    // Fill in the array of allowed attributes (field1, dropdown1, field2, dropdown2...) with an empty string as default for every attribute (which means the field is not used)
    for($i=1;$i<=self::$MAX_FIELDS;$i++){
      $allowedAtts[self::$ATT_PREFIX_FIELD.$i]='';
      $allowedAtts[self::$ATT_PREFIX_DROPDOWN.$i]='';
    }

    $atts=shortcode_atts($allowedAtts,$atts);

    $attFields=$this->groupAtts($atts,self::$ATT_PREFIX_FIELD);
    $attDropdowns=$this->groupAtts($atts,self::$ATT_PREFIX_DROPDOWN);

    if(!$attFields)
      myHome()->handleError('No Field attributes provided');
    else{
      $errors=$this->verifyFields($attFields,$attDropdowns);
      foreach($errors as $error){
        myHome()->handleError($error['message']);
        unset($attFields[$error['number']]);
      }
    }

    $this->loadView('shortcodeContact','MyHomeShortcodes',compact('attFields','attDropdowns'));
  }

  /**
   * Extracts a set of different non empty attribute values from the original attributes array into another array,
   * splitting them by the comma character
   *
   * <p>For example, if $namePrefix is "field", and only "field1" and "field2" are non empty, it returns
   * array(1=>array(value11,value12),2=>array(value21))</p>
   * <p>Attributes checked are those made up of $namePrefix and a number, from 1 to MAX_FIELDS</p>
   * <p>Gaps are allowed - ie "field3" may be empty but not "field4"</p>
   * <p>Values are not checked at this point for proper syntax</p>
   * <p>Commas must be escaped with an underscore (eg "Option1,Option2_, option2,option3") - backslashes are removed by
   * WordPress</p>
   *
   * @param string[] $atts       the original attributes array
   * @param string   $namePrefix the name prefix of the attributes extracted (eg "field")
   * @return mixed[] the non empty attributes from $atts, indexed by the number of each attribute (eg 3 for "field3")
   */
  private function groupAtts(array $atts,$namePrefix){
    $array=[];

    for($i=1;$i<=self::$MAX_FIELDS;$i++){
      // No need to check for the existence of this key - it is presumed to exist
      $attValue=trim($atts[$namePrefix.$i]);

      if($attValue==='')
        continue;

      // Replace escaped commas with a special character sequence to prevent splitting
      $attValue=str_replace('_,',"\xff\xff",$attValue);

      $values=
        explode(',',$attValue); // Don't apply array_filter($values,'strlen') - an empty string is valid as first item

      // Restore escaped commas and trim down each part
      $values=array_map(function ($value){
        return trim(str_replace("\xff\xff",',',$value));
      },$values);

      $array[$i]=$values;
    }

    return $array;
  }

  /**
   * Verifies the value of the fields shortcode attributes provided
   *
   * @uses ShortcodeContactController::POST_FIELDS
   * @uses ShortcodeContactController::VALID_TYPES
   * @param string[] $attFields    the field attribute values to check, indexed by the number of the attribute (eg
   *                               array(3=>value3) if field3=value3 is present)
   * @param string[] $attDropdowns the dropdown attribute values to check, indexed by the number of the attribute -
   *                               needed to check if dropdown fields have their corresponding options list
   * @return mixed[] a list of errors with the following format:
   *                               <ul>
   *                               <li>message: the error message</li>
   *                               <li>number: the number of the attribute - used to remove the field attribute value
   *                               from the fields array</li>
   *                               </ul>
   */
  private function verifyFields(array $attFields,array $attDropdowns){
    $errors=[];

    // Used to check for repeated POST fields
    $usedPostFields=[];

    // field          ::= 'newline' | nonempty_field
    // nonempty_field ::= title ',' post_field ',' type_details (',' placeholder)?
    // title          ::= [^,]*
    // post_field     ::= ['A'-'Z''a'-'z''0'-'9']+ (from POST_FIELDS)
    // type_details   ::= 'dropdown' | 'text' (',' max_length)? | 'number' | 'date' | 'note'
    // max_length     ::= ['0'-'9']+ (default: 100)
    // placeholder    ::= [^,]*
    foreach($attFields as $numField=>$values){
      if($values[0]==='newline')
        continue;

      if(count($values)<3){
        $errors[]=['message'=>sprintf('Wrong Field %u attribute: wrong format (less than three comma-separated values)',
          $numField),
          'number'=>$numField];
        continue;
      }

      $postField=$values[1];
      $type=$values[2];

      $typeSettings=isset($values[3])?$values[3]:null;

      // Verify the POST field
      if(!in_array($postField,self::$POST_FIELDS))
        $errors[]=['message'=>sprintf('Wrong Field %u attribute: wrong POST field "%s"',$numField,$postField),
          'number'=>$numField];

      // Check if the POST field is repeated
      else if(isset($usedPostFields[$postField]))
        $errors[]=['message'=>sprintf('Wrong Field %u attribute: repeated POST field "%s"',$numField,$postField),
          'number'=>$numField];

      // Verify the field type
      else if(!in_array($type,self::$VALID_TYPES))
        $errors[]=['message'=>sprintf('Wrong Field %u attribute: wrong type "%s"',$numField,$type),
          'number'=>$numField];

      // If a dropdown, check for the options list in $attDropdowns
      else if($type==='dropdown'&&!isset($attDropdowns[$numField]))
        $errors[]=
          ['message'=>sprintf('Wrong Field %u attribute: no options provided for the dropdown (%s%u attribute needed)',
            $numField,self::$ATT_PREFIX_DROPDOWN,$numField),
            'number'=>$numField];

      // If a text field, check for a valid maximum length parameter (only if present)
      else if($type==='text'&&$typeSettings!==null&&(!is_numeric($typeSettings)||$typeSettings<1||$typeSettings>100))
        $errors[]=
          ['message'=>sprintf('Wrong Field %u attribute: wrong maximum length "%s" (it must be a number between 1 and 100)',
            $numField,$typeSettings),
            'number'=>$numField];

      // If there are no errors, mark the POST field as used in this form
      else
        $usedPostFields[$postField]=true;
    }

    return $errors;
  }
}
