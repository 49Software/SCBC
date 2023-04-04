<?php
/******************************************/
function get_selection () {
/******************************************/
global $eMail;

     $selections .= '                             
          <div class="showSelection">
            <input name="selection" type="hidden" value="included">'.PHP_EOL.
            getMode().'
                        
            <fieldset><legend class="fieldset2ndLevel">Special Selectors</legend>         
              <p class="note">Standard Selectors are ignored when a Special Selector is used</p>
              
              <p><span class="bold blue" title="Send review copy to SCBC Board">
                    SCBC Board: </span>'.showOption('Board', false).
                    fn('&nbsp;(Used only during Review mode)').'
              </p>
              
              <p><span class="bold blue" title="Send to a specific member using their ACBL#">
                    Single Member: </span><input type="text" name="ACBL" size="8" value="'.$eMail['ACBL'].'">
              </p>';
              
              if (isSysAdm()) {                                   
     $selections .= '
              <p><span class="bold blue" title="Use D21 member database for Sectional Emails">
                    Sectional:</span>'.PHP_EOL.get_special($eMail).'
              </p>';}
     $selections .= '              
              <p><span class="bold blue" title="Select all the players in a recent game">
                    Game:</span>'.PHP_EOL.
                        getGameResults($eMail['Game'], 21, $setSelectionPrompt="Select Game Participants for eMail") . '
                        <br><span class="instructions">(BBO not supported)</span>                        
              </p>            
            </fieldset>
            <div style="margin-bottom: 6px;">&nbsp;</div>'.PHP_EOL.' 

            <fieldset><legend class="fieldset2ndLevel">Standard Selectors</legend>
              <p><span class="bold blue" title="Status of member in SCBC records">Member Status: </span>' .
                eMailSelect_Status($activeAndAssociateOnly=true).PHP_EOL.
              
              queryTotalMPs($eMail['MPsMin'], $eMail['MPsMax']) . '                                  

              <p><span class="bold blue" title="Year To Date Masterpoints won">YTD MPs:</span> ' . 
                    get_player_options('YTDMPs', $eMail['YTDMPs']) . '
              </p>

              <p><span class="bold blue" title="Member has a BBO name registered in SCBC records">On BBO: </span> ' . 
                get_player_options('BBO', $eMail['BBO']) . '                                    
              </p>

              <p><span class="bold blue" title="Member has presented documentation of their vaccine status">Covid Vaccine: </span> ' . 
                get_player_options('Covid', $eMail['Covid']) . '                                    
              </p>
            </fieldset>
            <div style="margin-bottom: 6px;">&nbsp;</div>'.PHP_EOL;              
            
            if (isSysAdm()) {                                   
     $selections .= '
            <fieldset><legend class="fieldset2ndLevel">Excluding</legend>
              <p class="bold blue">Group: ' . PHP_EOL .
                    excludingMemberSelects($eMail['Excludes']) . '
              </p>
            
              <p><span class="bold blue">Already Sent:</span> ' . 
                get_player_options('AlreadySent', $eMail['AlreadySent']) . '                                    
              </p>
            </fieldset>'.PHP_EOL;}         
     $selections .= '
         </div>';
     
     return $selections;          
}
/******************************************/
function generate_SQL () {
/******************************************/
global $eMail, $LR;
   
    $from = 'SELECT ACBL, FirstName, LastName FROM roster ' . PHP_EOL;
    $selects[] = 'eMail IS NOT NULL AND Subscribe = "Y"';
    $orderBy = ' ORDER BY LastName, FirstName';
    
    validateSpecialSelectors();
                 
    if ($p = getReviewers()) {
        $selects[] = 'ACBL IN (' .$p. ')';
        $where = ' WHERE ' . implode(' AND ', $selects);     
        $eMail['SQL'] = $from . $where . $orderBy; return true;}
               
    elseif (!is_blank($eMail['ACBL'])) { 
        $eMail['SQL'] = $from . 'WHERE ACBL = "'.$eMail['ACBL'].'"'; return true;}      

    elseif ($eMail['Game'] && $eMail['Game'] != NONE) {
      if (!$p = getGameParticipants($eMail['Game'])) {return false;}
      else {$eMail['SQL'] = $from . 'WHERE ACBL IN (' .$p. ')' . $orderBy; return true;} }
    
    elseif ($s_sql = get_Special($eMail, $type='Generate')) {return $s_sql;}        
    
    else {//Unit 550 SQL Statements

      if ($eMail['InfoCheck']) {$selects[] = 'OptOut1 = "N"';}  
        
      $selects[] = '(Status IN '.setStatusForSQL().')';   
        
      if ($eMail['BBO'] == 'Y') {$selects[] = 'BBOName IS NOT NULL';}
      elseif ($eMail['BBO'] == 'N') {$selects[] = 'BBOName IS NULL';}
      
      if ($eMail['Covid'] == 'Y') {$selects[] = 'Covid > 0';}
      elseif ($eMail['Covid'] == 'N') {$selects[] = 'Covid = 0';}
      
      //Simulate MPs for rosterDB code
      $_POST['MPsMin'] = $_POST['MPsMin'] ? $_POST['MPsMin'] : $eMail['MPsMin']; 
      $_POST['MPsMax'] = $_POST['MPsMax'] ? $_POST['MPsMax'] : $eMail['MPsMax'];
      //Custom MP Selection         
      segmentOn_MPs($from, $selects, date('Y', now()));
      //segmentOn_MPs($from, $selects, 2022);
      
      //Generate Where clause
      $where = ' WHERE ' . implode(' AND ', $selects);     
      $eMail['SQL'] = $from . $where . $orderBy;}
      
    return true;
}
/******************************************/
function validateSpecialSelectors () {
/******************************************/
global $eMail;

    if ($eMail['ACBL']) { 
        if (!GetDB($eMail['ACBL'])) {
            errorMsg('Member ACBL number, '.$eMail['ACBL'].', is invalid or is not a SCBC member; 
                      ACBL selection removed.', 'vSS_80'); clear('ACBL');} }
    
    if ($eMail['Sectional'] && $eMail['Game']) {
        errorMsg('Selecting <b>both</b> Sectional and Game are incompatible; 
                  both selectors are removed.', 'vSS_81'); clear('Game', 'Sectional');}
    
    if ($eMail['Game']) {
        if (!$r = getResult($eMail['Game'])) {                
            errorMsg('Game Selection, <b>'.$eMail['Game'].'</b>, not found; selector removed.', 
            'vSS_82.1'); clear('Game');}
        elseif (!isPosted($r)) {
            errorMsg('Game selected, <b>'.$eMail['Game'].'</b>, is not Posted; selector removed.', 
            'vSS_82.2'); clear('Game');} }
    /*    
    if (isModeReview()) {
        if ($eMail['Sectional'] || $eMail['Game']) {
            errorMsg('Only SCBC Board and/or Member may be addressed during Review mode; 
                other Special Selectors ignored', 
                'vSS_84'); } }

    if (!isModeReview()) {
        if ($eMail['Board']) {
            statusMsg('<b>SCBC Board</b> <em>Special Selector</em> only used during Review mode; 
                otherwise ignored'); } }
    */
    return true;
}
/******************************************/
function getReviewers () {
/******************************************/
global $eMail;

    if (!isModeReview()) {return false;}
    $p[] = loggedInAs();         
    if ($eMail['ACBL']) {$p[] = $eMail['ACBL'];}
    if ($eMail['Board']) {$p = array_merge($p, getBOD_ACBLs());}
    return makeLiterals($p);    
}
/******************************************/
function eMailSelectStatus ($activeAndAssociateOnly=true) {
/******************************************/
global $eMail;

//eMail Status changed to array

    $_POST['Status'] = $eMail['Status'];
    return select_Status($activeAndAssociateOnly=true);
}
/******************************************/
function setStatus () {
/******************************************/
global $eMail;

    if (!$eMail['Status'] && !$_POST['Status'])
    {
        $eMail['Status'] = ['A']; //Default   
    }
    elseif ($eMail['Stage'] && !$_POST['Status']) 
    {
        return $eMail['Status'];
    }
    else 
    {
        sort($eMail['Status']);
        sort($_POST['Status']);
        if ($eMail['Status'] == $_POST['Status'])
        {
            return $eMail['Status'];
        }
        else
        {
            changed();            
            return $eMail['Status'] = $_POST['Status'];
        }
    }
}
/******************************************/
function setStatusForSQL () {
/******************************************/
global $eMail;

      if ($eMail['Status'] == ['A']) 
      {
        return '("A")';
      }
      elseif ($eMail['Status'] == ['A','S'] || $eMail['Status'] == ['S','A']) 
      {
        return '("A", "S")';
      }
      elseif ($eMail['Status'] == ['S']) 
      {
        return '("S")';
      }
      else  
      {
        errorMsg('Unknown Status field, '.$eMail['Status'].', Status set to Active. 
                    Please contact webmaster with code and trace info:<br>'.debugb(), 'sSQ_80');
        return '("A")';
      }  
}    
/******************************************/
function setMode () {
/******************************************/
global $eMail;

    $eMail['Mode'] = $_POST['Mode'] ? $_POST['Mode'] : 'Test';     
    return true;
}
/******************************************/
function getGameParticipants () {
/******************************************/
//Output: List of ACBL#s, e.g. 1234567, R654321
include "SCBCv2/SA_functions.php";
    
    $f = pathToFile($_POST['selectedGame']);
    $r = rToLoc($_POST['selectedGame'], "ID_ONLY");
    $lines = file($f);
    $pairs = SA_PairData($r, $lines);

    foreach ($pairs AS $Direction_PairNo => $Players) {
      foreach ($Players AS $x=>$p_s) {
        if ($x == 'GamePercent') {continue;} //Not interested in winners
        foreach ($p_s AS $y=>$p) {
          if ($p['ACBL']) {
            //List of ACBLs
            $ACBLs .= '"'.$p['ACBL'].'", ';} } } }

    if ($ACBLs) {return substr($ACBLs,0,-2);}
    else {appError('eMail_gGP_90', 'No valid ACBL Numbers found!'); return false;}
}
/******************************************/
function excludingMemberSelects ($xclude) {
/******************************************/

    $excludes = eMailExcludes();
    
    if (is_null($xclude)) { 
        $o = '<option value="" selected>Select a group to exclude</option>' . PHP_EOL;}   

    foreach ($excludes AS $e=>$members) {
        if ($xclude && $e == $xclude) {$selected = ' selected';}
        else {$selected = null;} 
        $o .= '<option value="'.$e.'"'.$selected.'>'.$e.'</option>' . PHP_EOL;}
    return '
            <select name="Excludes">'.
                $o.'
            </select>';
}
/******************************************/
function get_Special ($eMail, $type='Get') {
/******************************************/
                      
   $SS['RS-D21']           = 'TotalMPs BETWEEN 1 AND 500 AND Unit IN (500, 501, 503, 507, 522, 524, 529, 530, 550)';
   $SS['SU-D21']           = 'TotalMPs > 10 AND Unit IN              (500, 501, 503, 507, 522, 524, 529, 530, 550)';   
/*
                           �    500: South County (South Alameda Co)
                           �    501: Livermore Valley
                           �    503: Palo Alto
                           �    507: Silicon Valley (SJ)
                           �    522: Fresno
                           �    524: Gilroy
                           �    529: Modesto
                           �    530: Monterey
                           �    550: Santa Cruz
/********* Other Selections *************/   
   $SS['NLM']              = '(TotalMPs > 10 AND (SUBSTRING(ACBL,1,1) BETWEEN "0" AND "9" OR SUBSTRING(ACBL,1,4) = "scbc"))';
/********* End Selections *************/
    
    if ($type == 'Get') {
        if (isSysAdm()) {
            if (!$eMail['Special']) {
                $o .= '<option value="" selected>Player Set</option>'.PHP_EOL;}
            
            foreach ($SS AS $s=>$criteria) {
              if ($eMail['Special'] == $s) {$selected = ' selected';} else {$selected = null;}
              $o .= '<option value="'.$s.'"'.$selected.'>'.$s.'</option>'.PHP_EOL;}
            
            return '
                     <select class="select" name="Special">' . PHP_EOL .
                         $o. '
                     </select>';}
        else {return 'Restricted Usage';} }    
    
    elseif ($type == 'Generate') {   
      if (!$eMail['Special']) {return null;}
      
      if (substr($eMail['Special'],-3,3) == "D21") {//Send Sectional eMails
          $from = 'SELECT * FROM D21 ' . PHP_EOL;
          $where = 'WHERE ' . $SS[$eMail['Special']] . ' AND eMail <> "Confidential@acbl.org"';
          $orderBy = ' ORDER BY Unit, LastName';
          return $from . $where . $orderBy;}      
      
      else {return null;} }             
}

?>
