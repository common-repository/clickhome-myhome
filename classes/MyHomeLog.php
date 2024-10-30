<?php

/**
 * The MyHomeLog class
 *
 * @package    MyHome
 * @subpackage Classes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeLog'))
  return;

/**
 * The MyHomeLog class
 *
 * Logs error messages into a file and allows the WordPress administrator to read that file
 */
class MyHomeLog{
  /**
   * Error logging level
   *
   * @since 1.3 prior to this version, log level was checked before any call to MyHomeLog::info() or MyHomeLog::error()
   */
  public static $LEVEL_ERROR=1;

  /**
   * Info logging level
   *
   * @since 1.3 prior to this version, log level was checked before any call to MyHomeLog::info() or MyHomeLog::error()
   */
  public static $LEVEL_INFO=2;

  /**
   * File logging method
   *
   * @since 1.3
   */
  public static $METHOD_FILE=1;

  /**
   * Option logging method
   *
   * @since 1.3
   */
  public static $METHOD_OPTION=2;

  /**
   * Option name for the log filename
   */
  private static $OPTION_FILENAME='myhome_log_filename';

  /**
   * Name of the option which stores whether the last attempt to write to the log file succeeded
   */
  private static $OPTION_WORKING='myhome_log_working';

  /**
   * Option name for the log contents, when using the option method
   *
   * @since 1.3
   */
  private static $OPTION_CONTENTS='myhome_log_contents';

  /**
   * Constructor method
   *
   * <p>Initialises the properties used by the class: the log filename and the "working" flag</p>
   * <p>Sets a new log filename if none is found</p>
   */
  public function __construct(){
    $filename=get_option(self::$OPTION_FILENAME);

    // Generate the log filename if this hasn't been done yet - usually, this is done upon first plugin activation
    if($filename===false){
      $filename=$this->generateLogFilename();
      update_option(self::$OPTION_FILENAME,$filename);
    }

    $this->filename=$filename;

    // It would be much easier to simply use is_writeable() at this point, but that function requires the file to exist
    // Return null if the option is not set, which means it is unknown whether it works or not
    $this->working=get_option(self::$OPTION_WORKING,null);

    // The property needs to be casted to bool in order to use strict comparison at manageWorkingFlag()
    if($this->working!==null)
      $this->working=(bool)$this->working;
  }

  /**
   * Clears the log by removing the file or emptying its contents
   *
   * @return bool true if succeeded, false otherwise
   */
  public function clear(){
    // If the logging system has already been tested during the current request and it failed, don't try again
    if(!$this->firstAttempt&&!$this->working)
      return false;

    if($this->method===self::$METHOD_OPTION)
      // update_option() return false if the value doesn't change
      if($this->read())
        $success=update_option(self::$OPTION_CONTENTS,'');
      else
        $success=true;
    else{
      $success=@unlink($this->filename);

      // If the file cannot be removed, try to truncate it
      if(!$success)
        $success=(bool)(@file_put_contents($this->filename,'')!==
          false); // Typecasting the result to bool would make $success=false (it returns 0)
    }

    // Update the working flag if changed
    $this->manageWorkingFlag($success);

    return $success;
  }

  /**
   * Enables logging
   *
   * @since 1.3
   */
  public function enable(){
    $this->enabled=true;
  }

  /**
   * Appends a line to the log with the "error" severity level
   *
   * @param string $message the message to append
   * @return bool true if succeeded, false otherwise
   */
  public function error($message){
    if(!$this->enabled||$this->level<self::$LEVEL_ERROR)
      return false;

    return $this->writeLine($message,'E');
  }

  /**
   * Appends a line to the log with the "info" severity level
   *
   * @param string $message the message to append
   * @return bool true if succeeded, false otherwise
   */
  public function info($message){
    if(!$this->enabled || $this->level<self::$LEVEL_INFO)
      return false;

    $caller = @debug_backtrace()[1]['class'] . "[" . @debug_backtrace()[0]['line'] . "]"; // ." . @debug_backtrace()[1]['function'] . "() 

    return $this->writeLine($caller . ": " . $message,'I');
  }

  /**
   * Returns the "working" flag
   *
   * @return bool|null whether the log system is working or not - if null, it is unknown
   */
  public function isWorking(){
    return $this->working;
  }

