<?php

/**
 * @property int    $houseid
 * @property int    $facadeid
 * @property string $title
 * @property string $type
 * @property int    $url
 */
class MyHomeHouseTypeFacadeDoc extends MyHomeBaseModel{
  protected static $NOT_NULL=['houseid',
    'facadeid',
    'title',
    'type',
    'url'];
}
