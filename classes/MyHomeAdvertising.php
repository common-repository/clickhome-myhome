<?php

/**
 * The MyHomeAdvertising class
 *
 * @package    MyHome
 * @subpackage Classes
 * @since      1.3
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('MyHomeAdvertising'))
  return;

/**
 * The MyHomeAdvertising class
 *
 * Includes several functions related to the advertising module: cronjobs, attachments for pictures (housedocs, etc.),
 * etc.
 *
 * @since 1.3
 * @todo  if all docs tables are replaced by junction tables and a unique docs table, move these methods to the
 *        MyHomeDoc class
 */
class MyHomeAdvertising{
  /**
   * Name of the cronjob action hook (no underscores or uppercase, as recommended at codex.wordpress.org)
   */
  private static $CRONJOB_HOOK='MyHome_Advertising'; //'myhomeadvertising';

  private static $MIME_TYPES=['jpg'=>'image/jpeg',
    'jpeg'=>'image/jpeg',
    'png'=>'image/png',
    'bmp'=>'image/bmp',
    'gif'=>'image/gif'];

  private static $DOC_META_ATTACHMENT='_myhome_doc';

  public function clearCronjob(){
    if($this->nextCronjob())
      wp_clear_scheduled_hook(self::$CRONJOB_HOOK);
  }

  /**
   * @return WP_Post[]
   */
  public function docFindAllAttachments(){
    $attachments=get_posts(['posts_per_page'=>-1,
      'post_type'=>'attachment',
      'post_status'=>'inherit',
      'meta_key'=>self::$DOC_META_ATTACHMENT]);

    $indexedAttachments=[];

    foreach($attachments as $attachment){
      $docId=get_post_meta($attachment->ID,self::$DOC_META_ATTACHMENT,true);

      $indexedAttachments[$docId]=$attachment;
    }

    return $indexedAttachments;
  }

  /**
   * @param string $documentUrl
   * @return WP_Post|null
   */
  public function docFindAttachment($documentUrl){
    $attachments = get_posts([
      'post_type'=>'attachment',
      'post_status'=>'inherit',
      'meta_key'=>self::$DOC_META_ATTACHMENT,
      'meta_value'=>$documentUrl
    ]);

    if($attachments) { //myHome()->log->info('docFindAttachment (' . $documentUrl . '): ' . count($attachments));
      /*foreach($attachments as $doc) {
        myHome()->log->info(json_encode($doc, JSON_PRETTY_PRINT));
      }*/
      return $attachments[0];
    } else { // echo('docFindAttachment Failed: ' . $documentUrl);
      return false;
    }
  }

  public function nextCronjob(){
    return wp_next_scheduled(self::$CRONJOB_HOOK);
  }

  /**
   * Registers the appropriate WordPress hooks
   *
   * The updateDatabaseCronjob() method is attached to the cronjob hook
   */
  public function setupHooks(){
    add_action(self::$CRONJOB_HOOK,[$this,'updateDatabaseCronjob']);
  }

  public function setCronjob(){
    $this->clearCronjob();

    wp_schedule_event(time()+86400,'daily',self::$CRONJOB_HOOK);
  }

