<?php

class MyHomeHouseTypePlanoptionMapper extends MyHomeBaseMapper{
  protected static $TABLE='houseTypesPlanoptions';

  protected static $ATTRIBUTES_FORMATS=['planoptionid'=>self::TYPE_INT,
    'houseid'=>self::TYPE_INT,
    'name'=>255,
    'description'=>1000,
    'pricefrom'=>self::TYPE_FLOAT];
}
