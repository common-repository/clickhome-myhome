<?php

class MyHomeDisplayPlanoptionMapper extends MyHomeBaseMapper{
  protected static $TABLE='displaysPlanoptions';

  protected static $ATTRIBUTES_FORMATS=['planoptionid'=>self::TYPE_INT,
    'displayid'=>self::TYPE_INT,
    'name'=>255,
    'description'=>1000,
    'pricefrom'=>self::TYPE_FLOAT];
}