  public function updateDatabase(){ //myHome()->log->info('updateDatabase()');
    if(!$this->checkPermalinkStructure())
      throw new MyHomeException('The permalink must be set to "Post name" - go to Settings > Permalinks and change it');

    if(!myHome()->database->check())
      throw new MyHomeException('There is an error with the database - click the Regenerate Database button');

    $apiKey=myHome()->options->getAdvertisingApiKey();

    if(!$apiKey)
      throw new MyHomeException('Advertising API key not set');

    $api=myHome()->api;
    $authentication=$api->authenticationHeadersApiKey($apiKey);
  
    // House Types / Displays
    $houseTypesResponse=$api->get('housedetails',$authentication,true);
    //myHome()->log->info('housedetails: ' . json_encode(array_map(create_function('$o', 'return $o->houseid;'), $houseTypesResponse)));
    if(!$houseTypesResponse)
      throw new MyHomeException('House types not available');
    else {
      $displaysResponse=$api->get('displays',$authentication,true);
      //myHome()->log->info('displays: ' . json_encode(array_map(create_function('$o', 'return $o->houseid;'), $displaysResponse)));
    }
    $houseTypes=MyHomeHouseType::createFromArray($houseTypesResponse);
    $displays=MyHomeDisplay::createFromArray($displaysResponse);

    global $wpdb;
    $wpdb->query('start transaction');
    try{
      // Clear old data
      if(MyHomeDisplay::deleteAll()===false) throw new MyHomeException('Could not empty the displays table - check the log for more details');
      if(MyHomeHouseType::deleteAll()===false) throw new MyHomeException('Could not empty the house types table - check the log for more details');

      // Insert new data
      if(MyHomeHouseType::insertAll($houseTypes)===false) throw new MyHomeException('Could not insert the house types - check the log for more details');
      if(MyHomeDisplay::insertAll($displays)===false) throw new MyHomeException('Could not insert the displays - check the log for more details');
    }
    catch(MyHomeException $e){
      // If logging is done through a WordPress option, it won't be updated unless we finish the transaction first
      $wpdb->query('rollback');
      throw $e;
    }
    $wpdb->query('commit');

    $templateHouseTypes=myHome()->options->getAdvertisingDefaultTemplateHouseTypes();
    $templateDisplays=myHome()->options->getAdvertisingDefaultTemplateDisplays();

    $templates=get_page_templates();

    if(!in_array($templateHouseTypes,$templates)){
      myHome()->log->error(sprintf('Template file "%s" not available in the current theme, using default template in house types', $templateHouseTypes));
      $templateHouseTypes='';
    }

    if(!in_array($templateDisplays,$templates)){
      myHome()->log->error(sprintf('Template file "%s" not available in the current theme, using default template in displays', $templateDisplays));
      $templateDisplays='';
    }

    // House-Types
    $newPagesHouseTypes=0;
    $houseTypeIds=[];
    foreach($houseTypes as $houseType){
      $page=$houseType->findPage();

      if(!$page){
        $pageId=$houseType->createPage($templateHouseTypes);
        $newPagesHouseTypes++;
      } else $pageId = $page->ID;

      $permalink=get_permalink($pageId);

      // New page or URL has changed
      if(empty($houseType->housetypeURL)||
        $houseType->housetypeURL!==$permalink
      ){ // housetypeURL is not stored in the database
        $params=['housetypeURL'=>$permalink];
        $api->post(['housedetails',$houseType->houseid,'houseurl'],$params,$authentication);
      }

      $houseTypeIds[]=$houseType->houseid;
    }

    $deletedPagesHouseTypes=0;
    foreach(MyHomeHouseType::findAllPages() as $houseTypeId=>$page) if(!in_array($houseTypeId,$houseTypeIds)){
      wp_delete_post($page->ID,true);
      $deletedPagesHouseTypes++;
    }

    // Display-Homes
    $newPagesDisplays=0;
    $displayIds=[];
    foreach($displays as $display){
      $page=$display->findPage();

      if(!$page){
        $pageId=$display->createPage($templateDisplays);
        $newPagesDisplays++;
      } else $pageId = $page->ID;

      $permalink=get_permalink($pageId);

      // New page or URL has changed
      if(empty($display->housetypeURL)||
        $display->housetypeURL!==$permalink
      ){ // housetypeURL is not stored in the database
        $params=['displayURL'=>$permalink];
        $api->post(['displays',$display->displayid,'displayurl'],$params,$authentication);
      }

      $displayIds[]=$display->displayid;
    }

    $deletedPagesDisplays=0;
    foreach(MyHomeDisplay::findAllPages() as $displayId=>$page) if(!in_array($displayId,$displayIds)){
      wp_delete_post($page->ID,true);
      $deletedPagesDisplays++;
    }


    // Attachments
    $documentUrls = $this->findDocUrls($houseTypes,$displays); myHome()->log->info('findDocUrls' . json_encode($documentUrls , JSON_PRETTY_PRINT));
    $newAttachmentsDocs=0;
    $unknownDocTypes=0;
    foreach($documentUrls as $documentUrl=>$type){ // myHome()->log->info('document: ' . $documentUrl . ' ' . $type);
      $mimeType=$this->docMimeType($type);

      if(!$mimeType){
        myHome()->log->error(sprintf('Unknown type of document %u: %s, skipping',$documentUrl,$type));
        $unknownDocTypes++;
        continue;
      }

      // Documents are supposed to never change their contents
      if($this->docFindAttachment($documentUrl))
        continue;

      $document = $api->download($documentUrl); // ,$authentication);

      if($this->docCreateAttachment($documentUrl, $type, $mimeType, $document)) {
        $newAttachmentsDocs++;
      } else { myHome()->log->info('create doc failed');
        throw new MyHomeException(sprintf('Could not download document %u', $documentUrl));
      }
    }

    return compact('newPagesHouseTypes', 'deletedPagesHouseTypes', 'newPagesDisplays', 'deletedPagesDisplays', 'newAttachmentsDocs', 'unknownDocTypes');
  }

  public function updateDatabaseCronjob(){
    // Set the PHP maximum execution time to 10 minutes
    @set_time_limit(600);

    $log=myHome()->log;

    $log->info(__('Advertising database update starting','myHome'));

    $result=$this->updateDatabase();
    
    $log->info(sprintf(__('Advertising database update finished: %s','myHome')));

    if($result != null)
      $log->info(sprintf($this->updateDatabaseResultString($result)));
  }

