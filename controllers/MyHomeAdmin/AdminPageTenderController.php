<?php

/**
 * The AdminPageTenderController class
 *
 * @package    MyHome
 * @subpackage ControllersAdmin
 * @since      1.5
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('AdminPageTenderController'))
  return;

/**
 * The AdminPageTenderController class
 *
 * Controller for the Tender admin page view
 *
 * @since 1.5
 */
class AdminPageTenderController extends MyHomeAdminBaseController{
  /**
   * Used by writeHeaderTabs() to know the active admin page
   */
  protected static $ACTIVE_PAGE='MyHomeTender';

  /**
   * Tender pages shortcodes and names
   *
   * <p>
   * <ul>
   * <li>Array key: page code</li>
   * <li>shortcode: page shortcode</li>
   * <li>name: page name (used in the admin page)</li>
   * </ul>
   * </p>
   * <p>This array is filled up later in the constructor, as it needs to use gettext's __() method</p>
   */
  protected static $TENDER_PAGES=[];

  public function __construct(){
    parent::__construct();

    self::$TENDER_PAGES=[
      'list'=>[
        'shortcode'=>'TenderList',
        'name'=>_x('List','Tender admin page','myHome'),
        'required'=>false
      ],
      'overview'=>[
        'shortcode'=>'TenderOverview',
        'name'=>_x('Overview','Tender admin page','myHome'),
        'required'=>true
      ],
      'packages'=>[
        'shortcode'=>'TenderPackages',
        'name'=>_x('Packages','Tender admin page','myHome'),
        'required'=>true
      ],
      'selections'=>[
        'shortcode'=>'TenderSelections',
        'name'=>_x('Selections','Tender admin page','myHome'),
        'required'=>false
      ],
      'selectionsEdit'=>[
        'shortcode'=>'TenderSelectionsEdit',
        'name'=>_x('Selection Edit','Tender admin page','myHome'),
        'required'=>true
      ],
      'variations'=>[
        'shortcode'=>'TenderVariations',
        'name'=>_x('Variations','Tender admin page','myHome'),
        'required'=>true
      ]
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
    $tenderPages=myHome()->options->getTenderPages();
    //echo('tenderpages: ' . var_dump($tenderPages));
    $suggestedTenderPages=$this->detectTenderPages();

    $tenderPagesDetails=static::$TENDER_PAGES;
    //echo('tenderPageDetails: ' . var_dump($tenderPagesDetails));

    $skipList = myHome()->options->isTenderSkipList();
    $skipSelectionOverview = myHome()->options->isTenderSkipSelectionOverview();
    $declaration = myHome()->options->getTenderVariationDeclaration();

    $this->loadView('adminPageTender','MyHomeAdmin',compact('tenderPages','suggestedTenderPages','tenderPagesDetails','skipList','skipSelectionOverview','declaration'));
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    list($tenderPages, $skipList, $skipSelectionOverview, $declaration) = $this->extractParams(['myHomeTenderPages','myHomeSkipList','myHomeSkipSelectionOverview','myHomeTenderVariationDeclaration'],$params);

    // Filter and typecast the settings received as needed
    $tenderPages=array_map('intval',$tenderPages);

    $error=false;

    foreach(self::$TENDER_PAGES as $page=>$pageSettings)
      if($pageSettings['required']&&empty($tenderPages[$page])){
        $this->flashVar('error',__('All the required Tender Pages must be provided','myHome'));
        $error=true;

        break;
      }

    // If no errors are found, save the options set by the user
    if(!$error){
      $options = myHome()->options;

      $options->setTenderPages($tenderPages);
      $options->setTenderSkipList((bool)$skipList);
      $options->setTenderSkipSelectionOverview((bool)$skipSelectionOverview);
      $options->setTenderVariationDeclaration($declaration);

      $options->saveAll();

      // Remember that the settings have been successfully saved
      $this->flashVar('saved',true);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function doPostXhr(array $params=[]){
  }

  /**
   * Searches for pages containing the different Tender shortcodes
   *
   * If more than one page contains a shortcode, it returns the first according to the default get_posts() order (post
   * date descending)
   *
   * @uses MyHomeShortcodes::detect()
   * @return int[] for each page, its ID, if found, or 0 otherwise
   */
  private function detectTenderPages(){
    // There is a bug here: 'Viewing' a page from wordpress editor saves the html result of the shortcodes into post_content - which breaks this function

    $suggestedTenderPages=array_combine(array_keys(self::$TENDER_PAGES),array_fill(0,count(self::$TENDER_PAGES),0));
    //var_dump(self::$TENDER_PAGES);

    $pages=get_posts(['posts_per_page'=>-1,
      'post_type'=>'page',
      'post_status'=>'publish']);

    // Detect each shortcode in each page
    foreach(self::$TENDER_PAGES as $tenderPage=>$pageSettings) {
      //echo('look for: ' . $tenderPage . '<br/>');
      foreach($pages as $page) {
        //echo($pageSettings['shortcode'] . ': ' . $page->post_title . ': ' . myHome()->shortcodes->detect($page->post_content, $pageSettings['shortcode']) . '<br/>'); // . ': ' . var_dump($page->post_content));
        if(myHome()->shortcodes->detect($page->post_content, $pageSettings['shortcode'])) {
          //echo('detected: ' . $page->post_title . '<br/><br/>');
          $suggestedTenderPages[$tenderPage]=$page->ID;
          break; // Don't look any further
        }
      }
    }

    return $suggestedTenderPages;
  }
}
