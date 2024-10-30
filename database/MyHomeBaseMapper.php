<?php

abstract class MyHomeBaseMapper{
  const TYPE_BOOLEAN='b';

  const TYPE_FLOAT='f';

  const TYPE_INT='i';

  // These constants must not numeric

  protected static $ATTRIBUTES_FORMATS=null;

  protected static $ALIASES=null;

  protected static $TABLE=null;

  public function deleteAll(){
    return $this->doDeleteAll($this->table());
  }

  public function find($class,array $conditions){
    return $this->doFind($this->table(), $conditions, $class, array_keys(static::$ATTRIBUTES_FORMATS), isset(static::$ALIASES)?static::$ALIASES:[]);
  }

  public function insertAll(array $array){
    return $this->doInsertAll($array, $this->table(), static::$ATTRIBUTES_FORMATS, isset(static::$ALIASES)?static::$ALIASES:[]);
  }

  public function lastError(){
    global $wpdb;

    return $wpdb->last_error;
  }

  // Precondition: each object should be a valid model ($object->check() should return true for every item)
  protected function createInsertQuery($table, array $attributesFormat, array $objects, array $aliases=[]){
    if(!$objects)
      return null;

    global $wpdb;

    $wpdb->hide_errors();

    $values='';
    $params=[];

    foreach($objects as $object){
      $valuesRow=[];

      foreach($attributesFormat as $attribute=>$format){
        if(!isset($object->$attribute)){
          $valuesRow[]='null';

          continue;
        }

        $value=$object->$attribute;

        if(is_int($format)){
          $valuesRow[]='%s';
          $params[]=substr($value,0,$format);
        }
        else if($format===self::TYPE_INT){
          $valuesRow[]='%d';
          $params[]=$value;
        }
        else if($format===self::TYPE_FLOAT){
          $valuesRow[]='%f';
          $params[]=$value;
        }
        else if($format===self::TYPE_BOOLEAN){
          $valuesRow[]='%d';
          $params[]=$value?1:0;
        }
      }

      if($values)
        $values.=',';

      $values .= '(' . implode(',', $valuesRow) .')';
    }

    $attributes=array_keys($attributesFormat);

    if($aliases){
      $attributes=array_combine($attributes,$attributes);

      foreach($aliases as $attribute=>$alias)
        $attributes[$attribute]=$alias;

      $attributes=array_values($attributes);
    }

    $attributes = '`' . implode('`,`', $attributes) . '`';

    $query="INSERT INTO {$table} ({$attributes}) VALUES {$values}";
    $query=$wpdb->prepare($query,$params);

    return $query;
  }

  protected function createSelectQuery($table,array $conditions,array $attributes,array $aliases=[]){
    global $wpdb;

    $wpdb->hide_errors();

    if(!$aliases)
      $attributesList = '`' . implode('`,`', $attributes) . '`';
    else{
      $attributesList = [];

      foreach($attributes as $attribute){
        
        if(isset($aliases[$attribute]))
          // Quotes are mandatory here - aliases are used to avoid attribute names like "group"
          // Tables containing those attributes could not be created using quotes because of dbDelta() restrictions
          $attribute="{$aliases[$attribute]} AS `{$attribute}`";
        else
          $attribute = '`' . $attribute . '`';

        $attributesList[]= $attribute;
      }

      $attributesList=implode(',',$attributesList);
    }

    $query="SELECT {$attributesList} FROM {$table}";

    $params=[];

    if($conditions){
      $separator='WHERE';

      foreach($conditions as $attribute=>$value){
        $query.=sprintf(' %s %s=',$separator,$attribute);

        if(is_float($value)){
          $query.='%f';
          $params[]=$value;
        }
        else if(is_int($value)){
          $query.='%d';
          $params[]=$value;
        }
        else if(is_bool($value)){
          $query.='%d';
          $params[]=$value?1:0;
        }
        else{
          $query.='%s';
          $params[]=$value;
        }

        $separator='AND';
      }
    }

    if($params)
      $query=$wpdb->prepare($query,$params);

    return $query;
  }

  /**
   * @param string $table
   * @return false|int
   */
  protected function doDeleteAll($table){  //myHome()->log->info('doDeleteAll(' . $table . ')');
    global $wpdb;

    $wpdb->hide_errors();
    
    $query = $wpdb->query("DELETE FROM {$table}");
    myHome()->log->info('Deleted ' . $query . ' rows from ' . $table);
    return $query;
  }

  /**
   * @param string $table
   * @param array  $conditions
   * @param string $class
   * @param array  $attributes
   * @param array  $aliases
   * @return mixed[]|null
   */
  protected function doFind($table,array $conditions,$class,array $attributes,array $aliases=[]){
    global $wpdb;

    $wpdb->hide_errors();

    $query=$this->createSelectQuery($table,$conditions,$attributes,$aliases);
    //myHome()->log->info($query);

    $results=$wpdb->get_results($query);

    if(!is_array($results))
      return null;
    if($wpdb->last_error) // $wpdb->get_results() returns an empty array when performing a wrong query (eg if unknown fields are present)
      return null;

    return call_user_func([$class,'createFromArray'],$results);
  }

// @return int|false
  protected function doInsertAll(array $array, $table,array $attributesFormats, array $aliases=[]){
    global $wpdb;
    //myHome()->log->info(json_encode($array));

    $wpdb->hide_errors();

    $insertQuery = $this->createInsertQuery($table,$attributesFormats,$array,$aliases);
    //myHome()->log->info($insertQuery);

    // Nothing to insert
    if(!$insertQuery)
      return true;

    $query = $wpdb->query($insertQuery);
    if($query > 0)
      myHome()->log->info('Added ' . $query . ' rows to ' . $table);
    else
      myHome()->log->info('SQL Failed: ' . $insertQuery);

    return $query;
  }

  protected function table(){
    return myHome()->database->{static::$TABLE};
  }
}
