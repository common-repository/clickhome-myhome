<?php

class MyHomeHouseTypePlanoptionDocMapper extends MyHomeBaseMapper{
  protected static $TABLE='houseTypesPlanoptionsDocs';

  protected static $ATTRIBUTES_FORMATS=['houseid'=>self::TYPE_INT,
    'planoptionid'=>self::TYPE_INT,
    'title'=>100,
    'type'=>10,
    'url'=>1000
  ];
}
