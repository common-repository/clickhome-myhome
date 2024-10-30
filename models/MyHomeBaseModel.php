<?php

abstract class MyHomeBaseModel{
  public static function all(){
    return static::find([]);
  }

  public static function createFromArray(array $array,array $extraAttributes=[]){
    $objects=[];

    foreach($array as $modelData){
      $object=new static($modelData,$extraAttributes);

      if($object->check())
        $objects[]=$object;
    }

    return $objects;
  }

  public static function deleteAll(){
    $mapper=self::mapper();

    $result=$mapper->deleteAll();

    if($result===false)
      myHome()->log->error(sprintf(__('Database error (DELETE, %s): %s','myHome'),get_called_class(),
        $mapper->lastError()));

    return $result;
  }

  public static function find(array $conditions){
    $mapper=self::mapper();

    $result=$mapper->find(get_called_class(),$conditions);

    if($result===null)
      myHome()->log->error(sprintf(__('Database error (SELECT, %s): %s','myHome'),get_called_class(),
        $mapper->lastError()));

    return $result;
  }

  public static function insertAll(array $array){
    $mapper=self::mapper();

    $result=$mapper->insertAll($array);

    if($result===false)
      myHome()->log->error(sprintf(__('Database error (INSERT, %s): %s','myHome'),get_called_class(),
        $mapper->lastError()));

    return $result;
  }

  /**
   * @return MyHomeBaseMapper the required mapper instance
   */
  protected static function mapper(){
    $class=get_called_class();

    if(!isset(self::$mapperInstances[$class])){
      $mapperClass=$class.'Mapper';
      self::$mapperInstances[$class]=new $mapperClass;
    }

    return static::$mapperInstances[$class];
  }

  protected static $NOT_NULL=[];

  protected static $mapperInstances=[];

  public function __construct(stdClass $modelData,array $extraAttributes){
    // Used for child attributes (eg houseid in a facade object)
    // Assign this before looping through $modelData, as some assignArray() methods may required one of these values
    foreach($extraAttributes as $attribute=>$value)
      $this->$attribute=$value;

    foreach($modelData as $attribute=>$value)
      if(is_array($value))
        $this->assignArray($attribute,$value);
      else if(is_object($value))
        $this->assignObject($attribute,$value);
      else
        $this->$attribute=$value;
  }

  protected function assignArray($attribute,array $array){
  }

  protected function assignObject($attribute,array $array){
  }

  protected function check(){
    if(isset(static::$NOT_NULL))
      foreach(static::$NOT_NULL as $attribute)
        if(!isset($this->$attribute))
          return false;

    return true;
  }
}
