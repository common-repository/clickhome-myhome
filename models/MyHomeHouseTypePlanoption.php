<?php

/**
 * @property int                            $houseid
 * @property int                            $planoptionid
 * @property string                         $name
 * @property string                         $description
 * @property float                          $pricefrom
 * @property MyHomeHouseTypePlanoptionDoc[] $planoptiondocs
 */
class MyHomeHouseTypePlanoption extends MyHomeBaseModel{
  public static function deleteAll(){
    if(parent::deleteAll()===false)
      return false;

    // This has no effect if foreign keys are available (eg if using InnoDB in MySQL)
    if(MyHomeHouseTypePlanoptionDoc::deleteAll()===false)
      return false;

    return true;
  }

  public static function find(array $conditions){
    $planoptions=parent::find($conditions);

    if(!$planoptions)
      return $planoptions;

    foreach($planoptions as $planoption){
      $houseTypeId=$planoption->houseid;
      $planoptionId=$planoption->planoptionid;

      $planoption->planoptiondocs=MyHomeHouseTypePlanoptionDoc::find(['houseid'=>$houseTypeId,
        'planoptionid'=>$planoptionId]);
      if($planoption->planoptiondocs===null)
        throw new MyHomeException(sprintf('Could not retrieve the docs list for plan option (house type) %u/%u',
          $houseTypeId,$planoptionId));
    }

    return $planoptions;
  }

  public static function insertAll(array $array){
    if(parent::insertAll($array)===false)
      return false;

    foreach($array as $houseTypePlanoption)
      if(!empty($houseTypePlanoption->planoptiondocs))
        if(MyHomeHouseTypePlanoptionDoc::insertAll($houseTypePlanoption->planoptiondocs)===false)
          return false;

    return true;
  }

  protected static $NOT_NULL=['houseid',
    'planoptionid',
    'name'];

  protected function assignArray($attribute,array $array){
    $class=null;

    if($attribute==='planoptiondocs') // It should be "planoptiondocs"
      $class='MyHomeHouseTypePlanoptionDoc';

    if($class!==null&&isset($this->planoptionid))
      $this->$attribute=call_user_func([$class,'createFromArray'],$array,['houseid'=>$this->houseid,
        'planoptionid'=>$this->planoptionid]);
  }
}