  /**
   * @param int[] $result result array, as returned by updateDatabase()
   * @return string a string with details about the database update
   */
  public function updateDatabaseResultString(array $result){
    /**
     * @var int $newPagesHouseTypes
     * @var int $deletedPagesHouseTypes
     * @var int $newPagesDisplays
     * @var int $deletedPagesDisplays
     * @var int $newAttachmentsDocs
     * @var int $unknownDocTypes
     */
    extract($result);

    $message=[];

    if($newPagesHouseTypes)
      $message[]=sprintf(__('%u new House Type pages','myHome'),$newPagesHouseTypes);
    if($deletedPagesHouseTypes)
      $message[]=sprintf(__('%u deleted House Type pages','myHome'),$deletedPagesHouseTypes);
    if($newPagesDisplays)
      $message[]=sprintf(__('%u new Display pages','myHome'),$newPagesDisplays);
    if($deletedPagesDisplays)
      $message[]=sprintf(__('%u deleted Display pages','myHome'),$deletedPagesDisplays);
    if($newAttachmentsDocs)
      $message[]=sprintf(__('%u new Document attachments','myHome'),$newAttachmentsDocs);
    if($unknownDocTypes)
      $message[]=sprintf(__('%u unknown Document types','myHome'),$unknownDocTypes);

    // Changes may have occurred in facades, plan options, etc.
    if(!$message)
      return __('No changes made','myHome');

    return implode(', ',$message);
  }

  private function checkPermalinkStructure(){
    $validStructures=['/%postname%/',
      '/index.php/%postname%/',
      '/blog/%postname%/'];

    $permalinkStructure=get_option('permalink_structure');

    foreach($validStructures as $structure)
      if($permalinkStructure===$structure)
        return true;

    return false;
  }

  private function docCreateAttachment($documentUrl,$type,$mimeType,$document){
    require_once ABSPATH.'wp-admin/includes/image.php';
    // myHome()->log->info('documentUrl: ' . json_encode($documentUrl));

    $filename = explode('?', substr($documentUrl, strrpos($documentUrl, '/') + 1))[0];
    myHome()->log->info('filename: ' . json_encode($filename));

    $wpUploadDir = wp_upload_dir();
    $url = sprintf('%s/%s.%s', $wpUploadDir['url'], $filename, $type);
    $path = sprintf('%s/%s.%s', $wpUploadDir['path'], $filename, $type);

    // Save It
    //myHome()->log->info('lastContentDisposition: ' . myHome()->api->getLastContentDisposition());
    //myHome()->log->info('url: ' . json_encode($url));
    //myHome()->log->info('path: ' . json_encode($path));
    //myHome()->log->info('file: ' . json_encode(finfo_file($file)));
    //myHome()->log->info(base64_encode($document));
    if(@file_put_contents($path, $document)===false)
      throw new MyHomeException(sprintf('Could not save the document %s to %s', $documentUrl, $path));

    $attachment=[
      'guid'=>$url,
      'post_mime_type'=>$mimeType,
      'post_title'=>sprintf($filename), //'Document %u', $documentUrl),
      'post_status'=>'inherit',
      'post_content'=>''
    ];

    $attachmentId=wp_insert_attachment($attachment,$path,0);

    update_post_meta($attachmentId,self::$DOC_META_ATTACHMENT,$documentUrl);

    $attachData=wp_generate_attachment_metadata($attachmentId,$path);
    wp_update_attachment_metadata($attachmentId,$attachData);

    return $attachData;
  }

  private function docMimeType($type){
    if(isset(self::$MIME_TYPES[$type]))
      return self::$MIME_TYPES[$type];

    return null;
  }

  private function findDocUrls(array $houseTypes,array $displays){
    $urlsTypes=[];

    foreach($houseTypes as $houseType) { 
      if(isset($houseType->housedocs)) foreach($houseType->housedocs as $doc)
        if(!isset($urlsTypes[$doc->url]))
          $urlsTypes[$doc->url]=$doc->type;

      foreach($houseType->facades as $facade) { myHome()->log->info('facade: ' . json_encode($facade, JSON_PRETTY_PRINT));
        if(isset($facade->facadedocs)) foreach($facade->facadedocs as $doc)
          if(!isset($urlsTypes[$doc->url]))
            $urlsTypes[$doc->url]=$doc->type;
      }

      foreach($houseType->planoptions as $planoption) { myHome()->log->info('planoption: ' . json_encode($planoption, JSON_PRETTY_PRINT));
        if(isset($planoption->planoptiondocs)) foreach($planoption->planoptiondocs as $doc)
          if(!isset($urlsTypes[$doc->url]))
            $urlsTypes[$doc->url]=$doc->type;
      }
    }

    foreach($displays as $display) { myHome()->log->info('display: ' . json_encode($display, JSON_PRETTY_PRINT));
      if(isset($display->planoptions)) foreach($display->planoptions as $planoption)
        foreach($planoption->planoptiondocs as $doc)
          if(!isset($urlsTypes[$doc->url]))
            $urlsTypes[$doc->url]=$doc->type;
    }

    return $urlsTypes;
  }
}
