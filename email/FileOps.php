<?php
/******************************************/
function initialize_eMail () {
/******************************************/
global $eMail;
 
    $eMail = get_eMailFromDB($_POST['Id'], $_POST['Type'], $_POST['Author']);

    setMode();
    setStage();    
    setStatus();
    logBeta();
    return true;    
}
/******************************************/
function dateOfEmailOK ($Id, $days) {
/******************************************/
    
    $eDate = strtotime(substr($Id, 0, 10));
    if ( $eDate + $days*24*60*60 > now()) {return true;}
    else {return false;}
}
/******************************************/
function get_eMailFiles ($Type) {
/******************************************/
global $sourceOfeMail;     

    $sql = '
            SELECT * FROM eMails
                WHERE Type = "'.$Type.'" 
                    ORDER BY Id DESC';
    
    if (!$eTemplates = allRows($sql, 'geM_90')) {return null;}                 
    foreach($eTemplates AS $e) {
        if (dateOfEmailOK($e['Id'], OLD)) {$emails[] = $e;} }
    return $emails;    
}
/******************************************/
function get_eMailFile ($Type) {
/******************************************/
global $sourceOfeMail;
    
    if ($Type == NEW_DRAFT) {
        return '<p class="bold blue">New Draft: 
                    <input name="newFileName" type="text" size="40" 
                        placeholder="Enter Id for new draft">
                </p>';}
    
    if (!$eMailList = get_eMailFiles($Type)) {statusMsg('No '.$Type.' eMails found.'); return null;}

    $o = '<option value="" selected>Select '.$sourceOfeMail[$Type]['title'].'</option>';
    foreach ($eMailList AS $e) {
        if ($e['Id'] == NEW_DRAFT) {continue;}       
        if ($e['Type']  == DRAFT) {
            if (restrictedDraft($e)) {continue;} }    
        $o .= '<option value="'.implode(',',[$e['Id'],$Type,loggedInAs()]).'">'.$e['Id'].'</option>'.PHP_EOL;}               

    return '
       <p class="bold blue">'.$sourceOfeMail[$Type]['title'].': 
          <select name="'.$sourceOfeMail[$Type]['selectName'].'">'.PHP_EOL.
               $o . '
          </select>'.PHP_EOL.'
       </p>';                  
}
/******************************************/
function restrictedDraft ($e) {
/******************************************/
//Restrict drafts to Author or SysAdm-Unit 550

    if ( !(isSysAdm() || $e['Author'] == loggedInAs() || 
            ($e['FromACBL'] == System && ($e['Author'] == 'SysAdm' || $e['Author'] == System))) ){
        return true;}
    else {return false;}    
}  
/******************************************/
function makeDateId ($Id=null, $withoutDate=false) {
/******************************************/
global $eMail;

    $offset = $withoutDate ? 0 : 11;
    if ($Id) {return addDate().substr($Id, $offset);}
    else     {return $eMail['Id'] = addDate().substr($eMail['Id'], $offset);} 
}
/******************************************/
function userEntry () {
/******************************************/
global $eMail;

    foreach (func_get_args() AS $a) {
      if (is_blank($eMail[$a])) {
          $errors[] = 'No '.$a.' has been specified; '.$a.' is a required input';} }
    if ($errors) {
        foreach ($errors AS $e) {
            errorMsg($e, 'eUe_90');}
        setStage(2); return false;} 
    else {return true;}        
}  
/******************************************/
function checkFileInput () {
/******************************************/
global $eMail;

    if (!checkTemplateSpecification()) {return false;}          
    setTemplate();  
    newDraft();
    return true;
}
/******************************************/
function checkTemplateSpecification () {
/******************************************/
global $eMail, $template, $sourceOfeMail;
        
    if (!$_POST['sentFileName'] && !$_POST['draftFileName'] && !$_POST['newFileName']) 
    {
        processError(0, 'No drafts selected!', 'sfL_90');
        return false;
    }        
    elseif (count(array_filter(array($_POST['sentFileName'], 
            $_POST['draftFileName'], $_POST['newFileName']))) > 1) 
    {
        processError(0, 'Multiple drafts selected; only 
                            <span style="color: black">one</span> allowed!', 'sfL_91');
        return false;
    }
    elseif ($_POST['newFileName'])
    {
        if (!cleanFileName($_POST['newFileName']))        
        {    
            processError(0, '<em><span style="color: black">New Draft Id</span></em> 
                                contains illegal characters! Only alpha, numbers or blank, 
                                underscore (_), period or dash (-) are allowed.', 'sfL_92');
            return false;           
        }
    }
    return true;
}    
/******************************************/
function setTemplate () {
/******************************************/
global $eMail, $template, $sourceOfeMail;
    
    foreach ($sourceOfeMail AS $source=>$a) {
        if (!$_POST[$a['selectName']]) {continue;}
        else { 
          if ($source == NEW_) {
            $template['Id'] = NEW_DRAFT;
            $template['Type'] = DRAFT;
            $template['Author'] = System;}
          else {
            $d = explode(',',$_POST[$a['selectName']]);
            $template['Id'] = $d[0];
            $template['Type'] = $d[1];
            $template['Author'] = $d[2];} } }
 
    return true;                                        
}
/******************************************/
function readyStage ($stage) {
/******************************************/
global $eMail, $template;

    if ($stage == 10) {
      loadTemplate();
      $eMail = $template;
      $eMail['Draft_Id'] = $_POST['Draft_Id'];           
      return true;}

}
/******************************************/
function draftExists () {
/******************************************/
global $eMail, $template;
//Used when Sent is selected for template
       
    foreach ($eDB = get_eMailFromDB_ByName($template['Id'], DRAFT) AS $e) {
        if (dateOfEmailOK($e['Id'], DUPLICATE_DAYS)) { //Check if outside of d Days
            $_POST['Draft_Id'] = $e['Id']; //Set up Id to load template (see pickedDraft)
            return true;} }
    return false;    
}         
/******************************************/
function createDraft () {
/******************************************/
global $eMail, $template;
    
            
    $template['Id'] = makeDateId($_POST['newFileName'], true);
    
    if (draftExists()) {
        //Duplicate; get another template from user
        processError(0, 'The draft you selected, 
                            <span style="color: black;">'.$_POST['newDraftName'].'</span>, 
                            already exists. Please choose a different name.', 'enD_90');
        return false;}       

    else {  
        $template['Id'] = NEW_DRAFT;
        $template['Type'] = DRAFT;        
        $template['Author'] = System;
        loadTemplate();
        $eMail['Id'] = makeDateId($_POST['newFileName'], true);        
        $eMail['Type'] = DRAFT;
        $eMail['Author'] = loggedInAs();
        $eMail['Created'] = timeStamp();
        saveDraft(); 
        return true;}
}
/******************************************/
function newDraft () {
/******************************************/
global $eMail, $template;
    
    //New draft, Check for duplicate, create new draft & set defaults
    if ($_POST['newFileName']) 
    {
        createDraft();
        if (processError()) {return false;}
        setStage(1);        
        return true;
    }
    //Selected draft;
    elseif ($_POST['draftFileName']) 
    {                     
        loadTemplate();
        setStage(1);        
        return true;
    }    
    //Selected Sent email
    else {
        if (draftExists()) 
        {                       
            readyStage(10);
            setStage(10);
            return true;
        }               
        else 
        {                       
            loadTemplate(); 
            setDraft();
            setStage(1);                         
            return true;
        } 
    }        
}
/******************************************/
function loadTemplate () {
/******************************************/
global $eMail, $template;

    if ($template['Type'] == SENT) {
        if ($template = get_eMailFromDB($template['Id'], $template['Type'])) {return true;}
        else {errorMsg('No "Sent" template record found for '.$template['Id'], 'noT_90');} }
    
    elseif ($template['Type'] == DRAFT) {
        if ($eMail = get_eMailFromDB($template['Id'], $template['Type'])) {return true;}
        else {errorMsg('No "Draft" template record found for '.$template['Id'], 'noT_91');} }
    
    else {errorMsg('Unknown "Type:" '.$template['Type']. 'noT_92');}
    
    return false;
}
/******************************************/
function setDraft () {
/******************************************/
global $eMail, $template;

    $eMail = $template;
    
    $eMail['Id'] = makeDateId($template['Id']);
    $eMail['Type'] = DRAFT;
    $eMail['Author'] = loggedInAs();
    resetTracking();
    $eMail['Created'] = timeStamp();
    saveDraft();
    return true;        
}
/******************************************/
function resetTracking () {
/******************************************/
global $eMail, $eMail_Fields;

    foreach ($eMail_Fields AS $name=>$f) {
        if ($f['tracking']) {$eMail[$name] = null;} }
    return true;        
}
/******************************************/
function transformFromeMail () {
/******************************************/
global $eMail;
     
    if (!$_POST['FromeMail']) {return true;}
    $d = explode(',', $_POST['FromeMail']);
    if (!$eMail['FromACBL'] || $eMail['FromACBL'] == $d[0]) {return true;}
    if (changed()) {
      $eMail['FromACBL']  = $d[0];
      $eMail['FromName']  = $d[1];
      $eMail['FromeMail'] = $d[2];
      restrictFromeMail();}
    return true; 
}
/******************************************/
function transformCCs () {
/******************************************/
global $eMail;
 
    if ($_POST['CC_ACBL'] && $_POST['CC_ACBL'] != "SelectCC_Name") { 
        if (changed()) {
            $eMail['CCs'] = 
                $eMail['CCs'] ? $eMail['CCs'].','.$_POST['CC_ACBL'] : $_POST['CC_ACBL'];} }    
    return true;   
}
/******************************************/
function transformBody () {
/******************************************/
global $eMail;

    if ($eMail['Body'] == $_POST['eMailHTML']) {return true;}
    elseif (!$_POST['eMailHTML']) {return true;}
    else {        
        if (changed()) {$eMail['Body'] = $_POST['eMailHTML'];} }
    return true;                    
}
/******************************************/
function transformGame () {
/******************************************/
global $eMail;

    if ($_POST['selectedGame'] == NONE) {return true;}
    elseif ($_POST['selectedGame'] == $eMail['Game']) {return true;}
    else {
        if (changed()) {$eMail['Game'] = $_POST['selectedGame'];} }
    return true;
}
/******************************************/
function transformStatus () {
/******************************************/
global $eMail;

      if     (!$eMail['Status'])            {return $eMail_Fields['Status']['default'];}
      elseif ($eMail['Status'] == '(A)')    {return '("A")';}
      elseif ($eMail['Status'] == '(A,S)')  {return '("A", "S")';}
      elseif ($eMail['Status'] == '(S)')    {return '("S")';}
      else  {
              errorMsg('Unknown Status field, '.$eMail['Status'].', Status set to Active. 
                          Please contact webmaster with code and trace info:<br>'.debugb(), 'tfS_80');
              return '("A")';
            }  
}
/******************************************/
function revise () {
/******************************************/
global $eMail, $eMail_Fields;
   
    foreach ($eMail_Fields AS $name=>$e) {        

        if ($e['key'] || $e['pgmCtrl'] ||  $e['function'] ||  $e['version']) {continue;}        
        
        if ($e['transform']) {
            $f ='transform'.$name;            
            $f(); continue;}        

        if (!$_POST[$name] && !$eMail[$name]) {continue;}
        if ($_POST[$name] == $eMail[$name]) {continue;}
        
        if (!$_POST[$name] && $eMail[$name]) {
            //User clearing a value after initialization
            if ( ($_POST['edit'] && $e['edit']) || ($_POST['selection'] && $e['selection']) ) {
                if (changed()) {$eMail[$name] = null;} }
            continue;}
        
        else {if (changed()) {$eMail[$name] = $_POST[$name];} } }
     
    return true;   
}
/******************************************/
function saveDraft () {
/******************************************/
global $eMail, $eMail_Fields;
    
    if (is_blank($eMail['Id'])) {
        processError(0, 'Draft Id is blank; please contact Webmaster.', 'saveDraft_90');
        return false;}
    
    if (!$eMail['Changed']) {
        if (takeAction() == 'Save') {        
            statusMsg('No changes to save');
            return true;} }
    else {$eMail['Updated'] = timeStamp();}
                     
    foreach ($eMail_Fields AS $name=>$f) {
      if ($f['pgmCtrl'] ||  $f['version']) {continue;}
      if ($f['clean']) {$v = makeClean($eMail[$name]);}
      elseif (is_null($eMail[$name])) {
        $fields .= $c.$name.' = NULL'.PHP_EOL;
        continue;} 
      else {$v = $eMail[$name];}                        
      $fields .= $c.$name." = '".$v."'".PHP_EOL;
      $c = ', ';}

    $sql = 'REPLACE INTO eMails SET ' . $fields;
          
    if (p_oneRow($sql, 'saveDraft_91')) {return true;}
    else {errorMsg('saveDraft_92', 'Draft eMail record not inserted.'.showDBerror($sql));} 
}
/******************************************/
function updateDraft ($fields) {
/******************************************/
global $eMail, $eMail_Fields;

    foreach ($fields AS $name=>$v) {
        if ($v == 'NULL') {$f .= $c.$name.' = NULL';}
        elseif ($eMail_Fields[$name]['clean']) {$f .= $c.$name.' = "'.makeClean($v).'"';} 
        else {$f .= $c.$name.' = "'.$v.'"';}
        $c = ', ';} 
     
     $sql = 'UPDATE eMails SET '.$f.
                ' WHERE Id = "'.$eMail['Id'].'" AND  
                       Type = "'.$eMail['Type'].'" AND 
                       Author = "'.$eMail['Author'].'"  
                       LIMIT 1';
     
     if (p_oneRow($sql, 'seM_UD_90')) {return true;}
     else {errorMsg('seM_UD_91', 'eMail record not updated.'.showDBerror($sql));}
}   
/******************************************/
function get_eMailFromDB ($Id=null, $Type=null, $Author=null) {
/******************************************/
global $eMail;
    
    $Id = !$Id ? $eMail['Id'] : $Id;
    
    if (!$Type && !$eMail['Type']) {$t = null;}
    else {$t = !$Type ? ' AND Type = "'.$eMail['Type'].'"' : ' AND Type = "'.$Type.'"';}   

    if (!$Author && !$eMail['Author']) {$a = null;}
    else {$a = !$Author ? ' AND Author ="'.$eMail['Author'].'"' : ' AND Author = "'.$Author.'"';}   

    $sql = 'SELECT * FROM eMails
                WHERE Id = "'.$Id.'"'.$t.$a.'
                AND Type <> "Discarded" 
                    ORDER BY Id DESC';                    
    
    if (!$eDB = oneRow($sql, 'geDB_90')) {return null;}
    else 
    {
       return restoreOriginalChars($eDB);
    }    
}
/******************************************/
function get_eMailFromDB_ByName ($Id=null, $Type=null) {
/******************************************/
global $eMail;

    $Id = !$Id ? $eMail['Id'] : $Id;
    
    if (!$Type && !$eMail['Type']) {$t = null;}
    else {$t = !$Type ? ' AND Type ="'.$eMail['Type'].'"' : ' AND Type ="'.$Type.'"';}   

    $searchId = '%'.substr($Id,11).'%';
    
    $sql = 'SELECT * FROM eMails
                WHERE Id LIKE "'.$searchId.'"'.$t.'
                    AND Type <> "Discarded"
                    ORDER BY Id DESC';    
                      
    if (!$eDB = allRows($sql, 'geDB_92')) {return null;}
    else 
    {
       return restoreOriginalChars($eDB);
    }    
    
}
/******************************************/
function updateType ($from, $to) {
/******************************************/
global $eMail;
    
     $sql = 'UPDATE eMails SET Type = "'.$to.'"'.
                ' WHERE Id = "'.$eMail['Id'].'" AND  
                       Type = "'.$from.'" AND 
                       Author = "'.$eMail['Author'].'"  
                       LIMIT 1';
     
     if (p_oneRow($sql, 'seM_UD_92')) {return true;}
     else {errorMsg('seM_UD_92', 'Type not updated.'.showDBerror($sql));}
}   
/******************************************/
function isTemp_eMail () {
/******************************************/
global $eMail;
//Part of Quit process

    $sql = 'SELECT * FROM eMailsTemp
                WHERE Id = "'.$eMail['Id'].'" 
                    AND Type = "Temp"
                    AND Author = "'.$eMail['Author'].'"';                    
    
    if ($eTemp = oneRow($sql, 'geDB_94')) {return true;}    
    else {return false;}
}
/******************************************/
function restoreDraftFromTemp () {
/******************************************/
global $eMail;
    
    $sql = 'INSERT INTO eMails SELECT * FROM eMailsTemp'.WHERE("Temp");  
    if (!p_oneRow($sql, 'seM_MT_70')) {
        errorMsg('Temporary record not restored.'.showDBerror($sql), 'seM_MT_71');}
}   
/******************************************/
function makeTemp () {
/******************************************/
global $eMail;

    unmakeTemp();
    
    $sql = 'INSERT INTO eMailsTemp SELECT * FROM eMails'.WHERE();  
    
    if (!p_oneRow($sql, 'seM_MT_80')) {
        errorMsg('Temporary record not created.'.showDBerror($sql), 'seM_MT_81');}
        
    $sql = 'UPDATE eMailsTemp SET Type = "Temp"'.WHERE();  
        
    if (!p_oneRow($sql, 'seM_MT_82')) {
        errorMsg('tmp table not updated.'.showDBerror($sql), 'seM_MT_83');}
        
    return true;        
}    
/******************************************/
function unmakeTemp () {
/******************************************/
global $eMail;

    $sql = 'DELETE FROM eMailsTemp'.WHERE("Temp");  
    p_oneRow($sql, 'seM_MT_84'); 
    return true;
}
/******************************************/
function discardDraft () {
/******************************************/
global $eMail;
        
    remove("Discarded"); //Only last copy of a Discarded Id is retained
    $sql = '
            UPDATE eMails SET 
                Type = "Discarded", 
                Discarded = "'.timeStamp().'"'.
                WHERE("Draft");
    
    if (p_oneRow($sql, 'DD_90')) {
        logBeta('DD_90', $eMail['Id'].' Draft discarded');
        statusMsg($eMail['Id'].' Draft discarded');return true;}
        
    else {errorMsg('Error occurred discarding draft with Id: '.$eMail['Id'].
                   ' Please contact webmaster.', 'DD_91'); return false;}
}
/******************************************/
function deleteDraft () {
/******************************************/
global $eMail;
        
    if (remove()) {
        logBeta('DD_94', $eMail['Id'].' Draft deleted');
        if (takeAction() == 'Exit') {
            statusMsg($eMail['Id'].' Draft <b>without</b> latest changes deleted');
            return true;}
        else {statusMsg($eMail['Id'].' Draft deleted');return true;} }
        
    else {errorMsg('Error occurred deleting draft with Id: '.$eMail['Id'].
                   ' Please contact webmaster.', 'DD_95'); return false;}
}
/******************************************/
function remove ($d="Draft") {
/******************************************/
global $eMail;
    
    $sql = 'DELETE FROM eMails'.WHERE($d);  
    return p_oneRow($sql, 'DD_92'); 
}
/******************************************/
function WHERE ($Type=null) {
/******************************************/
global $eMail;

    $eType = $Type ? $Type : $eMail['Type'];
    return ' 
            WHERE Id = "'.$eMail['Id'].'" AND  
                Type = "'.$eType.'" AND 
                Author = "'.$eMail['Author'].'"';      
}    
/******************************************/
function showDBerror ($sql=null) {
/******************************************/
global $eMail, $mySQLi;

    if ($sql) {$addSQLinfo = '
                  <p>SQL Statement:</p><p>'.$sql.'</p>
                  <p>mySQLi reports:</p>'.mysqli_error($mySQLi).'</p>';}
    
    return '    
            &nbsp;Please supply Webmaster with this error code and data below:<br>       
            Id = "'.$eMail['Id'].'" AND <br> 
            Type = "'.$eMail['Type'].'" AND <br> 
            Author = "'.$eMail['Author'].'"'.PHP_EOL.$addSQLinfo;
}
/******************************************/
function logBeta ($Code=null, $comments=null) {
/******************************************/
global $eMail;   
   
    $Code = $Code ? $Code : $eMail['ProcessError'];
    if (isSysAdm() && !$Code) {return true;}
    
    $m = GetDB(loggedInAs());
    
    $sql = 'INSERT logBeta SET
             Code       = "' . $Code . '",
             Id         = "' . $eMail['Id'] . '",
             Type       = "' . $eMail['Type'] . '",
             Author     = "' . $eMail['Author'] . '",
             LoggedInAs = "' . loggedInAs() . '",             
             Role       = "' . $_SESSION['Role'] . '",             
             FirstName  = "' . $m['FirstName'] . '",
             LastName   = "' . $m['LastName'] . '",
             Action     = "' . takeAction() . '",
             Stage      = "' . $eMail['Stage'] . '",
             Sections   = "' . implode(',',$eMail['Sections']) . '",
             ShowSQL    = "' . $eMail['SQL'] . '",
             LoggedAt   = "' . timeStamp() . '",
             Comments   = "' . $comments . '"';

    if (p_oneRow($sql, 'logBeta_90')) {return true;}
    else {errorMsg('Logging failed; please contact Webmaster', 'logBeta_95'); return false;}  
}
/******************************************/
function get_logEntry (&$eMail) {
/******************************************/

    $sql = '
            SELECT * FROM eMail_Log
                WHERE Id = "'.$eMail['Id'].'"';
    
    if (!$log = allRows($sql, 'gLR_90')) {errorMsg('No rows found for '.$eMail['Id'], 'gLR_96'); return false;}
    $eMail['TotalSent'] = count($log);
    $eMail['DateSent'] = date('Y-m-d H:i:s', strtotime($log[0]["Time"]));
    return true;    
}

?>
