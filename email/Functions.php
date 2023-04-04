<?php
/******************************************/
function setContent ($in=null) {
/******************************************/
global $eMail, $content;
       
    if (!$in) {return $content;}
    
    if ($in == 'formAction') {return ' action="index.php?content='.$content.'"';}
    
    if ($in == 'showVersion') {
        return '<div>
                  <p class="showVersion" 
                      style="text-align: right; vertical-align: bottom; font-size: 80%;
                              font-color: blue; font-family: \'Times New Roman\';">
                      <span color="black">Version: </span>'.CURRENT_VERSION.'
                  </p>
                </div>';}                    
}
/******************************************/
function isBeta () {
/******************************************/
global $eMail;

    if (setContent() == 'emailBeta') {return true;}
    else {return false;}
}
/******************************************/
function nextStage () {
/******************************************/
global $eMail, $Steps;
       
    return $Steps[$eMail['Stage']]['nextStage'];
}
/******************************************/
function setStage ($stage=null) {
/******************************************/
global $eMail, $Steps;

    //checkSpecialCases();
    if ($stage === 0) {$eMail['Stage'] = 0;}
    elseif ($_POST['Stage'] == "0" && takeAction() == 'Change') {$eMail['Stage'] = 7;}
    elseif ($stage) {$eMail['Stage'] = $stage;}
    else {$eMail['Stage'] = $_POST['Stage'];}
    
    $s = $Steps[$eMail['Stage']]['Review']['nextStage'];
    if (isModeReview() && $s) {$eMail['Stage'] = $s;} 

    makePriorStage(); 
    setForm();
    return true;
}
/******************************************/
function altStage () {
/******************************************/
global $eMail, $Steps;

    altStage_($Steps[priorStage()]['altStage']); //non-Mode
    altStage_($Steps[priorStage()][showMode_()]['altStage']); //Mode  
}    
/******************************************/
function altStage_ ($s) {
/******************************************/
global $eMail, $Steps;
debugv('alt', $eMail['Stage'], $_POST['PriorStage'], end($eMail['PriorStage']), priorStage(), takeAction(), $s[takeAction()]);
    if (!$s) {return $eMail['Stage'];} 
    
    if (!array_key_exists(takeAction(), $s)) {return $eMail['Stage'];}
    
    if ($s[takeAction()] == CURRENT_STAGE) 
    {                  
        $eMail['Stage'] = priorStage();
    }
    elseif ($s[takeAction()] == LAST_MAIN_STAGE) 
    {                  
        $eMail['Stage'] = lastMainStage() ? lastMainStage() : $eMail['Stage'];
    }
    else 
    { 
        $eMail['Stage'] = $s[takeAction()];
    }

    makePriorStage();
    setForm();
    return $eMail['Stage'];               
}
/******************************************/
function makePriorStage () {
/******************************************/
global $eMail;
       
    if (!$_POST['PriorStage']) 
    {
        return $eMail['PriorStage'] = [$eMail['Stage']];
    }
    elseif ($_POST['PriorStage'] && $eMail['Stage'] == 2) 
    {
        //Reset Prior Stage to Stage 2
        return $eMail['PriorStage'] = [$eMail['Stage']];
    }        
    elseif ($_POST['PriorStage'] && $eMail['Stage'] == 7) 
    {
        $eMail['PriorStage'] = explode(',', $_POST['PriorStage']);
        $eMail['PriorStage'][] = $eMail['Stage'];
        debugv('makePS', $eMail['Stage'], priorStage());
        return $eMail['PriorStage']; 
                
    }        
    elseif ($_POST['PriorStage'] && $eMail['Stage'] == 21) 
    {
        //Stage 21 non-existant
        return $eMail['PriorStage'] = explode(',', $_POST['PriorStage']);
    }        
    else
    {        
        $eMail['PriorStage'] = explode(',', $_POST['PriorStage']);
        return $eMail['PriorStage'][] = $eMail['Stage'];        
    }
}
/******************************************/
function setPriorStage () {
/******************************************/
global $eMail;

    setStage(priorStage());
}       
/******************************************/
function priorStage ($f=null) {
/******************************************/
global $eMail;
    
    if ($f == '4Form') {return implode(',', $eMail['PriorStage']);}
    else 
    {
        return end($eMail['PriorStage']);
    }
}      
/******************************************/
function lastMainStage () {
/******************************************/
global $eMail;

    $reversed_priorStage = array_reverse($eMail['PriorStage']);
    foreach ($reversed_priorStage AS $s)
    {
        if ($s <= 10) {return $s;}
        else {continue;}
    }        
}    
/******************************************/
function processError ($stage=null, $msg=null, $errCode=null) {
/******************************************/
global $eMail;
    
    if (!$msg && !$errCode) {
        if (!$eMail['ProcessError']) {return false;}
        else {return true;} }   
    else {
        errorMsg($msg, $errCode);
        $eMail['ProcessError'] = $errCode;
        logBeta($Code); 
        setStage($stage);
        return true;}     
}
/******************************************/
function clearProcessError () {
/******************************************/
global $eMail;
    
    $eMail['ProcessError'] = null;
}        
/******************************************/
function eMailType () {
/******************************************/
global $eMail;
    
    return $_POST['Type'] ? $_POST['Type'] : DRAFT; 
}
/******************************************/
function setForm () {
/******************************************/
global $eMail, $Steps;

    //Defaults
    $eMail['Sections'] = !$Steps[$eMail['Stage']]['Sections'] ? array('Selection', 'Edit') :
                            $Steps[$eMail['Stage']]['Sections'];
    return true;
}
/******************************************/
function me () {
/******************************************/  
    
    return GetDB($_SESSION['myACBL']);
}
/******************************************/
function isSender ($name) {
/******************************************/
global $eMail;  

    $t = explode('@', $eMail['FromeMail']);
    if ($t[0] == $name) {return true;}
}   
/******************************************/
function addDate () {
/******************************************/
global $eMail;

    return date('Y-m-d', now()).' ';
}
/******************************************/
function changed () {
/******************************************/
global $eMail;

    if ($eMail['Changes']) {return $eMail['Pending'] = true;}
    else {return $eMail['Changed'] = true;}
}
/******************************************/
function anyChanges () {
/******************************************/
global $eMail;

    $eMail['Changes'] = true;
    revise();
    $eMail['Changes'] = null;
    if (changePending()) {
        makeTemp();
        revise();
        saveDraft();}    
    return true;
}
/******************************************/
function forgetChanges () {
/******************************************/
global $eMail;

    if (!isTemp_eMail()) {return true;}
    deleteDraft();        
    restoreDraftFromTemp();
    updateType("Temp", "Draft");        
    unmakeTemp();
    return true;
}
/******************************************/
function changePending () {
/******************************************/
global $eMail;

    if ($eMail['Pending']) {return true;}
    else {return false;}
}
/******************************************/
function unchangedDraft () {
/******************************************/
global $eMail;

    if (!$eMail['Updated']) {return true;}
    else {return false;}
}
/******************************************/
function setFromeMailDefaults () {
/******************************************/
global $eMail, $eMail_Fields;

    $eMail['FromACBL'] =  $eMail_Fields['FromACBL']['default'];
    $eMail['FromName'] =  $eMail_Fields['FromName']['default'];
    $eMail['FromeMail'] = $eMail_Fields['FromeMail']['default'];
    return true;           
}
/******************************************/
function eMailButton($b) {
/******************************************/
global $eMail;

    return '<span style="margin-left: 1em;">' . genButton($b) . '</span>';    
}
/******************************************/
function keepDraft () {
/******************************************/
global $eMail;

    if (Stage(23)) {
        if (isTemp_eMail()) {//Quit process: Keep changes
            unmakeTemp();
            statusMsg('Draft of <b>'.$_POST['Id'].'</b> with changes kept');
            logBeta();} }        
    return true;
}
/******************************************/
function makeClean ($b) {
/******************************************/
global $eMail;

    $charsToClean = str_split("’‘”“"); 
    $replace = array("&rsquo;",  "&lsquo;", "&rdquo;",  "&ldquo;", "&#92;");
    
    $a = str_replace($charsToClean, $replace, $b);
    
    return str_replace("'", "&apos;", $a);           
}
/******************************************/
function restoreOriginalChars ($eDB) {
/******************************************/
global $eMail, $eMail_Fields;

    foreach ($eMail_Fields AS $name => $f)
    {
        if ($f['clean'])
        {
            $eDB[$name] = str_replace("&apos;", "'", $eDB[$name]);
        }
    }
    return $eDB;
}
/******************************************/
function clearSelections () {
/******************************************/
global $eMail, $eMail_Fields;

    foreach ($eMail_Fields AS $name=>$attributes) {
        if (!$attributes['selection']) {continue;}
        if ($attributes['default']) {$eMail[$name] = $attributes['default'];}
        else {$eMail[$name] = null;} }
    
    changed();
    return true;    
}
/******************************************/
function clear () {
/******************************************/
global $eMail;

    foreach (func_get_args() AS $a) {
        $eMail[$a] = null;
        $fields[$a] = 'NULL';}

    changed();    
    updateDraft($fields);                            
}
/******************************************/
function makeLiterals ($p) {
/******************************************/
global $eMail;

    if (!$p) {return null;}
    foreach ($p AS $p_) {$new_p .= '"'.$p_.'", ';}
    return substr($new_p, 0, -2);
}
/******************************************/
function startClock () {
/******************************************/
global $eMail;

    $eMail['DateSent'] = timeStamp();
}            
/******************************************/
function endClock () {
/******************************************/
global $eMail;

    $eMail['DoneAt'] = timeStamp();
}              
/******************************************/
function setFinal () {
/******************************************/
global $eMail, $Steps;

    if (isModeSend()) {$eMail['Final'] = true;}
    return true;    
}
/******************************************/
function getMode () {
/******************************************/
global $eMail, $To;

    $v = $eMail['Mode'] ? $eMail['Mode'] : 'Test';
    
    if ($eMail['Stage'] > 2) 
    {
        if (isModeTest()) 
        {
            return '<p style="text-align: center;"><span class="bold"><em>Test Mode</em></span></p>';
        }
        elseif (isModeReview())
        {
            $To = ['Test', 'Review'];
        }
        else
        {
            $To = ['Test', 'Send'];
        }
    }
    foreach ($To AS $t) 
    {
        if ($t == $v) {$checked = ' checked';} else {$checked = null;} 
        $o .= '<input name="Mode" type="radio" value="'.$t.'"'.$checked.'>'.$t.'&nbsp;&nbsp;';
    }
    return '
       <p class="P2"><span class="bold blue">Mode:</span> ' .
             $o . '
       </p>' . PHP_EOL;                  
}
/******************************************/
function isModeTest () {
/******************************************/
global $eMail;

    if ($eMail['Mode'] == 'Test') {return true;}
    else {return false;}
}
/******************************************/
function isModeReview () {
/******************************************/
global $eMail;

    if ($eMail['Mode'] == 'Review') {return true;}
    else {return false;}
}
/******************************************/
function isModeSend () {
/******************************************/
global $eMail;

    if ($eMail['Mode'] == 'Send') {return true;}
    else {return false;}
}
/******************************************/
function salutation () {
/******************************************/
global $eMail, $Elements;
    
    if ($eMail['Salutation'] == 'TOD') {return getTOD_Salutation();}
    else {return '
            <p class="Salutation">'.
                $Elements['Salutation'][$eMail['Salutation']]['Title'].'
            </p>'.PHP_EOL;} 
}    
/******************************************/
function get_Element ($element) {
/******************************************/
global $eMail, $Elements;

    foreach ($Elements[$element] AS $e=>$g) {
        if ($eMail[$element] == $e) {$s = ' selected';} 
        else {$s = null;}
        $o .= '<option value="'.$e.'" Title="'.$g['Title'].'" '.$s.'>'.$g['Use'].'</option>' . PHP_EOL;}
        
    return '
            <select style="margin: 2px 2px; font-size: 80%;" name="'.$element.'">'.
                $o.'
            </select>';        
}

/******************************************/
function getTOD_Salutation () {
/******************************************/
global $eMail;

    $H = date('H', now());
    if ($H > 3 && $H < 12) {$s = 'Morning ';}
    elseif ($H > 12 && $H < 16) {$s = 'Afternoon ';}
    else {$s = 'Evening ';}
    return '
            <p class="Salutation">'.
                'Good '.$s.'[Member],
            </p>'.PHP_EOL;                                 
}
/******************************************/
function get_PickDraft () {
/******************************************/
global $eMail;

    return '
            <p><span class="bold blue">Use Existing Draft:</span>'.
                get_player_options('UseExistingDraft',null,null,false).' 
           </p>'.             
           get_eMailFile(NEW_DRAFT);    
}
/******************************************/
function pickedDraft () {
/******************************************/
global $eMail, $template;
   
    if ($_POST['UseExistingDraft'] == 'Y' && $_POST['newDraftName']) {
        errorMsg('Picking BOTH existing draft and a new draft is incompatible; 
                    please start over.', 'pickDraft_90');
        doFile(); return;}

    if ($_POST['UseExistingDraft'] == 'Y') {
        $template['Id'] = $_POST['Draft_Id'];
        $template['Type'] = DRAFT;
        $template['Author'] = loggedInAs();
        loadTemplate(); 
        setStage(1);
        setMode();
        setStatus();                                
        return true;}
    
    elseif (!$_POST['newDraftName']){
        errorMsg('No draft template picked; please start over.', 'pickDraft_92');
        doFile();
        return;}
    
    //Check for duplicate/create new draft
    else {        
        createDraft();
        setMode();
        setStatus();
        setStage(1);                
        return true;}
}
?>
