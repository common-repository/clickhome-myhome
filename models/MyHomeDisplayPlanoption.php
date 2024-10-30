<?php

/**
 * @property int $displayid
 */
class MyHomeDisplayPlanoption extends MyHomeBaseModel{
  public static function deleteAll(){
    if(parent::deleteAll()===false)
      return false;

    // This has no effect if foreign keys are available (eg if using InnoDB in MySQL)
    if(MyHomeDisplayPlanoptionDoc::deleteAll()===false)
      return false;

    return true;
  }

  public static function find(array $conditions){
    $planoptions=parent::find($conditions);

    if(!$planoptions)
      return $planoptions;

    foreach($planoptions as $planoption){
      $displayId=$planoption->displayid;
      $planoptionId=$planoption->planoptionid;

      $planoption->planoptiondocs=MyHomeDisplayPlanoptionDoc::find(['displayid'=>$displayId,
        'planoptionid'=>$planoptionId]);
      if($planoption->planoptiondocs===null)
        throw new MyHomeException(sprintf('Could not retrieve the docs list for plan option (display) %u/%u',$displayId,
          $planoptionId));
    }

    return $planoptions;
  }

  public static function insertAll(array $array){
    if(parent::insertAll($array)===false)
      return false;

    foreach($array as $displayPlanoption)
      if(!empty($displayPlanoption->planoptiondocs))
        if(MyHomeDisplayPlanoptionDoc::insertAll($displayPlanoption->planoptiondocs)===false)
          return false;

    return true;
  }

  protected static $NOT_NULL=['displayid',
    'planoptionid',
    'name'];

  protected function assignArray($attribute,array $array){
    $class=null;

    if($attribute==='planoptiondocs')
      $class='MyHomeDisplayPlanoptionDoc';

    if($class!==null&&isset($this->planoptionid))
      $this->$attribute=call_user_func([$class,'createFromArray'],$array,['displayid'=>$this->displayid,
        'planoptionid'=>$this->planoptionid]);
  }
}
