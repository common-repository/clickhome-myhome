<?php

/**
 * The MyHomeFacebook class
 *
 * @package    MyHome
 * @subpackage Classes
 * @since      1.4
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeFacebookImageMemory'))
  return;

use Facebook\FileUpload\FacebookFile;

/**
 * The MyHomeFacebookImageMemory class
 *
 * Class used to upload a file to Facebook without using the file system
 *
 * @since 1.4
 */
class MyHomeFacebookImageMemory extends FacebookFile{
  /**
   * {@inheritDoc}
   * @param string $contents
   */
  public function __construct($filePath,$contents){
    parent::__construct($filePath);

    $this->contents=$contents;
  }

  /**
   * {@inheritDoc}
   */
  public function open(){
  }

  /**
   * {@inheritDoc}
   */
  public function close(){
  }

  /**
   * {@inheritDoc}
   */
  public function getContents(){
    return $this->contents;
  }

  /**
   * @var string
   */
  private $contents;
}
