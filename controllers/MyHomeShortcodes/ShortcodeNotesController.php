<?php

/**
 * The ShortcodeNotesController class
 *
 * @package    MyHome
 * @subpackage ControllersShortcodes
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Do not attempt to redefine the class
if(class_exists('ShortcodeNotesController'))
  return;

/**
 * The ShortcodeNotesController class
 *
 * Controller for the Notes shortcode
 */
class ShortcodeNotesController extends MyHomeShortcodesBaseController{
  /**
   * {@inheritDoc}
   */
  public function doGet(array $params=[]){
  }

  /**
   * {@inheritDoc}
   */
  public function doPost(array $params=[]){
    myHome()->log->info("doPost notes: " . json_encode($params));
    /*list($subject,$body,$document) = $this->extractParams([
      'myHomeSubject', 
      'myHomeBody', 
      'myHomeDocument'
    ],$params);*/

    if($params['myHomeSubject'] === '')
      myHome()->abort(400,'Note subject empty'); // Bad Request
    if($params['myHomeBody'] === '')
      myHome()->abort(400,'Note body empty'); // Bad Request

    // Filter the subject and body
    //$subject = $this->filterText($subject,250);
    //$body = $this->filterText($body,10000);

    // Leave the replytoid and stepid as null by the moment
    $noteParams = [
      'replytoid' => $params['myHomeReplyToId'],
      'stepid' => null, //$params['myHomeStepId'],
      'subject' => $this->filterText($params['myHomeSubject'], 250),
      'body' => $this->filterText($params['myHomeBody'], 10000)
    ];

    $authentication = myHome()->session->getAuthentication();
    $newNote = myHome()->api->post('notes',$noteParams,$authentication,true);

    if($newNote===null)
      myHome()->abort(403,'Note submission failed'); // Forbidden
    else if($newNote) {
      //myHome()->log->info("addNote success: " . json_encode($newNote));
      if(file_exists($_FILES['myHomeDocument']['tmp_name'])) {
        $document = (object) $_FILES['myHomeDocument'];
        $imgbinary = fread(fopen($document->tmp_name, "r"), filesize($document->tmp_name));
        myHome()->log->info("uploadDoc: " . json_encode($document));
        //myHome()->log->info("uploadDoc" . " Type: " . $document->type . ", Path: " . $_FILES['myHomeDocument']['tmp_name']);
        //myHome()->log->info("uploadDocBinary: " . $imgbinary);
        //myHome()->log->info("uploadDocBase64: " . 'data:' . $document->type . ';base64,' . base64_encode($imgbinary));
        $docParams = [
          'Content' => base64_encode($imgbinary), //class_exists('CurlFile', false) ? new \CURLFile($_FILES[0], 'image/png') : "@{$_FILES[0]}",
          'Title' => $document->name,
          'FileName' => $document->name
        ];
        $newDoc = myHome()->api->post('notes/' . $newNote->noteid . '/document', $docParams, $authentication, true);
        myHome()->log->info("newDoc: " . json_encode($newDoc));
      }
    }

    //$note = $this->noteItem((object) $newNote);
    //$this->loadView(['shortcodeNotes','note'],'MyHomeShortcodes',compact('note'));
    return $note;
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
    $attLimit = isset($atts['limit']) ? (int) $atts['limit'] : null;
    $attHideFields = isset($atts['hidefields']) ? explode(',',$atts['hidefields']) : [];
    $attHideFields = array_map('trim',$attHideFields);
    $attHideFields = array_filter($attHideFields,'strlen');
    $attHideNew = isset($atts['hidenew']) ? $atts['hidenew']==='true' : false;
    $attPreSubjects = isset($atts['predefinedsubjects']) ? $atts['predefinedsubjects']==='true' : false;
    $attShowDocuments = isset($atts['showdocuments']) ? $atts['showdocuments']==='true' : false;
    $attTitle = isset($atts['content']) ? $atts['content'] : 'Compose a New Note';
    
    /*if(!$this->verifyHideNew($attHideNew)){
      myHome()->handleError('Wrong Hide New attribute: '.$attHideNew);
      $attHideNew='false';
    }*/
    if(!$this->verifyHideFields($attHideFields)){
      myHome()->handleError('Wrong Hide Fields attribute: '.implode(',',$attHideFields));
      $attHideFields=[];
    }

    // Predefined Subjects
    if($attPreSubjects) {
      $preSubjects = myHome()->api->get('notes/subjects', myHome()->session->getAuthentication(), true);
      if(isset($preSubjects)) sort($preSubjects);
      //myHome()->log->info("preSubjects: " . json_encode($preSubjects));
    }

    $notes = $this->notesList();
    if($notes===null)
      return;
    if($attLimit)
      $notes = array_slice($notes,0,$attLimit);

    $this->loadView('shortcodeNotes','MyHomeShortcodes',compact('attHideNew','attHideFields','attPreSubjects','attTitle','preSubjects','attShowDocuments','notes'));
  }

