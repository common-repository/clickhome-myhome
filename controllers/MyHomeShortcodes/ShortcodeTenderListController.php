<?php

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeTendersListController'))
  return;

/**
 * The ShortcodeTenderListController class
 *
 * Controller for the TenderList shortcode
 *
 * @since 1.5
 */
class ShortcodeTenderListController extends ShortcodeTenderBaseController{
  private static $tenderList;

  /**
   * @return string[][]|null
   */
  public static function tenders(){
    if(empty(static::$tenderList))
      static::$tenderList=static::loadTenderList();
    return static::$tenderList;
  }

  /**
   * Returns the tenders list after querying the API with the tenders command
   *
   * @uses MyHomeApi::get()
   * @return string[][]|null the tenders list (null if not available) - each item is composed of:
   * <ul>
   * <li>title: tender title</li>
   * <li>createDate: tender creation date</li>
   * <li>expiryDate: tender expiry date</li>
   * <li>status: tender status</li>
   * <li>expired: whether the tender is expired</li>
   * <li>houseType: house type name</li>1
   * <li>houseTypeUrl: URL to the page of the house type, if available</li>
   * <li>thumbnailUrl: URL to the thumbnail image, if available</li>
   * <li>overviewUrl: URL to the overview page for each tender, if available</li>
   * </ul>
   */
  private static function loadTenderList(){
    $tenderList = myHome()->api->get('tenders', myHome()->session->getAuthentication(),true);
    //var_dump($tenderList);

    if($tenderList===null)
      return null;

    //var_dump($tenderList);

    $tenders=[];

    $attributes=
      ['tenderId',
        'title',
        'housetypeid',
        'housetypename',
        'facadeid',
        'facadename',
        'statusid',
        'status',
        'price',
        'createdate',
        'expirydate'];

    $tenderPages = myHome()->options->getTenderPages();
    if(!empty($tenderPages['overview']))
      $overviewUrlBase=get_permalink($tenderPages['overview']);
    else
      $overviewUrlBase=null;

    foreach($tenderList as $tender){
      foreach($attributes as $attribute)
        if(empty($tender->{$attribute}))
          continue;

      // Skip if expired
      if($tender->statusid >= 10) continue;

      $houseTypes=MyHomeHouseType::find(['houseid'=>(int)$tender->housetypeid]);

      $houseTypeUrl='';
      $thumbnailUrl='';

      if($houseTypes){
        $houseType=$houseTypes[0];

        $docs=MyHomeHouseTypeDoc::find(['houseid'=>(int)$tender->housetypeid]);

        if($docs){
          $doc=$docs[0];

          $attachment=myHome()->advertising->docFindAttachment($doc->url);
          if($attachment){
            $imageSrc=wp_get_attachment_image_src($attachment->ID,[300,300]);

            if($imageSrc)
              $thumbnailUrl=$imageSrc[0];
          }
        }

        $page=$houseType->findPage();
        $houseTypeUrl=$page?get_permalink($page->ID):'';
      }

      if($overviewUrlBase){
        $params['myHomeTenderId']=$tender->tenderId;
        $overviewUrl=add_query_arg($params,$overviewUrlBase);
      }
      else
        $overviewUrl=null;

      if(!empty($tenderPages['selections']) && $params['myHomeTenderId']){
        $selectionUrlBase=get_permalink($tenderPages['selections']);
        $selectionUrl=add_query_arg($params,$selectionUrlBase);
      }
      else
        $selectionUrl=null;

      $tenders[] = (object)[
        'title'=>$tender->title,
        'createDate'=>(new DateTime($tender->createdate))->format('d/M/Y'),//$this->dateFromJson($tender->createdate),
        'expiryDate'=>(new DateTime($tender->expirydate))->format('d/M/Y'),//$this->dateFromJson($tender->expirydate),
        'status'=>$tender->status,
        'statusId'=>$tender->statusid,
        'expired'=>$tender->statusid>10,
        'houseType'=>$tender->housetypename,
        'houseTypeUrl'=>$houseTypeUrl,
        'thumbnailUrl'=>$thumbnailUrl,
        'overviewUrl'=>$overviewUrl,
        'price'=>$tender->price,
        'selectionsOpen'=>(strtolower($tender->status) == 'draft' || strtolower($tender->status) == 'clientedit'),
        'selectionUrl'=>$selectionUrl
      ];
	  //break; // Limit 1 tender result for testing "skip tender list"
    }

    return $tenders;
  }

  /**
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts=shortcode_atts(['includenonactive'=>'false', 'showrunningquantities'=>'false', 'showrunningprices'=>'false'],$atts);
    $attIncludeNonActive=$atts['includenonactive'];
    $attShowRunningQuantities=$atts['showrunningquantities'];
    $attShowRunningPrices=$atts['showrunningprices'];
	
    if(!$this->verifyIncludeNonActive($attIncludeNonActive)){
      myHome()->handleError('Wrong Include non active attribute: '.$attIncludeNonActive);
      $attIncludeNonActive='false';
    }

    // If the "Skip list" setting is enabled, this list should have been loaded earlier when checking if it only
    // contains one tender
    $tenders=static::tenders();
    if($tenders===null)
      return;

    $this->loadView('shortcodeTenderList','MyHomeShortcodes',compact('attIncludeNonActive','attShowRunningQuantities','attShowRunningPrices','tenders'));
  }

  /**
   * Verifies the value of the includenonactive shortcode attribute provided
   *
   * @param string $includeNonActive the includenonactive attribute value to check
   * @return bool whether the attribute is valid or not (it must be "false" or "true")
   */
  private function verifyIncludeNonActive($includeNonActive){
    return in_array($includeNonActive,['false','true']);
  }

  /**
   * @param string $date
   * @return string|null
   */
  private function dateFromJson($date){
    if(!preg_match('|^/Date\((\d+)\)/$|',$date,$timestamp))
      return null;

    $timestamp=$timestamp[1];
    // If in milliseconds, convert to seconds
    if($timestamp>2e9)
      $timestamp/=1000;

    return date('d/M/Y',$timestamp);
  }
}
