<?php

class MyHomeHouseTypeFacadeMapper extends MyHomeBaseMapper{
  protected static $TABLE='houseTypesFacades';

  protected static $ATTRIBUTES_FORMATS=['facadeid'=>self::TYPE_INT,
    'houseid'=>self::TYPE_INT,
    'name'=>255,
    'description'=>1000,
    'default'=>self::TYPE_BOOLEAN,
    'pricefrom'=>self::TYPE_FLOAT];

  protected static $ALIASES=['default'=>'facadedefault'];
}
