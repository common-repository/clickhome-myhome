<?php

/**
 * The MyHomeDatabase class
 *
 * @package    MyHome
 * @subpackage Classes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeDatabase'))
  return;

/**
 * The MyHomeDatabase class
 *
 * Provides some support functions for the MyHome database: check, creation, generation of table names, etc.
 *
 * @since 1.3
 * @property-read string $houseTypes
 * @property-read string $houseTypesRooms
 * @property-read string $houseTypesDocs
 * @property-read string $houseTypesFacades
 * @property-read string $houseTypesFacadesDocs
 * @property-read string $houseTypesPlanoptions
 * @property-read string $houseTypesPlanoptionsDocs
 * @property-read string $displays
 * @property-read string $displaysPlanoptions
 * @property-read string $displaysPlanoptionsDocs
 */
class MyHomeDatabase{
  /**
   * Database version
   */
  private static $DATABASE_VERSION = 2;

  /**
   * Name of the option storing the database version
   */
  private static $DATABASE_VERSION_OPTION='myhome_database_version';

  /**
   * Table names
   *
   * Filled up later - access to $wpdb is needed
   */
  private static $TABLES=[];

  /**
   * Constructor method
   *
   * Initialises the table names array
   */
  public function __construct(){
    $tableNames=[
      'houseTypes'=>'housedetails',
      'houseTypesRooms'=>'housedetails_rooms',
      'houseTypesDocs'=>'housedetails_housedocs',
      'houseTypesFacades'=>'housedetails_facades',
      'houseTypesFacadesDocs'=>'housedetails_facades_docs',
      'houseTypesPlanoptions'=>'housedetails_planoptions',
      'houseTypesPlanoptionsDocs'=>'housedetails_planoptions_docs',
      'displays'=>'displays',
      'displaysPlanoptions'=>'displays_planoptions',
      'displaysPlanoptionsDocs'=>'displays_planoptions_docs'
    ];

    global $wpdb;

    // Using $wpdb->prefix allows each site in a multisite installation to have its own database
    foreach($tableNames as $table=>$name)
      self::$TABLES[$table]=sprintf('%smh_%s',$wpdb->prefix,$name);
  }

  /**
   * Used to retrieve read-only properties (table names)
   *
   * @param string $name name of the property
   * @uses MyHomeDatabase::$TABLES
   * @return string|null the table name, if available, or null otherwise
   */
  public function __get($name){
    if(isset(self::$TABLES[$name]))
      return self::$TABLES[$name];

    // Do not throw an exception, as this is more like a standard PHP error
    trigger_error('Undefined property: '.$name);

    return null;
  }

  /**
   * Checks if the database contains all the required tables
   *
   * @return bool
   */
  public function check(){ //myHome()->log->info('check()');
    global $wpdb;

    $wpdb->hide_errors();

    if(self::$DATABASE_VERSION != $this->version()) 
      return false;

    foreach(self::$TABLES as $table) {
      //myHome()->log->info($table . " dbCols: " . $wpdb->query("DESC {$table}") . ", modelAtts: " . 0);
      if(!$wpdb->query("DESC {$table}"))
        return false;
    }

    return true;
  }

