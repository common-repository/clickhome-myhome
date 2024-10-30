<?php

class MyHomeHouseTypeRoomMapper extends MyHomeBaseMapper{
  protected static $TABLE='houseTypesRooms';

  protected static $ATTRIBUTES_FORMATS=['houseid'=>self::TYPE_INT,
    'room'=>100,
    'group'=>100];

  protected static $ALIASES=['group'=>'roomgroup'];
}
