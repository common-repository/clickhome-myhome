<?php

/**
 * The ShortcodeDocumentsController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeDocumentsController'))
  return;

/**
 * The ShortcodeDocumentsController class
 *
 * Controller for the Documents shortcode - note that doGet() is used to serve documents to the Photos and House
 * Details shortcodes as well
 */
class ShortcodeDocumentsController extends MyHomeShortcodesBaseController{
  /**
   * Mime types for well-known file types
   *
   * @see ShortcodeDocumentsController::documentType()
   */
  private static $MIME_TYPES=['jpg'=>'image/jpeg',
    'jpeg'=>'image/jpeg',
    'png'=>'image/png',
    'bmp'=>'image/bmp',
    'gif'=>'image/gif',
    'xls'=>'application/vnd.ms-excel',
    'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ods'=>'application/vnd.oasis.opendocument.spreadsheet',
    'doc'=>'application/msword',
    'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'odt'=>'application/vnd.oasis.opendocument.text',
    'pdf'=>'application/pdf',
    'zip'=>'application/zip',
    '7z'=>'application/x-7z-compressed',
    'rar'=>'application/x-rar-compressed'];

  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){ myHome()->log->info('docController.doGet() called: ' . json_encode($params, JSON_PRETTY_PRINT));
    // The parameters needed are the document ID, whether the document should be returned inline or as an attachment, and whether the document must be retrieved using the thumbs API call (images only)
    list($documentUrl,$inline,$thumb,$cache,$action) = $this->extractParams([
      'myHomeDocumentId',
      'myHomeInline',
      'myHomeThumb',
      'myHomeCache',
      'myHomeAction'
    ],$params);

    //myHome()->log->info('docContr doGet: ' . json_encode($params, JSON_PRETTY_PRINT));

    if(!$documentUrl)
      myHome()->abort(400,'Document not availble'); // Bad Request

    // Cache is available only for inline documents
    if(!$inline)
      $cache=false;

    $varName=sprintf('document%s%u',$thumb?'Thumb':'',$documentUrl);

    if($cache){
      $cachedDocument=$this->restoreVar($varName);

      // If the document is cached, don't call the API
      if($cachedDocument!==null&&!empty($cachedDocument['contentType'])&&!empty($cachedDocument['contentDisposition'])&&
        !empty($cachedDocument['document'])
      ){
        $contentType=$cachedDocument['contentType'];
        $contentDisposition=$cachedDocument['contentDisposition'];
        $document=$cachedDocument['document'];

        @header('Content-Type: '.$contentType);
        @header('Content-Disposition: '.$contentDisposition);
        @header('Content-Transfer-Encoding: binary');
        @header('Content-Length: '.strlen($document));

        echo $document;

        return;
      }
    }

    $api=myHome()->api;

    // Default to system auth
    //$route = 'documents';
    $authentication = myHome()->session->getAuthenticationDocuments();
    if($action == 'clientDocument') {
        //$route = 'clientdoc'; //$thumb ? 'thumbs' : 'clientdoc';
        $authentication = myHome()->session->getAuthentication();
    }

    /*myHome()->log->info(json_encode(array(
      'route' => $route,
      'action' => $action,
      'auth' => $authentication,
      'documentId' => $documentId
    ), JSON_PRETTY_PRINT));*/
    $document = $api->download(add_query_arg(array(
      'auth' => $authentication['authorization'],
      'contractNo' => $authentication['contractNumber'],
      'thumb' => $thumb
    ), $documentUrl), $authentication); 
    //$document = $api->download([$route, $documentId, $thumb ? '?thumb='.$thumb : null], $authentication);
    
    if($api->getLastErrorType()!==null)
      myHome()->abort(500,'API request error: '.$api->getLastErrorMessage()); // Internal error
    else if($document==='')
      myHome()->abort(500,'Empty document'); // Internal error

    // "filename*" is not allowed here (see RFC 6266, 4.1), also, the filename must be written as is (eg "filename: photo.jpeg")
    $contentDisposition = $api->getLastContentDisposition();
    //myHome()->log->info($contentDisposition);
    if(!preg_match('|^attachment; filename=.+$|', $contentDisposition))
      myHome()->abort(500,'Wrong document content disposition received: '.$contentDisposition); // Internal error

    // The API call returns an attachment
    if($inline)
      $contentDisposition=str_replace('attachment','inline',$contentDisposition);

    $contentType=$api->getLastContentType();

    // Workaround to replace the generic "application/octet-stream" content type
    if($contentType==='application/octet-stream'){
      $lastDotPosition=strrpos($contentDisposition,'.');
      if($lastDotPosition===false)
        myHome()->abort(500,'Document extension not found'); // Internal error

      $extension=substr($contentDisposition,$lastDotPosition+1);
      $extension=strtolower($extension);

      if(isset(self::$MIME_TYPES[$extension]))
        $contentType=self::$MIME_TYPES[$extension];
    }

    if($cache){
      $cachedDocument=['contentType'=>$contentType,
        'contentDisposition'=>$contentDisposition,
        'document'=>$document];

      $this->cacheVar($varName,$cachedDocument);
    }

    @header('Content-Type: '.$contentType);
    @header('Content-Disposition: '.
      $contentDisposition); // This relies on the API server for the correct handling of special characters in the filenames
    @header('Content-Transfer-Encoding: binary');
    @header('Content-Length: '.strlen($document));

    echo $document;
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
   * {@inheritDoc}
   */
  public function doShortcode(array $atts=[]){
    $atts=shortcode_atts(['exclude'=>''],$atts);

    // This attribute can contain any of the document types returned by documentType()
    $attExclude=explode(',',$atts['exclude']);
    $attExclude=array_map('trim',$attExclude);
    $attExclude=array_filter($attExclude,'strlen');

    if(!$this->verifyExclude($attExclude)){
      myHome()->handleError('Wrong Exclude attribute: '.implode(',',$attExclude));
      $attExclude=[];
    }

    $documents=$this->documentsList();
    if($documents===null)
      return;

    // This is used by documentDownloadUrl() to generate a download URL
    $this->formAttributes=myHome()->adminPostHandler->formAttributes('clientDocument','GET');

    $this->loadView('shortcodeDocuments','MyHomeShortcodes',compact('attExclude','documents'));
  }

  /**
   * Returns the download URL (with Content-Type: attachment) for a given document ID
   *
   * This method is called from the Documents shortcode to insert a link to a document
   *
   * @uses ShortcodeDocumentsController::$formAttributes to generate the appropriate GET URL for the document action
   * @param int $documentId the document ID
   * @return string the download URL
   */
  protected function documentDownloadUrl($documentUrl) {
    return trailingslashit(myHome()->options->getEndpoint()) . $documentUrl;
    /*$formAttributes=$this->formAttributes;
    $formAttributes['params']['myHomeDocumentId'] = $documentUrl;
    $formAttributes['params']['myHomeInline'] = (int)false; // add_query_arg() ignores parameters with a boolean false value
    return add_query_arg($formAttributes['params'],$formAttributes['url']);*/
  }

  /**
   * Returns the document type for a given file extension
   *
   * <p>This document type matches the possible choices of the exclude shortcode attribute</p>
   * <p>It is also used to set the adequate class in the document block</p>
   *
   * @param string $extension the file extension
   * @return string the document type
   */
  private function documentType($extension){
    switch(strtolower($extension)){
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'bmp':
      case 'gif':
        return 'image';
      case 'xls':
      case 'xlsx':
      case 'ods':
        return 'spreadsheet';
      case 'doc':
      case 'docx':
      case 'odt':
        return 'document';
      case 'pdf':
        return 'pdf';
      case 'zip':
      case '7z':
      case 'rar':
        return 'compressed';
      default:
        return 'other';
    }
  }

  /**
   * Returns the documents list after querying the API with the documents command
   *
   * @uses MyHomeApi::get()
   * @uses MyHomeBaseController::dateString()
   * @return mixed[]|null the photos list (null if not available) - each item is composed of:
   * <ul>
   * <li>title: photo title (title field)</li>
   * <li>date: formatted photo date (generated from the docdate field)</li>
   * <li>url: document ID (url field)</li>
   * <li>type: document type (generated from the type field)</li>
   * </ul>
   */
  private function documentsList(){
    $authentication=myHome()->session->getAuthentication();
    $documents=myHome()->api->get('documents',$authentication,true);

    if($documents===null)
      return null;

    $documentsList=[];

    foreach($documents as $document){
      if(empty($document->docdate))
        continue;
      if(empty($document->title))
        continue;
      if(empty($document->url))
        continue;
      if(empty($document->type))
        continue;

      $dt=new DateTime($document->docdate);

      $documentsList[]=['title'=>$document->title,
        'date'=>$this->dateString($dt),
        'url'=>$document->url,
        'type'=>$this->documentType($document->type)]; // The type field contains the file extension
    }

    myHome()->log->info('documents: ' . json_encode($documentsList, JSON_PRETTY_PRINT));
    return $documentsList;
  }

  /**
   * Verifies the value of the exclude shortcode attribute provided
   *
   * @see ShortcodeDocumentsController::documentType() for a list of valid document types
   * @param string[] $exclude the exclude attribute value to check
   * @return bool whether the attribute is valid or not (it must contain valid document types)
   */
  private function verifyExclude(array $exclude){
    $validFields=['image',
      'spreadsheet',
      'document',
      'pdf',
      'compressed',
      'other'];

    foreach($exclude as $field)
      if(!in_array($field,$validFields))
        return false;

    return true;
  }

  /**
   * Settings for the document action - used by documentDownloadUrl()
   *
   * @var mixed[]
   */
  private $formAttributes;
}
