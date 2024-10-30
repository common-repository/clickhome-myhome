<?php

class MyHomeDisplayPlanoptionDocMapper extends MyHomeBaseMapper{
  protected static $TABLE='displaysPlanoptionsDocs';

  protected static $ATTRIBUTES_FORMATS=['displayid'=>self::TYPE_INT,
    'planoptionid'=>self::TYPE_INT,
    'title'=>100,
    'type'=>10,
    'url'=>1000
  ];
}
