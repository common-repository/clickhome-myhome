<?php

class MyHomeDisplayMapper extends MyHomeBaseMapper{
  protected static $TABLE='displays';

  protected static $ATTRIBUTES_FORMATS=['displayid'=>self::TYPE_INT,
    'name'=>255,
    'address'=>100,
    'salespersonid'=>self::TYPE_INT,
    'salesperson'=>100,
    'facadeid'=>self::TYPE_INT,
    'phone1'=>20,
    'phone2'=>20,
    'email'=>100,
    'houseid'=>self::TYPE_INT,
    'opentimessimple'=>255];
}
