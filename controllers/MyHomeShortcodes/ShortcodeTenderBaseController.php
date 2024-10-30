<?php

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTenderBaseController'))
  return;

/**
 * The ShortcodeTenderBaseController class
 *
 * Abstract class for Tender controllers
 *
 * @since 1.5
 */
abstract class ShortcodeTenderBaseController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  /**
   * Returns the tender details
   *
   * @uses MyHomeApi::get()
   * @param int $id
   * @return null|string[][] the tenders details (null if not available) - each item is composed of details, documents,
   * and images; details are:
   * <ul>
   * <li>houseDesign</li>
   * <li>facade</li>
   * <li>size</li>
   * <li>orientation</li>
   * <li>bedrooms</li>
   * <li>bathrooms</li>
   * <li>livingAreas</li>
   * <li>stories</li>
   * <li>parking</li>
   * <li>description</li>
   * <li>documents</li>
   * <li>photos</li>
   * </ul>
   */
  protected function tender($tenderId = false, $returnCached = false) {
    if(!$tenderId) $tenderId = $this->getParam('myHomeTenderId');
    if(!$tenderId) throw new MyHomeException('Tender ID not provided');

    global $tender; // echo 'HEEERE' . json_encode($tender)  . ' ' . $tender->tenderid;
    if(isset($tender->tenderid) && $tender->tenderid == $tenderId) {//  echo 'CURRENT INSTANCE';
      // Current Instance
      return $tender;
    } else if($returnCached) { // echo 'CACHED INSTANCE';
      // Cached Instance
      $tender = myHome()->storage->get('tender-' . $tenderId);
      if(isset($tender->tenderid) && $tender->tenderid == $tenderId)
        return $tender;
    }
    
    // If no current or cached instance, get it from API
    //if(is_null($tender) || !$tender->tenderid) { // echo 'GET IT';
      $tender = myHome()->api->get(sprintf('tenders/%u', $tenderId), myHome()->session->getAuthentication());
      //myHome()->log->info('base.tender()' . json_encode($tender, JSON_PRETTY_PRINT));

      if($tender === null || count((array) $tender) == 0) //return null;
        throw new MyHomeException(sprintf('Tender %u not available', $tenderId));
      else if(isset($tender->exception))
        throw new MyHomeException($tender->exception);

      // Pages
      $tenderPages = myHome()->options->getTenderPages();
      $tender->urls = (object) [];
      if(isset($tenderPages['overview'])) 
        $tender->urls->overview = add_query_arg(['myHomeTenderId' => $tenderId], get_permalink($tenderPages['overview']));
      if(isset($tenderPages['selections'])) 
        $tender->urls->selections = add_query_arg(['myHomeTenderId' => $tenderId], myHome()->options->isTenderSkipSelectionOverview() ? get_permalink($tenderPages['selectionsEdit']) : get_permalink($tenderPages['selections']));
      if(isset($tenderPages['packages'])) 
        $tender->urls->packages = add_query_arg(['myHomeTenderId' => $tenderId], get_permalink($tenderPages['packages']));
      if(isset($tenderPages['variations'])) 
        $tender->urls->variations = add_query_arg(['myHomeTenderId' => $tenderId], get_permalink($tenderPages['variations']));

      myHome()->storage->put('tender-' . $tenderId, $tender);
      return $tender;
    //}
  }
}