  /**
   * Returns the log file contents, or an empty string if it does not exist
   */
  public function read(){
    if($this->method===self::$METHOD_OPTION)
      $log=get_option(self::$OPTION_CONTENTS);
    else
      $log=@file_get_contents($this->filename);

    // Both get_option() and file_get_contents() return false on error
    if($log===false) return '';

    $lines = explode("\n", $log); // echo('LINES: ' . count($lines) . '</br>');
    $maxLines = 100000;
    $startLine = -1 + ($maxLines * -1) + count($lines); // echo('STARTLINE: ' . $startLine);
  
    $firsts = array_slice($lines, $startLine, $maxLines);
    return implode("\n", $firsts);
    //return $log;
  }

  /**
   * Sets the logging level
   *
   * @since 1.3
   * @param int $level the desired logging level
   */
  public function setLevel($level){
    if($level<self::$LEVEL_ERROR)
      $level=self::$LEVEL_ERROR;
    else if($level>self::$LEVEL_INFO)
      $level=self::$LEVEL_INFO;

    $this->level=(int)$level;
  }

  /**
   * Sets the logging method
   *
   * @since 1.3
   * @param int $method the desired logging method
   */
  public function setMethod($method){
    if(!in_array($method,[self::$METHOD_FILE,self::$METHOD_OPTION]))
      $method=self::$METHOD_FILE;

    $this->method=(int)$method;
  }

  /**
   * Generates a random filename for the log
   *
   * <p>It is important to randomise this to prevent anyone from reading it by accessing eg
   * "http://website.com.au/wp-content/plugins/clickhome-myhome/myhome.log"</p>
   * <p>The URL for this file is not disclosed anywhere in the admin panel (although this wouldn't be a security
   * concern if SSL were used)</p>
   * <p>The probability of finding out this filename is 63^(-30)=1.04E-54</p>
   *
   * @uses mt_rand() to generate random string positions using the Mersenne Twister algorithm
   * @return string the full path of the random filename (eg
   *                "/var/www/website/wp-content/plugins/clickhome-myhome/dWedvsWHf3umN_SNqBejactVwmkMwb.log")
   */
  private function generateLogFilename(){
    $characters='0123456789abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ_';
    $lastCharacter=strlen($characters)-1;

    $filename=MH_PATH_HOME.'/';

    // Use 30 random characters from the given set ($characters)
    for($i=0;$i<30;$i++)
      $filename.=$characters[mt_rand(0,$lastCharacter)];

    $filename.='.log';

    return $filename;
  }

  /**
   * Manages the "working" flag - ie updates it in the event of a change
   *
   * @param bool $success whether the last operation succeeded
   */
  private function manageWorkingFlag($success){
    // If the last operation didn't succeed but the system was working so far (or viceversa), update the option which reflects this
    if($success!==$this->working){
      $this->working=$success;
      update_option(self::$OPTION_WORKING,$this->working);
    }

    // This isn't no longer the first attempt - if the system isn't working, don't try again during this request
    $this->firstAttempt=false;
  }

  /**
   * Appends a message line to the log
   *
   * Lines have the following format: "[2014-06-12 13:02:11 E] Error message" - the "E" is the "error" severity level
   *
   * @param string $message the message to append
   * @param string $level   severity level of the message
   * @return bool true if succeeded, false otherwise
   */
  private function writeLine($message,$level){
    // If the logging system has already been tested during the current request and it failed, don't try again
    if(!$this->firstAttempt&&!$this->working)
      return false;

    $date=current_time('mysql');
    $line=sprintf("[%s %s] %s\n",$date,$level,$message);

    if($this->method===self::$METHOD_OPTION){
      $log=$this->read();
      $log.=$line;

      $success=update_option(self::$OPTION_CONTENTS,$log);
    }
    else
      $success=(bool)@file_put_contents($this->filename,$line,
        FILE_APPEND); // file_put_contents() returns the number of bytes written if succeeded - typecast to boolean

    // Update the working flag if it changed
    $this->manageWorkingFlag($success);

    return $success;
  }

  /**
   * Full path of the log filename
   *
   * @var string
   */
  private $filename=null;

  /**
   * Whether the logging system is working or not - if null, it means it hasn't been tested yet
   *
   * @var bool|null
   */
  private $working=false;

  /**
   * Whether the next attempt on this request will be the first - used to try only once if an error occurs, and update
   * the working option accordingly
   *
   * @var bool
   */
  private $firstAttempt=true;

  /**
   * Whether the log is enabled or not
   *
   * @since 1.3
   * @var bool
   */
  private $enabled=false;

  /**
   * Logging level
   *
   * @since 1.3
   * @var int
   */
  private $level=0;

  /**
   * Logging method
   *
   * @since 1.3
   * @var int
   */
  private $method=0;
}
