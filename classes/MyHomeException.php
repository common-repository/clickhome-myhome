<?php

/**
 * The MyHomeException class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeException'))
  return;

/**
 * The MyHomeException class
 *
 * Base class for MyHome related exceptions
 */
class MyHomeException extends Exception{
  /**
   * MyHomeException constructor.
   *
   * @param string    $message
   * @param int       $code
   * @param Exception $previous
   */
  public function __construct($message='',$code=0,Exception $previous=null){
    //myHome()->log->info("code: " . $code . "\n");
    // Convert file upload error codes to messages
    if(strpos($message, 'receiving the file') !== false) {
      $phpFileUploadErrors = array(
          0 => 'There is no error, the file uploaded with success',
          1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
          2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
          3 => 'The uploaded file was only partially uploaded',
          4 => 'No file was uploaded',
          6 => 'Missing a temporary folder',
          7 => 'Failed to write file to disk.',
          8 => 'A PHP extension stopped the file upload.',
      );
      $message .= ' ' . $phpFileUploadErrors[$code];
    }

    parent::__construct($message,$code,$previous);

    $this->uniqueCode=substr(md5(uniqid('',true)),0,8);
  }

  /**
   * @return string
   */
  public function getUniqueCode(){
    return $this->uniqueCode;
  }

  /**
   * @var string
   */
  private $uniqueCode='';
}
