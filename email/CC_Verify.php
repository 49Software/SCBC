<?php
/******************************************/
function checkCC () {
/******************************************/
global $eMail;

    if ($_POST['CC_ACBL'] && $_POST['CC_ACBL'] != "SelectCC_Name") { 
        $eMail['CCs'] = 
            $eMail['CCs'] ? $eMail['CCs'].','.$_POST['CC_ACBL'] : $_POST['CC_ACBL'];}
    return true;
}                                     
/******************************************/
function get_ccNames () {
/******************************************/
global $eMail;
    
    if ($_POST['lookUpName']) {return lookUpCCName();}                           
    else {return enterCCName();}                                             
}
/******************************************/
function removeCC_Name () {
/******************************************/
global $eMail;

    foreach (explode(',',$eMail['CCs']) AS $a) {
        if ($_POST['CC'] != $a) {$updatedCCs[] = $a;}
        else {changed();} }
    if (!$updatedCCs) {$eMail['CCs'] = null;}
    else {$eMail['CCs'] = implode(',',$updatedCCs);} 
      
    return true;
}
/******************************************/
function genCCForms () {
/******************************************/
global $eMail;

    if (!$eMail['CCs']) {return null;}
    
    foreach (explode(',',$eMail['CCs']) AS $a) {
        $removeCC .= '
                     <form id="RemoveCC_'.$a.'" '.setContent('formAction').' method="POST">'.
                        getFormBase().'
                        <input name="CC" type="hidden" value="'.$a.'">
                     </form>';}
                     
    return $removeCC;
}
/******************************************/
function genCCButtons () {
/******************************************/
global $eMail;

    foreach (explode(',',$eMail['CCs']) AS $a) {
        $removeButtons .= '
                     <button class="textButton" type="submit" name="appSubmit" form="RemoveCC_'.$a.'" title="Click To Remove" 
                        value="RemoveCC" style="margin: 2px 2px; padding: 1px 2px; font-size: 80%; vertical-align: middle;">'.
                        myName(GetDB($a),'Nick_Last').'<span style="font-size: 130%;">&#9447;</span>'.'
                     </button> ';}
                     
    return $removeButtons;
}
/******************************************/
function lookUpCCName () {
/******************************************/
global $eMail;

    if (!$temp = GetDBbyName(trim($_POST['lookUpName']))) {
        statusMsg('The Player Name you entered, ' . $_POST['lookUpName'] . ', is not found.');
        return enterCCName();}
    else {
        foreach ($temp as $t) {
            $names .= '<option value="' . $t['ACBL'] . '">' . $t['Name'] . '</option>' . PHP_EOL;}             
        return '
                <select name="CC_ACBL" style="margin: 2px 2px; font-size: 80%;" maxlength="20">' . PHP_EOL . '
                  <option value="SelectCC_Name">Select CC Name</option>' . PHP_EOL . 
                        $names . '
                </select>'.PHP_EOL.showCC();}

}                
/******************************************/
function enterCCName () {
/******************************************/
global $eMail;

    return '<input type="text" name="lookUpName" placeholder="Last Name (3+ letters)" style="margin: 2px 2px; 
                font-size: 80%;" size="20" maxlength="20">'.showCC();
}                
/******************************************/
function addCCs () {
/******************************************/
global $eMail;

    if (!$eMail['Send']['Recipients'] || !$eMail['CCs']) {return true;}
    $CCs = explode(',',$eMail['CCs']); 
    if (in_array(System, $CCs)) {$CCs = array_merge($CCs, getBOD_ACBLs());}
    
    foreach ($CCs AS $cc) 
    {        
          $m = GetDB($cc);
          if ($eMail['Send']['Recipients'][$m['ACBL']]) {continue;}
          else {$eMail['Send']['CC'][$m['ACBL']] = $m;}
    }                         
    return true;    
}                
/******************************************/
function resetVerify () {
/******************************************/
global $eMail;

    if ($eMail['Verify']) {
        $eMail['Verify'] = null;
        updateDraft(array('Verify'=>'NULL'));
        changed();}
    
    return true;  
}
/******************************************/
function sendVerificationCode () {
/******************************************/
global $eMail;

    $eMail['Verify'] = substr(rand(), 1,6);
    updateDraft(array('Verify'=>$eMail['Verify']));
    changed();
    
    if (isSysAdm() || isBeta() || isModeReview()) 
    {
        return statusMsg('Verification Code: '.$eMail['Verify']);
    }
    else 
    {
        $eMail['Body'] .= '<p class="emailVerify">Verification Code: '.$eMail['Verify'].'</p>';
        send_e(me());
        return true;
    }    
}
/******************************************/
function validateVC () {
/******************************************/
global $eMail;

    if ($_POST['VerificationCode'] == $eMail['Verify']) {return true;}
    else {
        errorMsg('The verification code you entered does not match', 'eVC_90');
        setStage(41);
        return false;}        
}
/******************************************/
function verify ($button) {
/******************************************/
global $eMail;

    $button['action'] = 'CheckVC';
    return '<span style="margin-left: 1em;">'.                
                genButton($button).                
                ' <input type="text" name="VerificationCode" size="8" style="font-size: 1.2em; vertical-align: bottom;" autofocus>'.' 
           </span>';
}

?>
