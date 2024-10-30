<?php

/**
 * @property int    $houseid
 * @property string $room
 * @property string $group
 */
class MyHomeHouseTypeRoom extends MyHomeBaseModel{
  public static $STANDARD_ROOMS=['ALFRESCO'=>'Alfresco',
    'ENSUITE'=>'Ensuite',
    'MEDIA'=>'Media Room',
    'STUDY'=>'Study'];

  protected static $NOT_NULL=['houseid',
    'room'];
}