  /**
   * Creates the MyHome database
   *
   * @param bool $forceCreate try to create the tables even if the database version is correct (Optional - default
   *                          false)
   * @param bool $dropTables  drop the tables before creating them (Optional - default false)
   * @uses dbDelta() to create or upgrade the tables
   */
  public function createTables($forceCreate=false,$dropTables=false) {
    if(!$forceCreate)
      // Return if the database structure is up to date
      if($this->version()>=self::$DATABASE_VERSION)
        return;

    // Required to use dbDelta()
    require_once ABSPATH.'wp-admin/includes/upgrade.php';

    global $wpdb;

    $wpdb->hide_errors();

    if($dropTables){
      // Drop tables in this order
      $tablesToDelete=[$this->displaysPlanoptionsDocs,
        $this->displaysPlanoptions,
        $this->displays,
        $this->houseTypesPlanoptionsDocs,
        $this->houseTypesPlanoptions,
        $this->houseTypesFacadesDocs,
        $this->houseTypesFacades,
        $this->houseTypesDocs,
        $this->houseTypesRooms,
        $this->houseTypes];

      foreach($tablesToDelete as $table) {
        myHome()->log->info('dropTable: ' . $table);
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
      }
    }

    // If a charset and collate is set, use it in these tables
    $charsetCollate='';
    if(!empty($wpdb->charset))
      $charsetCollate="DEFAULT CHARACTER SET {$wpdb->charset}";
    if(!empty($wpdb->collate))
      $charsetCollate.=" COLLATE {$wpdb->collate}";

    myHome()->log->info('createTables() ' . $charsetCollate);

    dbDelta(<<<END
CREATE TABLE {$this->houseTypes} (
  `houseid` int(10) unsigned NOT NULL,
  `housename` varchar(100) NOT NULL,
  `sizevalue` int(10) unsigned,
  `bedqty` int(10) unsigned,
  `bathqty` decimal(3,1) UNSIGNED,
  `garageqty` int(10) unsigned,
  `minwidth` decimal(6,2) UNSIGNED,
  `size` varchar(20),
  `pricefrom` decimal(10,2) UNSIGNED,
  `description` varchar(2000),
  PRIMARY KEY  (houseid)
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->houseTypesRooms} (
  `houseid` int(10) unsigned NOT NULL,
  `room` varchar(100) NOT NULL,
  `roomgroup` varchar(100),
  PRIMARY KEY  (houseid,room),
  CONSTRAINT FOREIGN KEY (houseid) REFERENCES {$this->houseTypes} (houseid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->houseTypesDocs} (
  `id` INT NOT NULL AUTO_INCREMENT,
  `houseid` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `order` int(100) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (houseid) REFERENCES {$this->houseTypes} (houseid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->houseTypesFacades} (
  `houseid` int(10) unsigned NOT NULL,
  `facadeid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(1000),
  `facadedefault` tinyint NOT NULL,
  `pricefrom` decimal(10,2) UNSIGNED,
  PRIMARY KEY  (houseid,facadeid),
  CONSTRAINT FOREIGN KEY (houseid) REFERENCES {$this->houseTypes} (houseid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->houseTypesFacadesDocs} (
  `id` INT NOT NULL AUTO_INCREMENT,
  `houseid` int(10) unsigned NOT NULL,
  `facadeid` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL,
  `url` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (houseid,facadeid) REFERENCES {$this->houseTypesFacades} (houseid,facadeid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->houseTypesPlanoptions} (
  `houseid` int(10) unsigned NOT NULL,
  `planoptionid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(1000),
  `pricefrom` decimal(10,2) UNSIGNED,
  PRIMARY KEY  (houseid,planoptionid),
  CONSTRAINT FOREIGN KEY (houseid) REFERENCES {$this->houseTypes} (houseid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->houseTypesPlanoptionsDocs} (
  `id` INT NOT NULL AUTO_INCREMENT,
  `houseid` int(10) unsigned NOT NULL,
  `planoptionid` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL,
  `url` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (houseid,planoptionid) REFERENCES {$this->houseTypesPlanoptions} (houseid,planoptionid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->displays} (
  `displayid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(100),
  `salespersonid` int(10) unsigned,
  `salesperson` varchar(100),
  `facadeid` int(10) unsigned,
  `phone1` varchar(20),
  `phone2` varchar(20),
  `email` varchar(100),
  `houseid` int(10) unsigned NOT NULL,
  `opentimessimple` varchar(255),
  PRIMARY KEY  (displayid),
  CONSTRAINT FOREIGN KEY (houseid,facadeid) REFERENCES {$this->houseTypesFacades} (houseid,facadeid) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (houseid) REFERENCES {$this->houseTypes} (houseid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->displaysPlanoptions} (
  `displayid` int(10) unsigned NOT NULL,
  `planoptionid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(1000),
  `pricefrom` decimal(10,2) UNSIGNED,
  PRIMARY KEY  (displayid,planoptionid),
  CONSTRAINT FOREIGN KEY (displayid) REFERENCES {$this->displays} (displayid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    dbDelta(<<<END
CREATE TABLE {$this->displaysPlanoptionsDocs} (
  `id` INT NOT NULL AUTO_INCREMENT,
  `displayid` int(10) unsigned NOT NULL,
  `planoptionid` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL,
  `url` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (displayid,planoptionid) REFERENCES {$this->displaysPlanoptions} (displayid,planoptionid) ON UPDATE CASCADE ON DELETE CASCADE
) {$charsetCollate};
END
    );

    update_option(self::$DATABASE_VERSION_OPTION,self::$DATABASE_VERSION);
  }

  /**
   * @return int|null
   */
  public function version(){
    return get_option(self::$DATABASE_VERSION_OPTION,null);
  }
}
