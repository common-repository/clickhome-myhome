<?php

class MyHomeHouseTypeFacadeDocMapper extends MyHomeBaseMapper{
  protected static $TABLE='houseTypesFacadesDocs';

  protected static $ATTRIBUTES_FORMATS=['houseid'=>self::TYPE_INT,
    'facadeid'=>self::TYPE_INT,
    'title'=>100,
    'type'=>10,
    'url'=>1000
  ];
}
