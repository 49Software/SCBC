<?php
/******************************************/
function getFormBase () {
/******************************************/
global $eMail;        

    return '
        <input name="Stage" type="hidden" value="'.nextStage().'">                   
        <input name="Id" type="hidden" value="'.$eMail['Id'].'">
        <input name="Type" type="hidden" value="'.$eMail['Type'].'">
        <input name="Author" type="hidden" value="'.$eMail['Author'].'">                   
        <input name="Mode" type="hidden" value="'.$eMail['Mode'].'">
        <input name="PriorStage" type="hidden" value="'.priorStage('4Form').'">'.PHP_EOL;
}
/******************************************/
function getForm () {
/******************************************/
global $eMail;        
            
    return
        genCCForms().'                    
        <div id="directions">
          <form name="sendeMails" '.setContent('formAction').' method="POST">'.PHP_EOL.
                getFormBase().
                getSections().'               
          </form>' . PHP_EOL . '
        </div>
        <div style="clear:both;"></div>'.
        showLists(); 
}
/******************************************/
function getSections () {
/******************************************/
global $eMail;        

    foreach ($eMail['Sections'] AS $s) {        
        $Sections .= thisSection($s);}
        
    return $Sections;
}
/******************************************/
function thisSection ($s) {
/******************************************/
global $eMail;        

$Section = array(
    'File' => '
             <fieldset><legend class="action">'.showActionInfo().'</legend>
              
              <div style="float: left;">' .
                get_eMailFile(SENT).
                get_eMailFile(DRAFT).
                get_eMailFile(NEW_DRAFT).
                showActions('select').PHP_EOL.'
              </div>         
             
             </fieldset>'.PHP_EOL,         
    
    'Verify' => '
            <div style="float: left; width: 40%;">             
             <fieldset><legend class="action">'.showActionInfo().'</legend>
              
              <div class="softBox" style="float: left;">
                <p>
                    A email has been sent to the your address containing the 6-digit Verification Code.
                    To continue to Confirm, enter the code below.
                </p>
                <p>
                    You can request a new Verification Code by clicking the "Get Verification Code" 
                    button in the right-hand panel.
                </p>
              </div>                     
              
              <div style="float: left;">'.
                showActions('select').PHP_EOL.'
              </div>         
             
             </fieldset>         
            </div>'.PHP_EOL,    

    'Confirm' => '
            <div style="width: 40%; margin: auto;">             
             <fieldset><legend class="action">'.showActionInfo().'</legend>
              
              <div class="softBox" style="float: left;">
                <p>
                    Please thoroughly review your selections, the recipients, and the text of 
                    your email.
                </p>'.
                showSectionMsg('Confirm').'
              </div>                     
             
             </fieldset>         
            </div>'.PHP_EOL.'
            <div style="margin-bottom: 1em;">&nbsp;</div>
            <div style="clear:both;"></div>
            
            <div style="float: left;">                   
               <fieldset><legend class="action">Review Selections: <span style="color: black">'.
                showRecipientCount().' Recipients</span></legend> 
                    
                    <div style="float: left; padding: .25em;">'.PHP_EOL.'
                      <div>'.
                          showSelection().'                      
                      </div>
                    </div>'.PHP_EOL.'     
               
               </fieldset>
            </div>'.PHP_EOL,

    'ReVerify' => '
            <div style="float: left; width: 40%;">             
             <fieldset><legend class="action">'.showActionInfo().'</legend>
              
              <div class="softBox" style="float: left;">
                <p>
                    You can try the 6-digit Verification Code again from the email sent to you. 
                </p>
                <p>
                    Or, you can request another Verification Code by clicking the "Request Another Verification Code" 
                    button in the right-hand panel beneath the preview.
                </p>
              </div>                     
              
              <div style="float: left;">'.
                showActions('select').PHP_EOL.'
              </div>         
             
             </fieldset>         
            </div>'.PHP_EOL,    

    'PickDraft' => '
            <input name="Draft_Id" type="hidden" value="'.$eMail['Draft_Id'].'">
            <div style="float: left; width: 40%;">             
             <fieldset><legend class="action">'.showActionInfo().'</legend>
              
              <div class="softBox" style="float: left;">
                <p>There is an <b>existing draft</b> created for the previously sent eMail you selected.
                   You cannot have two drafts with the same name within '.DAYS.' of each other; select an option from below to 
                   resolve the issue.</p>
                <p>The <b>Sent</b> email is shown on the right for your reference.</p>
              </div>                     
              
              <div style="float: left;">'.
                get_PickDraft().
                showActions('select').PHP_EOL.'
              </div>         
             
             </fieldset>         
            </div>'.PHP_EOL,    

    'ConfirmDiscard' => '
            <div style="float: left; width: 40%;">             
             <fieldset><legend class="action">'.showActionInfo().'</legend>
              
              <div class="softBox" style="float: left;">
                <p>Do you really want to <b>discard this changed draft?</b></p>
                <p>The draft is shown on the right for your reference.</p>
              </div>                     
              
              <div style="float: left;">'.                
                showActions('select').PHP_EOL.'
              </div>         
             
             </fieldset>         
            </div>'.PHP_EOL,    

    'ConfirmQuit' => '
            <div style="float: left; width: 40%;">             
             <fieldset><legend class="action">'.showActionInfo().'</legend>
              
              <div class="softBox" style="float: left;">'.
                showConfirmQuit().'                
                <p>The current draft shown on the right for your reference.</p>
              </div>                     
              
              <div style="float: left;">'.                
                showActions('select').PHP_EOL.'
              </div>         
             
             </fieldset>         
            </div>'.PHP_EOL,    

    'Selection' => '
            <div style="float: left;">                   
               <fieldset><legend class="action">'.showActionInfo().'</span></legend> 
                    
                    <div style="float: left; padding: .25em;">'.PHP_EOL.'
                      <div>'.                          
                          get_selection().'                                 
                      </div>
                      <div>'.                                 
                          showActions('select').PHP_EOL.'                                
                      </div>                                                                                     
                    </div>'.PHP_EOL.'     
               
               </fieldset>
            </div>'.PHP_EOL,
            
    'showSelection' => '
            <div style="float: left;">                   
               <fieldset><legend class="action">'.showActionInfo().'</span></legend> 
                    
                    <div style="float: left; padding: .25em;">'.PHP_EOL.'
                      <div>'.
                          showSelection().'                      
                      </div>
                      <div>'.                                 
                          showActions('select').PHP_EOL.'                                
                      </div>                                                                                     
                    </div>'.PHP_EOL.'     
               
               </fieldset>
            </div>'.PHP_EOL,
            
    'Edit' => '
            <div style="float: left;">
               <fieldset><legend class="action">Edit eMail: <span class="black">'.$eMail['Id'].'</span></legend>
                    
                    <div style="float: left; padding: .25em;">'.PHP_EOL.'
                      <div>'.
                          edit_eMail().'
                      </div>
                      <div>'.                                 
                          showActions('send').PHP_EOL.
                          showActions('edit').PHP_EOL.
                          setContent('showVersion').'                                
                      </div>                                                                                     
                    </div>
               
               </fieldset>
            </div>'.PHP_EOL,    
                    
    'Preview' => '
            <div style="float: left;">
               <fieldset><legend class="action">'.showDraftOrFinal().': <span class="black">'.$eMail['Id'].'</span></legend>
                    
                    <div style="float: left; padding: .25em;">'.PHP_EOL.'
                      <div>'.
                          showPreview().'
                      </div>
                      <div>'.                                 
                          showActions('send').PHP_EOL.
                          showActions('edit').PHP_EOL.
                          showActions('review').PHP_EOL.
                          setContent('showVersion').'                                   
                      </div>                                                                                     
                    </div>
               
               </fieldset>
            </div>'.PHP_EOL,

    'Summary' => '
            <div style="float: left; width: 75%; margin-bottom: 10px;">             
             <fieldset><legend class="action">'.showMode_().' Summary</legend>
              
              <div class="softBox" style="float: left;">'.
                    showSummary().'
              </div>                     
              
             </fieldset>         
            </div>
            <div style="clear:both;"></div>'.PHP_EOL);

    return $Section[$s];                
}
?>
