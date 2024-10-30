<?php

/**
 * @property string $url
 */
class MyHomeHouseTypeDoc extends MyHomeBaseModel{
  protected static $NOT_NULL=[
    'houseid',
    'title',
    'type',
    'url',
    'order'
  ];
}