  /**
   * Returns the sorted notes list after querying the API with the jobsteps command
   *
   * @uses MyHomeApi::get()
   * @uses ShortcodeNotesController::noteItem() to retrieve each note item
   * @return mixed[]|null the notes list (null if not available), sorted by sequence number in ascending order - each
   *                      item is composed of:
   * <ul>
   * <li>Array key: note timestamp (generated from the notedate field)</li>
   * <li>author: note author (author field)</li>
   * <li>subject: note subject (subject field)</li>
   * <li>body: note body (body field)</li>
   * <li>date: note date (generated from the date field)</li>
   * </ul>
   */
  private function notesList(){
    $notes = myHome()->api->get('notes', myHome()->session->getAuthentication(), true);
    //myHome()->log->info("getNotes: " . json_encode($notes));

    if($notes===null)
      return null;

    $notesTimes=[];

    foreach($notes as $note){
      if(empty($note->notedate)) continue;

      $note->authorname = sprintf(_x('By %s','Note Author','myHome'), $note->authorname);
      $dt=new DateTime($note->notedate);

      // The note timestamp is used as a key to sort the notes
      $time=$dt->getTimestamp();
      if(!isset($notesTimes[$time]))
        $notesTimes[$time]=[];

      $note->notedate = $this->dateString(new DateTime($note->notedate));
      $notesTimes[$time][] = $note; //$this->noteItem($note,$dt);

      // Format reply timestamps
      $this->formatDates($note);
    }

    // Sort by timestamp (array key)
    krsort($notesTimes,SORT_NUMERIC);

    // Flatten the array - $notesList is made up of arrays of items, one per timestamp (although it is not very likely that two or more notes will share the exact same timestamp)
    $notesList=[];
    foreach($notesTimes as $notesTime)
      $notesList=array_merge($notesList,$notesTime);

    return $notesList;
  }

  /**
   * Recursively walk down replies formatting dates
   */
  private function formatDates($note){
    if(!isset($note->replies)) return;

    foreach((array)$note->replies as $note){
      if(empty($note->notedate)) continue;
      $note->notedate = $this->dateString(new DateTime($note->notedate));
      if($note->replies) $this->formatDates($note);
    }
  }

  /**
   * Verifies the value of the hidefields shortcode attribute provided
   *
   * @param string[] $hideFields the hidefields attribute value to check
   * @return bool whether the attribute is valid or not (it must not contain fields other than "author", "subject",
   *                             "body" and/or "date")
   */
  private function verifyHideFields(array $hideFields){
    $validFields=['author',
      'subject',
      'body',
      'date'];

    foreach($hideFields as $field)
      if(!in_array($field,$validFields))
        return false;

    return true;
  }

  /**
   * Verifies the value of the hidenew shortcode attribute provided
   *
   * @param string $hideNew the hidenew attribute value to check
   * @return bool whether the attribute is valid or not (it must be "false" or "true")
  
  private function verifyHideNew($hideNew){
    return in_array($hideNew,['false','true']);
  } */
}
