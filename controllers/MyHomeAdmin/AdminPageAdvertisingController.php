<?php

/**
 * The AdminPageAdvertisingController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageAdvertisingController'))
  return;

/**
 * The AdminPageAdvertisingController class
 *
 * Controller for the Advertising admin page view
 *
 * @since 1.3
 */
class AdminPageAdvertisingController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeAdvertising';

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $nextCronjob = myHome()->advertising->nextCronjob();
    $databaseStatus = myHome()->database->check();
    $houseTypes = [];
    $displays = [];

    if($databaseStatus){
      $allHouseTypes=MyHomeHouseType::all();

      if($allHouseTypes){
        foreach($allHouseTypes as $houseType){
          $page=$houseType->findPage();

          $houseTypes[$houseType->houseid]=[
            'name'=>$houseType->housename,
            'pageId'=>$page?$page->ID:null,
            'url'=>$page?get_permalink($page->ID):''
          ];
        }

        $allDisplays=MyHomeDisplay::all();

        if($allDisplays)
          foreach($allDisplays as $display){
            $page=$display->findPage();

            $displays[$display->displayid]=['name'=>$display->name,
              'pageId'=>$page?$page->ID:null,
              'url'=>$page?get_permalink($page->ID):''];
          }
        else if($allDisplays===null)
          $databaseStatus=false;
      }
      else if($allHouseTypes===null)
        $databaseStatus=false;
    }

    $defaultTemplateHouseTypes=myHome()->options->getAdvertisingDefaultTemplateHouseTypes();
    $defaultTemplateDisplays=myHome()->options->getAdvertisingDefaultTemplateDisplays();

    $this->loadView('adminPageAdvertising','MyHomeAdmin',
      compact('nextCronjob','databaseStatus','houseTypes','displays','defaultTemplateHouseTypes',
        'defaultTemplateDisplays'));
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    list($submit)=$this->extractParams(['myHomeSubmit'],$params);

    if($submit==='update')
      $this->updateDatabase();
    else if($submit==='regenerate')
      $this->regenerateDatabase();
    else // Defaults to save changes
      $this->saveChanges($params);
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  private function regenerateDatabase(){
    try{
      myHome()->database->createTables(true,true);
    }
    catch(MyHomeException $e){
      $this->flashVar('error', sprintf(__('An error occurred while regenerating the database (%s)','myHome'),$e->getMessage()));

      throw $e;
    }

    $this->flashVar('regenerated',true);
  }

  private function saveChanges(array $params){
    list($cronjobEnabled,$defaultTemplateHouseTypes,$defaultTemplateDisplays)=
      $this->extractParams(['myHomeCronjobEnabled',
        'myHomeDefaultTemplateHouseTypes',
        'myHomeDefaultTemplateDisplays'],$params);

    if($cronjobEnabled&&!myHome()->options->getAdvertisingApiKey()){
      myHome()->advertising->clearCronjob();

      $this->flashVar('error',__('Automatic updates require a valid advertising API key','myHome'));

      return;
    }

    if($cronjobEnabled)
      myHome()->advertising->setCronjob();
    else
      myHome()->advertising->clearCronjob();

    myHome()->options->setAdvertisingDefaultTemplateHouseTypes($defaultTemplateHouseTypes);
    myHome()->options->setAdvertisingDefaultTemplateDisplays($defaultTemplateDisplays);

    myHome()->options->saveAll();

    // Remember the fact that the settings were successfully saved
    $this->flashVar('saved',true);
  }

  private function updateDatabase(){
    try{
      $result=myHome()->advertising->updateDatabase();
    }
    catch(MyHomeException $e){
      $this->flashVar('error',
        sprintf(__('An error occurred while updating the database (%s)','myHome'),$e->getMessage()));

      throw $e;
    }

    if($result != null)
      $this->flashVar('updated',myHome()->advertising->updateDatabaseResultString($result));
    else
      $this->flashVar('error', 'Database update exited with no results');

    if($result['unknownDocTypes'])
      $this->flashVar('error',
        __('Some documents were not saved because of unknown type - check the log for more details','myHome'));
  }
}
