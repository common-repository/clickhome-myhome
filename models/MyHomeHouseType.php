<?php

/**
 * @property int                         $houseid
 * @property string                      $housename
 * @property int                         $sizevalue
 * @property int                         $bedqty
 * @property float                       $bathqty
 * @property int                         $garageqty
 * @property float                       $minwidth
 * @property string                      $size
 * @property float                       $pricefrom
 * @property string                      $description
 * @property MyHomeHouseTypeRoom[]       $rooms
 * @property MyHomeHouseTypeDoc[]        $housedocs
 * @property MyHomeHouseTypeFacade[]     $facades
 * @property MyHomeHouseTypePlanoption[] $planoptions
 * @method static MyHomeHouseType[] all()
 * @method static MyHomeHouseType[] createFromArray(array $array,array $extraAttributes=[])
 */
class MyHomeHouseType extends MyHomeBaseModel{
  public static function deleteAll(){
    if(parent::deleteAll()===false)
      return false;

    // This has no effect if foreign keys are available (eg if using InnoDB in MySQL)
    if(MyHomeHouseTypeRoom::deleteAll()===false)
      return false;
    if(MyHomeHouseTypeDoc::deleteAll()===false)
      return false;
    if(MyHomeHouseTypeFacade::deleteAll()===false)
      return false;
    if(MyHomeHouseTypePlanoption::deleteAll()===false)
      return false;

    return true;
  }

  /**
   * @param mixed[] $conditions the search conditions
   * @return MyHomeHouseType[] the list of house types matching the conditions
   * @throws MyHomeException if a list (rooms, documents, etc.) for a house type is not available
   */
  public static function find(array $conditions){
    $houseTypes=parent::find($conditions);

    if(!$houseTypes)
      return $houseTypes;

    foreach($houseTypes as $houseType){
      $houseTypeId=$houseType->houseid;

      $houseType->rooms=MyHomeHouseTypeRoom::find(['houseid'=>$houseTypeId]);
      if($houseType->rooms===null)
        throw new MyHomeException(sprintf('Could not retrieve the rooms list for house type %u',$houseTypeId));

      $houseType->housedocs=MyHomeHouseTypeDoc::find(['houseid'=>$houseTypeId]);
      if($houseType->housedocs===null)
        throw new MyHomeException(sprintf('Could not retrieve the documents list for house type %u',$houseTypeId));
      else {
        // Ensure order is the same everywhere
        //if($houseType->housename == 'Barwon 30') myHome()->log->info('beforeOrder: ' . json_encode($houseType->housedocs));
        usort($houseType->housedocs, function ($a, $b) {
          if($a->order == $b->order)
              return $a->title < $b->title ? 1 : -1;
          return $a->order > $b->order ? 1 : -1;
        });
        //if($houseType->housename == 'Barwon 30') myHome()->log->info('afterOrder: ' . json_encode($houseType->housedocs));
      }

      $houseType->facades=MyHomeHouseTypeFacade::find(['houseid'=>$houseTypeId]);
      if($houseType->facades===null)
        throw new MyHomeException(sprintf('Could not retrieve the facades list for house type %u',$houseTypeId));

      $houseType->planoptions=MyHomeHouseTypePlanoption::find(['houseid'=>$houseTypeId]);
      if($houseType->planoptions===null)
        throw new MyHomeException(sprintf('Could not retrieve the plan options list for house type %u',$houseTypeId));
    }

    return $houseTypes;
  }

  /**
   * This includes pages for deleted IDs as well
   *
   * @return WP_Post[]
   */
  public static function findAllPages(){
    $pages=get_posts(['posts_per_page'=>-1,
      'post_type'=>'page',
      'post_status'=>'publish',
      'meta_key'=>self::$META_HOUSE_TYPE]);

    $indexedPages=[];

    foreach($pages as $page){
      $houseTypeId=get_post_meta($page->ID,self::$META_HOUSE_TYPE,true);

      $indexedPages[$houseTypeId]=$page;
    }

    return $indexedPages;
  }

  public static function insertAll(array $array){
    if(parent::insertAll($array)===false)
      return false;

    foreach($array as $houseType){
      if(!empty($houseType->rooms))
        if(MyHomeHouseTypeRoom::insertAll($houseType->rooms)===false)
          return false;

      if(!empty($houseType->housedocs))
        if(MyHomeHouseTypeDoc::insertAll($houseType->housedocs)===false)
          return false;

      if(!empty($houseType->facades))
        if(MyHomeHouseTypeFacade::insertAll($houseType->facades)===false)
          return false;

      if(!empty($houseType->planoptions))
        if(MyHomeHouseTypePlanoption::insertAll($houseType->planoptions)===false)
          return false;
    }

    return true;
  }

  /**
   * @return stdClass|false
   */
  public static function maxValues(){
    /**
     * @var MyHomeHouseTypeMapper $mapper
     */
    $mapper=self::mapper();

    return $mapper->maxValues();
  }

  protected static $NOT_NULL=['houseid',
    'housename'];

  private static $META_HOUSE_TYPE='_myhome_house_type';

  public function createPage($template=''){
    $postSettings=['post_title'=>$this->housename,
      'post_content'=>sprintf('[MyHome.HouseType id=%u]',$this->houseid),
      'post_type'=>'page',
      'post_status'=>'publish',
      'comment_status'=>'closed',
      'page_template'=>$template];
    $result=wp_insert_post($postSettings,true);

    if(is_wp_error($result))
      throw new MyHomeException(sprintf('Could not create a page for house type %u: %s',$this->houseid,
        $result->get_error_message()));

    if(update_post_meta($result,self::$META_HOUSE_TYPE,$this->houseid)===false)
      throw new MyHomeException(sprintf('Could not add meta data (ID) to the page for house type %u',$this->houseid));

    return $result;
  }

  // This array is not loaded into the object (unlike rooms, housedocs, facades, and planoptions) in find() to be consistent with assignArray(), which skips the (abbreviated) displays list coming from the API
  public function displays(){
    return MyHomeDisplay::find(['houseid'=>$this->houseid]);
  }

  /**
   * @return WP_Post the WordPress page requested
   */
  public function findPage(){
    $pages=get_posts(['post_type'=>'page',
      'post_status'=>'publish',
      'meta_key'=>self::$META_HOUSE_TYPE,
      'meta_value'=>$this->houseid]);

    if($pages)
      return $pages[0];

    return null;
  }

  public function hasRoom($group){
    foreach($this->rooms as $room)
      if($room->group===$group)
        return true;

    return false;
  }

  public function hasStudyRoom(){
    return $this->hasRoom('STUDY');
  }

  public function hasTheatreRoom(){
    return $this->hasRoom('THEATRE');
  }

  /**
   * @return string[]
   */
  public function roomsList(){
    $roomsList=[];

    foreach($this->rooms as $room)
      $roomsList[]=$room->group;

    return $roomsList;
  }

  protected function assignArray($attribute,array $array){
    $class=null;

    switch($attribute){
      case 'rooms':
        $class='MyHomeHouseTypeRoom';
        break;
      case 'housedocs':
        $class='MyHomeHouseTypeDoc';
        break;
      case 'facades':
        $class='MyHomeHouseTypeFacade';
        break;
      case 'planoptions':
        $class='MyHomeHouseTypePlanoption';
        break;
    }

    if($class!==null&&isset($this->houseid))
      $this->$attribute=call_user_func([$class,'createFromArray'],$array,['houseid'=>$this->houseid]);
  }
}
