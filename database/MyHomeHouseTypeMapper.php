<?php

class MyHomeHouseTypeMapper extends MyHomeBaseMapper{
  protected static $TABLE='houseTypes';

  protected static $ATTRIBUTES_FORMATS=['houseid'=>self::TYPE_INT,
    'housename'=>100,
    'sizevalue'=>self::TYPE_INT,
    'bedqty'=>self::TYPE_INT,
    'bathqty'=>self::TYPE_FLOAT,
    'garageqty'=>self::TYPE_INT,
    'minwidth'=>self::TYPE_FLOAT,
    'size'=>20,
    'pricefrom'=>self::TYPE_FLOAT,
    'description'=>2000];

  /**
   * @return stdClass|false
   */
  public function maxValues(){
    global $wpdb;

    $wpdb->hide_errors();

    $query=<<<END
SELECT
  MAX(sizevalue) as maxSize,
  MAX(bedqty) as maxBedrooms,
  MAX(bathqty) as maxBathrooms,
  MAX(garageqty) as maxGarage,
  MAX(minwidth) as maxMinWidth,
  MAX(pricefrom) as maxMinPrice
FROM {$this->table()}
END;

    return $wpdb->get_row($query);
  }
}
