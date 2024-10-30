<?php

/**
 * @property int                        $houseid
 * @property int                        $facadeid
 * @property string                     $name
 * @property string                     $description
 * @property int                        $facadedefault
 * @property float                      $pricefrom
 * @property MyHomeHouseTypeFacadeDoc[] $facadedocs
 */
class MyHomeHouseTypeFacade extends MyHomeBaseModel{
  public static function deleteAll(){
    if(parent::deleteAll()===false)
      return false;

    // This has no effect if foreign keys are available (eg if using InnoDB in MySQL)
    if(MyHomeHouseTypeFacadeDoc::deleteAll()===false)
      return false;

    return true;
  }

  public static function find(array $conditions){
    $facades=parent::find($conditions);

    if(!$facades)
      return $facades;

    foreach($facades as $facade){
      $houseTypeId=$facade->houseid;
      $facadeId=$facade->facadeid;

      $facade->facadedocs=MyHomeHouseTypeFacadeDoc::find(['houseid'=>$houseTypeId,
        'facadeid'=>$facadeId]);
      if($facade->facadedocs===null)
        throw new MyHomeException(sprintf('Could not retrieve the docs list for facade %u/%u',$houseTypeId,$facadeId));
    }

    return $facades;
  }

  public static function insertAll(array $array){
    if(parent::insertAll($array)===false)
      return false;

    foreach($array as $houseTypeFacade)
      if(!empty($houseTypeFacade->facadedocs))
        if(MyHomeHouseTypeFacadeDoc::insertAll($houseTypeFacade->facadedocs)===false)
          return false;

    return true;
  }

  protected static $NOT_NULL=['houseid',
    'facadeid',
    'name',
    'default'];

  protected function assignArray($attribute,array $array){
    $class=null;

    if($attribute==='facadedocs')
      $class='MyHomeHouseTypeFacadeDoc';

    if($class!==null&&isset($this->facadeid))
      $this->$attribute=call_user_func([$class,'createFromArray'],$array,['houseid'=>$this->houseid,
        'facadeid'=>$this->facadeid]);
  }
}
