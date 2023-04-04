<?php
/******************************************/
function restrictFromeMail () {
/******************************************/
global $eMail;

    if (isSysAdm() || loggedInAs() == $eMail['FromACBL'] || $eMail['FromACBL'] == System) {return true;}
    else {
            $Unit = GetDB(System);
            errorMsg('You must use either '.myName($Unit, 'Full').' or 
                        your officer email address as "From" email. 
                        "From Email" address changed to '.$Unit['eMail'].'.');
            setFromeMailDefaults();
            return true;
        }                        
}
/******************************************/
function makeSendList () {
/******************************************/
global $eMail, $Steps;

    if (!$Steps[$eMail['Stage']]['doList']) {return null;} 
    
    generate_SQL();              
    sendList();
    return true;                          
}
/******************************************/
function doSending () {
/******************************************/
global $eMail;
    
    if (!$eMail['Send']) {errorMsg('No recipients found!', 'doS_90'); return false;}    
    if (isModeTest()) {
        statusMsg('No emails sent: <em>Mode</em> set to "Test".'); return true;}                               
    
    foreach ($eMail['Send'] AS $SendList=>$List)
    {
        if (isModeReview()) 
        {
            if ($SendList == 'Reviewers')
            {
                addToSubject();
                sendToList($eMail['Send'][$SendList]); 
                restoreSubject();
            }
            else {continue;}            
        }
        else {sendToList($eMail['Send'][$SendList]);}       
    }    
    return true;         
}
/******************************************/
function sendToList ($List) {
/******************************************/
global $eMail;

    foreach ($List AS $m) 
    {
        send_e($m);
    }   
    return true;            
}      
/******************************************/
function send_e ($m) {
/******************************************/
global $eMail;
    
    if (!betaRecipient($m)) {$eMail['Error']['Beta'][$m['ACBL']] = $m; return false;}  
     
    $eMail['Signature'] = showSignature().showContact();
    $eMail['Message'] = personalize_eMail($m); 
                                               
    if (!sendHTML($m, $eMail)) {
        $eMail['Error']['failed'][$m['ACBL']] = $m;
        errorMsg('Email to '.myName($m, 'ACBL').' failed to send', 'seM_80'); 
        return false;}
    else {$eMail['Sent']['Success'][$m['ACBL']] = $m; return true;}             
}
/******************************************/
function betaRecipient ($m) {
/******************************************/
global $eMail, $betaRecipients;
     
    if (setContent() != 'emailBeta') {return true;} 

    if (in_array($m['ACBL'], $betaRecipients)) {return true;}
    else {return false;}
}    
/******************************************/
function addToSubject () {
/******************************************/
global $eMail;

    if (isModeReview()) 
    {
        $eMail['Subject'] = REVIEW.$eMail['Subject'];    
    }    
    return true;    
}
/******************************************/
function restoreSubject () {
/******************************************/
global $eMail;

    if (isModeReview()) 
    {
        $eMail['Subject'] = str_replace(REVIEW, null, $eMail['Subject']);    
    }    
    return true;    
}
/******************************************/
function personalize_eMail ($m) {
/******************************************/
global $eMail;
//Based on ready_eMail

    addHTML_eMail();
    if ($eMail['InfoCheck'] == 'Phone Directory') {
       $info = '
            <div>
                 <ul>
                      <li>Name: '.myName($m, 'Full').'</li>
                      <li>Phone: '.getPhone($m).'</li>
                 </ul>
            </div>' . PHP_EOL;}     

    if ($eMail['InfoCheck'] == 'Sectional') {      
       $info = '<p>Dear <span style="color: #009ddc; font-weight: bold;">Bridge Player '.myName($m).',</span></p>';}
       
    $eMail['Body'] = str_replace('InfoCheck', $info, $eMail['Body']);       
       
    return $eMail['start_HTML']. $eMail['head'] . $eMail['styles'] . $eMail['end_head'] . $eMail['start_body'] . 
                personalizeSalutation($m) . 
                $eMail['Body'] . 
                $eMail['Signature'] .
           $eMail['end_body'] . $eMail['end_HTML'];
}
/******************************************/
function addHTML_eMail () {
/******************************************/
global $eMail;

    $eMail['start_HTML'] = '<!DOCTYPE HTML><html>'; 
    $eMail['head'] = '<head>';
    $eMail['styles'] = e_styles();     
    $eMail['end_head'] = '</head>';
    $eMail['start_body'] = '<body>';    
    $eMail['end_body'] = '</body>';
    $eMail['end_HTML'] = '</html>';
}            
/******************************************/
function personalizeSalutation ($m) {
/******************************************/
global $eMail;

    if ($eMail['Salutation'] == 'None') {return null;}
    elseif (preg_match('/\[Member\]/', salutation())) {
        return str_replace('[Member]', myName($m), salutation());}
    elseif (preg_match('/\[BBO Name\]/', salutation())) {
        return str_replace('[BBO Name]', myName($m, 'BBO'), salutation());}
    else {return null;}                    
}
/******************************************/
function doTracking () {
/******************************************/
global $eMail;
    
    if (isModeTest()) {return true;}
    
    if (isModeReview()) 
    {
        $fields = array(
                  'Id'=>          $eMail['Id'],
                  'Type'=>        DRAFT,
                  'Author'=>      loggedInAs(),
                  'Copies'=>      count($eMail['Send']['Reviewers']) + $eMail['Copies']);     
        updateDraft($fields);
        return true;
    }
    else 
    {
        $fields = array(
                  'Id'=>          $eMail['Id'],
                  'Type'=>        SENT,
                  'Author'=>      loggedInAs(),                
                  'SentBy'=>      loggedInAs(), 
                  'DateSent'=>    $eMail['DateSent'],
                  'DoneAt'=>      $eMail['DoneAt'], 
                  'TotalQ'=>      showEmailQd(),
                  'TotalSent'=>   count($eMail['Sent']['Success']) );
        updateDraft($fields);
        $eMail['Type'] = SENT;
        return true;
    }                                    
}
/******************************************/
function sendList () {
/******************************************/
global $eMail, $ACBL_Unit;
    
   if (!$eMail['SQL']) {return false;}
   //if ($bounced) {foreach ($bounced AS $me) {$members[] = get_eDB($me);} }

   if (!$members = allRows($eMail['SQL'], 'SeM_95')) {errorMsg('No receiptants found.', 'SeM_96'); return null;}
   
   if ($_POST['AlreadySent'] == 'Y') { //Populate already sent eMail array
          $sql = 'SELECT ACBL FROM eMail_Log 
                    WHERE Id = "' . $eMail['Id'].'"';
          if ($aS = allRows($sql, 'eM90')) { foreach ($aS AS $a) {$alreadySent[] = $a['ACBL'];} } }

   /*2022-11-22 Changed to only one group may be excluded
   /*TODO        
   if ($eMail['Excludes']) {
          $AllExcludeGroups = eMailExcludes();
          foreach ($AllExcludeGroups AS $group => $excludedMembers) {
             if ($group == $_POST['Excludes']) {$Excludes[$group] = $excludedMembers;} } }
                             
   if (strpos($eMail['SQL'],'FROM D21')) {$D21 = true; $elements = array('Unit', 'TotalMPs');}
   else {$elements = array('Unit', 'TotalMPs', 'BBOName');}
   */ 
   $elements = array('Unit', 'TotalMPs', 'BBOName');
   
   foreach ($members AS $member) { 
           $thisMemberExcluded = false;
           if ($alreadySent) {   
               if (in_array($member['ACBL'], $alreadySent)) {
                    $eMail['Error']['previouslySent'][$member['ACBL']] = GetDB($member['ACBL']); continue;} }                   
           
           if ($D21) {               
               if ($member['Unit'] != $ACBL_Unit) {
                    if (!valid_eMailAddress($member['eMail'])) {$list['invalid_eMail'][$member['ACBL']] = $member; continue;}
                    if (in_array($member['ACBL'], $D21_BadeMails)) {$list['invalid_eMail'][$member['ACBL']] = $member; continue;}
                    else {$m = $member;} }
               else {
                    if (!$m = Unit_Subscriber($member)) {$list['NotSubscribed'][$member['ACBL']] = $member; continue;}                 
                    if ($m['Status'] == DECEASED) {$list['Deceased'][$member['ACBL']] = $m; continue;} } }              
           
           else {$m = GetDB($member['ACBL']);} 
           
           if ($Excludes) {          
                foreach ($Excludes AS $group => $excludeTheseMembers) {                   
                    if (in_array($m['eMail'], $excludeTheseMembers)) {
                        $eMail['Error']['excluded'][$eMail['Send']['Reviewers']] = $m;} } }
             
           if (isModeReview()) {$eMail['Send']['Reviewers'][$m['ACBL']] = $m;}
           else {$eMail['Send']['Recipients'][$m['ACBL']] = $m;} }                 

   addCCs();
   addOthers();  
   return true;
}
/******************************************/
function addOthers () {
/******************************************/
global $eMail;
        
    if (isModeTest() || isModeSend()) 
    {
        if (!isSysAdm())
        {    
            addOthers_(me());
            addOthers_(GetDB(SysAdm));
        }
    }
    return true;    
}
/******************************************/
function addOthers_ ($m) {
/******************************************/
global $eMail;
        
    if ($eMail['Send']['Recipients'][$m['ACBL']] || $eMail['Send']['CC'][$m['ACBL']]) {return true;}
    $eMail['Send']['Others'][$m['ACBL']] = $m; return true;
}    
?>
