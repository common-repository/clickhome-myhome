<?php

/**
 * The ContractHeaderWidget class
 *
 * @package    MyHome
 * @subpackage Widgets
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ContractHeaderWidget'))
  return;

/**
 * The ContractHeaderWidget class
 *
 * Class for the Contract Header widget - displays the Contract Header shortcode
 */
class ContractHeaderWidget extends WP_Widget{
  /**
   * Constructor method
   *
   * Registers the widget
   */
  public function __construct(){
    parent::__construct
    ('', // ID base
      __('MyHome Contract Header','myHome'), // Name
      ['description'=>__('Displays the MyHome Contract Header when a user is logged in.','myHome')]); // Widget options
  }

  /**
   * Displays the widget settings form
   *
   * @param string[] $instance the widget instance
   * @return string
   */
  public function form($instance){
    if(isset($instance['title']))
      $title=esc_attr($instance['title']);
    else
      $title='';

    echo '<p>';
    printf('<label>%s<input class="widefat" id="%s" name="%s" placeholder="%s" type="text" value="%s"></label>',
      __('Widget title:','myHome'),$this->get_field_id('title'),$this->get_field_name('title'),
      __('Example: MyHome Contract','myHome'),esc_attr($title));
    echo '</p>';
  }

  /**
   * Updates the widget settings
   *
   * @param string[] $newInstance the new widget instance
   * @param string[] $oldInstance the previous widget instance
   * @return string[] the previous widget instance
   */
  function update($newInstance,$oldInstance){
    $oldInstance['title']=strip_tags($newInstance['title']);

    return $oldInstance;
  }

  /**
   * Displays the widget
   *
   * @uses MyHomeSession::guest() to check for a logged in client
   * @param string[] $args     the widget arguments
   * @param string[] $instance the widget instance
   */
  function widget($args,$instance){
    // If no session is detected, do not display anything
    if(myHome()->session->guest())
      return;

    echo $args['before_widget'];

    // Display the widget title, if set
    if(isset($instance['title'])){
      echo $args['before_title'];
      echo apply_filters('widget_title',$instance['title']);
      echo $args['after_title'];
    }

    echo do_shortcode('[MyHome.ContractHeader]');

    echo $args['after_widget'];
  }
}
