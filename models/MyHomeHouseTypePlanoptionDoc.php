<?php

/**
 * @property int    $houseid
 * @property int    $planoptionid
 * @property string $title
 * @property string $type
 * @property int    $url
 */
class MyHomeHouseTypePlanoptionDoc extends MyHomeBaseModel{
  protected static $NOT_NULL=['houseid',
    'planoptionid',
    'title',
    'type',
    'url'];
}
