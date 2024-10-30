<?php

/**
 * @property int    $displayid
 * @property string $name
 * @property string $address
 * @property int    $salespersonid
 * @property string $salesperson
 * @property int    $facadeid
 * @property string $phone1
 * @property string $phone2
 * @property string $email
 * @property int    $houseid
 * @property string $opentimessimple
 * @method static MyHomeDisplay[] all()
 * @method static MyHomeDisplay[] createFromArray(array $array,array $extraAttributes=[])
 */
class MyHomeDisplay extends MyHomeBaseModel{
  public static function deleteAll(){
    if(parent::deleteAll()===false)
      return false;

    // This has no effect if foreign keys are available (eg if using InnoDB in MySQL)
    if(MyHomeDisplayPlanoption::deleteAll()===false)
      return false;

    return true;
  }

  /**
   * @param mixed[] $conditions the search conditions
   * @return MyHomeDisplay[] the list of displays matching the conditions
   * @throws MyHomeException if the plan options list for a display is not available
   */
  public static function find(array $conditions){
    $displays=parent::find($conditions);

    if(!$displays)
      return $displays;

    foreach($displays as $display){
      $displayId=$display->displayid;

      $display->planoptions=MyHomeDisplayPlanoption::find(['displayid'=>$displayId]);
      if($display->planoptions===null)
        throw new MyHomeException(sprintf('Could not retrieve the plan options list for display %u',$displayId));
    }

    return $displays;
  }

  public static function findAllPages(){
    $pages=get_posts(['posts_per_page'=>-1,
      'post_type'=>'page',
      'post_status'=>'publish',
      'meta_key'=>self::$META_DISPLAY]);

    $indexedPages=[];

    foreach($pages as $page){
      $displayId=get_post_meta($page->ID,self::$META_DISPLAY,true);

      $indexedPages[$displayId]=$page;
    }

    return $indexedPages;
  }

  public static function insertAll(array $array){
    if(parent::insertAll($array)===false)
      return false;

    foreach($array as $display)
      if(!empty($display->planoptions))
        if(MyHomeDisplayPlanoption::insertAll($display->planoptions)===false)
          return false;

    return true;
  }

  protected static $NOT_NULL=['displayid',
    'name',
    'houseid'];

  private static $META_DISPLAY='_myhome_display';

  public function createPage($template=''){
    $postSettings=['post_title'=>$this->name,
      'post_content'=>sprintf('[MyHome.Display id=%u]',$this->displayid),
      'post_type'=>'page',
      'post_status'=>'publish',
      'comment_status'=>'closed',
      'page_template'=>$template];
    $result=wp_insert_post($postSettings,true);

    if(is_wp_error($result))
      throw new MyHomeException(sprintf('Could not create a page for display %u: %s',$this->displayid,
        $result->get_error_message()));

    if(update_post_meta($result,self::$META_DISPLAY,$this->displayid)===false)
      throw new MyHomeException(sprintf('Could not add meta data (ID) to the page for display %u',$this->displayid));

    return $result;
  }

  /**
   * @return WP_Post the WordPress page requested
   */
  public function findPage(){
    $pages=get_posts(['post_type'=>'page',
      'post_status'=>'publish',
      'meta_key'=>self::$META_DISPLAY,
      'meta_value'=>$this->displayid]);

    if($pages)
      return $pages[0];

    return null;
  }

  public function houseType(){
    $houseType=MyHomeHouseType::find(['houseid'=>$this->houseid]);

    if(is_array($houseType))
      return $houseType[0];

    return null;
  }

  protected function assignArray($attribute,array $array){
    $class=null;

    if($attribute==='planoptions')
      $class='MyHomeDisplayPlanoption';

    if($class!==null&&isset($this->displayid))
      $this->$attribute=call_user_func([$class,'createFromArray'],$array,['displayid'=>$this->displayid]);
  }
}
