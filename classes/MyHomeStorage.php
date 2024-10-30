<?php

/**
 * The MyHomeStorage class
 *
 * @package    MyHome
 * @subpackage Classes
 * @since      1.2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeStorage'))
  return;

/**
 * The MyHomeStorage class
 *
 * <p>Provides support for the storage of temporary variables (linked to PHP sessions)</p>
 * <p>Used by MyHomeSession to store session information (session ID, authentication headers, etc.)</p>
 * <p>Used by controllers to store flash data and cache API responses</p>
 *
 * @since 1.2
 * @uses  $_SESSION
 */
class MyHomeStorage{
  /**
   * Name of the storage container array within $_SESSION
   */
  private static $MYHOME_CONTAINER='myhome';

  /**
   * Constructor method
   *
   * It initialises the PHP session, if none is available, and creates the storage container, if required
   *
   * @uses session_id() to check for a valid PHP session (session_status() requires PHP >=5.4.0)
   */
  public function __construct(){
    if(session_id()==='')
      session_start();

    // Create the container if it doesn't exist
    if(!isset($_SESSION[self::$MYHOME_CONTAINER]))
      $this->resetContainer();

    // Purge expired variables
    $this->purgeContainer();
  }

  /**
   * Deletes a variable
   *
   * @uses MyHomeStorage::get()
   * @param string $name the variable name
   * @return mixed|null the previous value, if present, or null otherwise
   * @throws MyHomeException if no valid PHP session is found
   */
  public function delete($name){
    $this->checkSession();

    $previous=$this->get($name); // Flash data is deleted at this point
    unset($_SESSION[self::$MYHOME_CONTAINER][$name]);

    return $previous;
  }

  /**
   * Checks for the existence of a variable
   *
   * @param string $name the variable name
   * @return bool whether the variable exists or not
   * @throws MyHomeException if no valid PHP session is found
   */
  public function exists($name){
    $this->checkSession();

    return isset($_SESSION[self::$MYHOME_CONTAINER][$name]);
  }

  /**
   * Retrieves a variable
   *
   * If the variable contains flash data, it is deleted from the container
   *
   * @uses MyHomeStorage::exists()
   * @param string     $name    the variable name
   * @param mixed|null $default the value to return if the variable is not found (Optional - default null)
   * @return mixed the value, if present, or $default otherwise
   * @throws MyHomeException if no valid PHP session is found
   */
  public function get($name,$default=null){
    $this->checkSession();

    // Return $default if the variable is not found
    if(!$this->exists($name))
      return $default;

    $value=$_SESSION[self::$MYHOME_CONTAINER][$name]['value'];

    // Delete the variable if it contains flash data
    if($_SESSION[self::$MYHOME_CONTAINER][$name]['flash'])
      unset($_SESSION[self::$MYHOME_CONTAINER][$name]);

    return $value;
  }

  /**
   * Stores a variable
   *
   * Each variable has the following associated data:
   * <ul>
   * <li>Array key: variable name</li>
   * <li>value: stored value</li>
   * <li>expiration: the expiration timestamp (null if the variable doesn't expire)</li>
   * <li>flash: whether the variable contains flash data (as in frameworks like Laravel or Symfony, these variables are
   * deleted upon read)</li>
   * </ul>
   *
   * @uses MyHomeStorage::exists()
   * @param string   $name    the variable name
   * @param mixed    $value   the value to be stored
   * @param bool     $replace whether the previous value with the same name should be replaced, if already present
   *                          (Optional - default true)
   * @param int|null $timeout the timeout limit for this variable, in seconds, or null if it doesn't expire (Optional -
   *                          default null)
   * @param bool     $flash   whether the variable contains flash data (Optional - default false)
   * @return mixed|null the previous value, if present, or null otherwise
   * @throws MyHomeException if no valid PHP session is found
   */
  public function put($name,$value,$replace=true,$timeout=null,$flash=false){
    $this->checkSession();

    // get() is not used here as it could delete flash data, making $replace=false useless
    if(!$this->exists($name))
      $previous=null;
    else
      $previous=$_SESSION[self::$MYHOME_CONTAINER][$name]['value'];

    if($replace||!$this->exists($name))
      $_SESSION[self::$MYHOME_CONTAINER][$name]=['value'=>$value,
        'expiration'=>$timeout!==null?time()+$timeout:null,
        'flash'=>$flash];

    return $previous;
  }

  /**
   * Resets the storage container
   */
  public function resetContainer(){
    $_SESSION[self::$MYHOME_CONTAINER]=[];
  }

  /**
   * Verifies that a valid PHP session is active
   *
   * @uses session_id() to check for a valid PHP session (session_status() requires PHP >=5.4.0)
   * @throws MyHomeException if no valid PHP session is found
   */
  private function checkSession(){
    if(session_id()==='')
      throw new MyHomeException('A valid PHP session is required');
  }

  /**
   * Removes expired values from the container
   */
  private function purgeContainer(){
    // Values with expiration timestamps lesser or equal than $now are expired
    $now=time();

    foreach($_SESSION[self::$MYHOME_CONTAINER] as $name=>$variableData)
      if($variableData['expiration']!==null&&$now>=$variableData['expiration'])
        unset($_SESSION[self::$MYHOME_CONTAINER][$name]);
  }
}
