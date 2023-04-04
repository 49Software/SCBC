<?php
/******************************************/
function showActionInfo () {
/******************************************/
global $eMail, $Steps;

    $info = getInfo();
    
    if (strpos($info, 'Recipients')) {
        return str_replace('Recipients', '<span style="color: black">Recipients: '.showRecipientCount().'</span>', $Steps[$eMail['Stage']]['i']);}   
    elseif (strpos($info, '#eMails')) 
    {
        if (showRecipientCount() == 1) 
        {
            $replace = '<span style="color: black">'.showRecipientCount().' Email</span>';
        }
        else
        {         
            $replace = '<span style="color: black">'.showRecipientCount().' Emails</span>';            
        }
        return str_replace('#eMails', $replace, $info); 
    }    
    else
    {
        return $info;
    }
}
/******************************************/
function getInfo () {
/******************************************/
global $eMail, $Steps;

    if ($i = $Steps[$eMail['Stage']][showMode_()]['i'])
    {
        return $i;
    }
    else
    {
        return $Steps[$eMail['Stage']]['i'];
    }
}
/******************************************/
function showActions ($section) {
/******************************************/
//input: array of action buttons
global $eMail, $Steps, $Actions;
    
    if (!$Steps[$eMail['Stage']]['buttons'][$section]) {return null;}
    
    foreach (getButtons($section) AS $group=>$g) {
        foreach ($g AS $i => $a) {
              if ($Actions[$a]['genButton']) {
                $f = $Actions[$a]['genButton'];
                $p .= $f($Actions[$a]);}
              else {
                $b = $Actions[$a];
                $b['action'] = $a;   
                $p .= eMailButton($b);} }
          $o .= '<p>'.
                      $p.'
                  </p>'.PHP_EOL;
          $p = null;}                
                  
    return $o;
}
/******************************************/
function getButtons ($section) {
/******************************************/
global $eMail, $Steps;
 
        if ($s = $Steps[$eMail['Stage']][showMode_()]['buttons'][$section]) 
        {
            return $s;
        }
        else
        {
            return $Steps[$eMail['Stage']]['buttons'][$section];
        }
}   
/******************************************/
function showSectionMsg ($S) {
/******************************************/
global $eMail;

    if ($S == 'Confirm') 
    {
        if (isModeTest()) {return null;}
        else 
        {
            return '
                <p>
                    The next screen provides the 
                    <span style="background-color: yellow;">
                        <span style="color: black; font-weight: bold;">Send</span>&nbsp;button 
                         which once clicked triggers the sending function 
                         and can not be stopped or reversed.
                    </span>
                </p>';
        }
    }         
}
/******************************************/
function showRecipientCount () {
/******************************************/
global $eMail;

    if (!$eMail['Send']) {return null;}
    foreach ($eMail['Send'] AS $List) {$count += count($List);}
    return $count;
}    
/******************************************/
function edit_eMail () {
/******************************************/
global $eMail;
    
    return '
        <input name="edit" type="hidden" value="included">'.PHP_EOL.'
        <p class="bold blue" style="margin: 2px 2px;">Subject:</span> 
                <input size="60" name="Subject" type="text"  
                style="font-weight: bold;" 
                placeholder="[Enter Subject]" value="'.$eMail['Subject'].'">
        </p>
        <p class="bold blue" style="margin: 2px 2px;">From: '.
            authorizedSenders('select', $eMail).'
        </p>
        <div>  
            <p class="e_option">
                To: <span style="color: black;">'.showMode().'
            </p>
        </div>
        <div>
            <p class="e_option">Greeting '.get_Element('Salutation').'</p>                     
            <p class="e_option"> | Signature '.get_Element('Sign').' | '.showOption('Contact').'</p>                    
        </div>
        <div style="clear:both;"></div>
        <p class="e_option">cc: '.get_ccNames().'</p>   
        <textarea name="eMailHTML" id="editor1" rows="10" cols="80">'
                .$eMail['Body'].'
        </textarea>
        <script>CKEDITOR.replace( "eMailHTML" );</script>';        
}    
/******************************************/
function showPreview () {
/******************************************/
global $eMail;
    
    return            
           '<p class="eSubject">
                <span class="eSubject_">Subject: </span>'.
                $eMail['Subject'].'
            </p>'.PHP_EOL.'                    

            <p class="eFrom">
                <span class="eFrom_">From: </span>'.
                $eMail['FromName'].' ['.$eMail['FromeMail'].']
            </p>'.PHP_EOL.'
            
            <p class="eTo">
                <span class="eTo_">To: </span>'.
                showMode().'
            </p>'.PHP_EOL.
            
            showCCPreview().'
            
            <div class="bodyWrap" name="eMailDraft">'.
                showSaluatation().$eMail['Body'].showSignature().showContact().'
            </div>';                    
}
/******************************************/
function showSelection () {
/******************************************/
global $eMail;

     return                              
          getMode().          
          
          showSelection_('Status', 'Member Status').'
          
          <fieldset><legend class="fieldset2ndLevel">Selection by ACBL # or Game Participants</legend>'.         
                showSelection_('ACBL').
                //showSelection_('Sectional').
                showSelection_('result', 'Game').'            
          </fieldset>'.PHP_EOL. 
          
          showSelectionMPs().
          
          showSelection_('YTDMPs', 'YTD MPs').
          
          showSelection_('BBO', 'On BBO').
          
          showSelection_('Covid', 'Covid Vaccine').'                                            

          <fieldset><legend class="fieldset2ndLevel">Excluding</legend>'.
                showSelection_('Group').
                showSelection_('AlreadySent', 'Already Sent').'
          </fieldset>'.PHP_EOL; 
}
/******************************************/
function showSelection_ ($name, $label=null) {
/******************************************/
global $eMail, $eMail_Fields;

    $label = $label ? $label : $name;
    
    if ($eMail_Fields[$name]['version'] > CURRENT_VERSION) {
        return sSel_($label, FUTURE, 'p2S');}  
    elseif ($eMail_Fields[$name]['show']) {
        $f = 'show'.$name;        
        return sSel_($label, $f());} 
    elseif (!$eMail[$name]) {
        return sSel_($label, IGNORED);}
    else {                  
        return sSel_($label, $eMail[$name]);}
}
/******************************************/
function sSel_ ($label, $value=null, $span='black') {
/******************************************/
global $eMail;

    if ($value == 'Y') {$value = 'Yes';}
    if ($value == 'N') {$value = 'No';}    
    
    $class = $c ? ' class="'.$c.'"' : null;
    return '
          <p class="P2">'.$label.': 
            <span class="'.$span.'">'.$value.'</span>
          </p>';   
}
/******************************************/
function showSelectionMPs () {
/******************************************/
global $eMail;

     return '
            <div class="lM_Group" style="margin-top: 5px;"><div class="groupTitle"><span class="bold blue">Total MPs:</span></div>
               <div class="items"><p class="lM_Label">Minimum</p><span class="bold">'.$eMail['MPsMin'].'</span></div>
               <div class="items"><p class="lM_Label">Maximum</p><span class="bold">'.$eMail['MPsMax'].'</span></div>
            </div><div style="clear:both; margin-bottom: 5px;"></div>' . PHP_EOL;
}
/******************************************/
function showOption ($opt, $showLabel=true) {
/******************************************/
global $eMail;

    $label = $showLabel ? $opt.' ' : null;
    return $label.get_player_option($opt, $eMail[$opt]);
}    
/******************************************/
function showCC () {
/******************************************/
global $eMail;

    if (!$eMail['CCs']) {return null;}

    else {return '<span display="inline" style="margin: 2px 2px; font-size: 80%;">'.
                    genCCButtons().
                 '</span>';}                       
}
/******************************************/
function showCCPreview () {
/******************************************/
global $eMail;

    if (!$eMail['CCs']) {return null;}
    
    foreach (explode(',',$eMail['CCs']) AS $a) {
        $ccNames[] = myName(GetDB($a), 'Nick_Last');}

    return '    
        <p class="Element_L0">
            <span class="Element_L2">CC: </span>'.
            implode(', ',$ccNames).'
        </p>'.PHP_EOL;
}
/******************************************/
function showMode () {
/******************************************/
global $eMail;

    if     (isModeTest())   {return 'Test (Send turned off)';}
    elseif (isModeReview()) {return showReviewers();}    
    elseif (isModeSend())   {return 'Sending (see list below)';}

    else {return 'Unknown value for "Mode"; no emails will be sent.';}
}
/******************************************/
function showMode_ () {
/******************************************/
global $eMail;

    if     (isModeTest())   {return 'Test';}
    elseif (isModeReview()) {return 'Review';}    
    elseif (isModeSend())   {return 'Send';}

    else {return null;}
}
/******************************************/
function showReviewers () {
/******************************************/
global $eMail;
    
    $c = ' (<span class="blue">Review Copy</span>)';
    if ($eMail['Board']) {$reviewers[] = 'SCBC Board';}
    if ($eMail['ACBL'])  {$reviewers[] = myName(GetDB($eMail['ACBL']));}
    if (!$reviewers)                {return 'You'.$c;}
    if (count($reviewers) == 1)     {return $reviewers[0].' and You'.$c;}    
    else {$reviewers[] = ' and You'; return implode(', ', $reviewers).$c;}    
}    
/******************************************/
function showStatus () {
/******************************************/
global $eMail;
    
    if (!$eMail['Stage'] || $eMail['Stage'] <= 1 || processError()) {return null;}
    if ($eMail['Status'] == '(A)') {return 'Active';}
    elseif ($eMail['Status'] == '(A,S)' || $eMail['Status'] == '(A, S)') {return 'Active & Associate';}
    elseif ($eMail['Status'] == '(S)') {return 'Associate';}
    else 
        {
          errorMsg('Unknown Status field, '.$eMail['Status'].', Status set to Active. 
                    Please contact webmaster with code and trace info:<br>'.debugb(), 'sST_80');
          return 'Active';
        }  
}    
/******************************************/
function showSaluatation () {
/******************************************/
global $eMail;

    if ($eMail['Salutation'] == 'None') {return null;}
    elseif ($eMail['Salutation'] == 'Time') {$s = getTOD_Salutation();}
    else {$s = salutation();}
    return '
            <p class="Salutation">'.
                $s.'
            </p>';                          
}
/******************************************/
function showSignature () {
/******************************************/
global $eMail;
    
    if ($eMail['Sign'] == 'Personal') {        
            if (isSender('info'))
            {
                $name = '<p class="Member">SCBC Board of Directors</p>';
                checkSender();
            }
            else
            {
                $s = isSysAdmActualSender();
                $name = '<p class="Member">'.myName(GetDB($s['ACBL']), 'Nick').'</p>';
            }
    }
    elseif ($eMail['Sign'] == 'Members') 
    { 
                $name = '<p class="Member">Members of the Santa Cruz Bridge Center</p>';
                checkSender();
    }   
    else 
    {           
                $name = showNameWithTitle();
    }
    return $name.sendingForAuthor();    
}
/******************************************/
function showNameWithTitle () {
/******************************************/
global $eMail;

    if (isSender('info')) 
    {
          checkSender();
          return '
                  <p class="Member">SCBC Board of Directors</p>
                  <p class="Contact">ACBL of Santa Cruz County</p>';
    }                                 
    elseif (isSender('system')) 
    {        
          return '
                  <p class="Member">'.myName(GetDB(SysAdm), 'Nick_Last').'</p>
                  <p class="Contact">SCBC Webmaster</p>';
    }                                 
    else 
    {
          $s = isSysAdmActualSender();
          return '
                  <p class="Member">'.myName(GetDB($s['ACBL']), 'Nick_Last').'</p>
                  <p class="Org">'.$s['Officer'].'</p>';
    }                                          
}
/******************************************/
function isSysAdmActualSender () {
/******************************************/
global $eMail;

    $s = authorizedSenders('ACBL', $eMail);          
    if (isSysAdm()) 
    {
      $eMail['Sender'] = myName(GetDB('SysAdm'), 'Nick_Last');        
    }
    return $s;
}
/******************************************/
function checkSender () {
/******************************************/
global $eMail;

    if (!isSysAdm())
    {
        $eMail['Sender'] = myName(me(), 'Nick_Last');
    }
    return true;        
}
/******************************************/
function sendingForAuthor () {
/******************************************/
global $eMail;

    if (!$eMail['Sender']) {return null;} 

    return '
            <p class="Contact">
                (Sent by '.$eMail['Sender'].')
            </p>';            
}
/******************************************/
function showContact () {
/******************************************/
global $eMail, $myUnit;

    if (!$eMail['Contact']) {return null;}
    
    $m = me();
    if ($eMail['Sign'] == 'Personal') {    
        return '
          <p class="Contact">email: <a href="mailto:' . $eMail['FromeMail'] . '">' . $eMail['FromeMail'] . '</a></p>      
          <p class="Contact">phone: '.primaryPhone($m).'</p>';}
    else {
        return '     
          <p class="Contact">web: <a href="' . $myUnit['homePage'] . '">' . $myUnit['homePage'] . '</a></p>     
          <p class="Contact">email: <a href="mailto:' . $myUnit['eMail'] . '">' . $myUnit['eMail'] . '</a></p>      
          <p class="Contact">phone: ' . $myUnit['Phone'] . '</p>';}
}          
/******************************************/
function showEmailQd () {
/******************************************/
global $eMail;

    return 
            count($eMail['Send']['Recipients']) + 
            count($eMail['Send']['CC']) + 
            count($eMail['Send']['Others']);
}
/******************************************/
function addCSS ($t) {
/******************************************/
global $eMail;
//input text: one or more p with class specified, e.g.
//<p class="some_class">text</p>

$cssForClass = array(
                'Member'=>'style="margin-top: 4pt; margin-bottom: 0pt; font-size: 14pt; 
                            font-family: \'Lucida Handwriting\'; font-style: italic; color: navy;"',
                'Org'=>   'style="margin-top: 4pt; margin-bottom: 0pt; font-size: 14pt;
                            font-family: Arial, sans-serif; font-style: italic; color: navy;"',
                'Contact'=>'style="margin-top: 1pt; margin-bottom: 0pt; font-size: 10pt;
                            font-family: Arial, sans-serif; font-weight: bold;"');
        
    foreach (explode('<p class=', $t) AS $q) {        
        if (preg_match('/^"(.*)"(.*)$/U', $q, $u)) {
            $o .= '<p class="'.$u[1].'" '.$cssForClass[$u[1]].$u[2].PHP_EOL;} }        
    
    return $o;
}
/******************************************/
function showConfirmQuit () {
/******************************************/
global $eMail;

    if (changePending()) {return '<p>Do you <b>really</b> want to quit with <b>unsaved changes?</b></p>';}
    else {return '<p>You have <b>no unsaved changes</b>, tap Exit to leave app now.</p>';}
}   
/******************************************/
function showLists () {
/******************************************/
global $eMail, $Elements;
   
   if (!$eMail['Send'] && !$eMail['Error']) {return null;}
   
   if (isSysAdm()) 
    {
        $showSQL = '         
             <div style=\'font-family:"Times New Roman"; margin-top:1em; padding:.25em;border:1px dashed black; background-color:#ffff80;\'>' .
                   $eMail['SQL'] . '                  
             </div>' . PHP_EOL;
    }                  
   
   $tableHeaders = array('Unit', 'TotalMPs', 'BBOName');

   foreach ($Elements['List'] AS $L => $group) {
        foreach ($group AS $subgroup => $label) { 
            $o .= elistMembers($eMail[$L][$subgroup], $label, $tableHeaders);} }     
   
   return '
          <div style="width: 80%; margin-top:.5em; padding:.25em; border:1px dotted red; 
            background-color:white;">'.
               $showSQL.
               $o.'
          </div>';     
}
/******************************************/
function elistMembers ($members, $header, $elements, $table=true) {
/******************************************/

     if (!$members) {return null;}
     $lH = '<h4><u>'.$header.'</u>: '.count($members) . '</h4>' . PHP_EOL;
     if ($table) {
          foreach ($members as $mACBL => $m) {$o .= lMrow($m, $elements);}
          return 
               $lH.'
               <table>'.PHP_EOL.
                    lMhead($members[0], $elements).
                    $o.'
               </table>'.PHP_EOL;}     
     else {       
        foreach ($members as $m) {$o .=  '<p>' . $m . '</p>' . PHP_EOL;}
        return $lH.$o;}      
}
/******************************************/
function showDraftOrFinal () {
/******************************************/
global $eMail;

    if (isModeReview() || isModeTest()) {return "Working Draft";}
    else {return "Final Email";}    
}    
/******************************************/
function showSummary () {
/******************************************/
global $eMail;

    if (isModeTest()) 
    {
      return '
              <div class="listTable" style="margin-bottom: .5em; border:1px dotted red; background-color:white;">
                  <table>'.
                      tr(null, sSum_('Author:', myName(GetDB($eMail['Author']), 'Nick_Last')). 
                               sSum_('Sending By:', myName(me(), 'Nick_Last'))).PHP_EOL.
                      tr(null, sSum_('Testing At:', timeStamp()).
                               sSum_('Testing Ended:', timeStamp())).PHP_EOL.                      
                      tr(null, sSum_('Emails Q\'d:', showEmailQd()).
                               sSum_('Emails Sent:', "0")).PHP_EOL.
                      tr(null, sSum_('CC Emails Q\'d'.fn_().':', count($eMail['Send']['CC'])).
                               sSum_('Other Emails Q\'d'.fn_().':', count($eMail['Send']['Others']))).PHP_EOL.'
                  </table>'.
                  fn('*Included in above totals').'
              </div>';        
    }
    if (isModeReview()) 
    {
      return '
              <div class="listTable" style="margin-bottom: .5em; border:1px dotted red; background-color:white;">
                  <table>'.
                      tr(null, sSum_('Author:', myName(GetDB($eMail['Author']), 'Nick_Last')). 
                               sSum_('Sent By:', myName(me(), 'Nick_Last'))).PHP_EOL.
                      tr(null, sSum_('Review Copies:', count($eMail['Sent']['Success'])).
                               sSum_('Total Copies Sent'.fn_('*', 'red').':', $eMail['Copies'])).PHP_EOL.'
                  </table>'.
                  fn('*Cumulative for this draft', 'red').'
              </div>';
    }
    elseif (isModeSend())
    {           
      return '
              <div class="listTable" style="margin-bottom: .5em; border:1px dotted red; background-color:white;">
                  <table>'.
                      tr(null, sSum_('Author:', myName(GetDB($eMail['Author']), 'Nick_Last')). 
                               sSum_('Sent By:', myName(me(), 'Nick_Last'))).PHP_EOL.
                      tr(null, sSum_('Sent At:', $eMail['DateSent']).
                               sSum_('Finished At:', $eMail['DoneAt'])).PHP_EOL.                      
                      tr(null, sSum_('Emails Q\'d:', $eMail['TotalQ']).
                               sSum_('Emails Sent:', $eMail['TotalSent'])).PHP_EOL.
                      tr(null, sSum_('CC Emails'.fn_('*', 'red').':', count($eMail['Send']['CC'])).
                               sSum_('Other Emails'.fn_('*', 'red').':', count($eMail['Send']['Others']))).PHP_EOL.'
                  </table>'.
                  fn('*Included in above totals', 'red').'
              </div>';
    }                  
} 
/******************************************/
function sSum_ ($t, $s ) {
/******************************************/
global $eMail;

    return td("td_right", $t).td(null, '<span class="bold">'.$s.'</span>');
}
/******************************************/
function fn ($text, $c=null) {
/******************************************/

    $color = $c ? $c : 'black';
    return '<span style="margin-top: .5em; margin-bottom: .5em; 
            font-size: .75em; color: '.$color.';">'.$text.'</span>';
}
/******************************************/
function fn_ ($text=null, $c=null) {
/******************************************/

    $t = $text ? $text : '*';
    $color = $c ? $c : 'black';
    return '<span style="font-size: .75em; color: '.$color.'; vertical-align: super;">'.$t.'</span>';
}
?>
