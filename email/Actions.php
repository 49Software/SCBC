<?php

/******************************************/
function doFile () {
/******************************************/
global $eMail;
    
    setStage(0);
    return true;
}
/******************************************/
function doSelect () {
/******************************************/
global $eMail;
   
    checkFileInput();
    setMode();
    setStage();
    return true;
}
/******************************************/
function doList () {
/******************************************/
global $eMail;
  
    initialize_eMail();
    revise();
    saveDraft();     
    return true;
}
/******************************************/
function doPreview () {
/******************************************/
global $eMail;
    
    initialize_eMail();    
    revise();    
    saveDraft();
    setStage(30);    
    userEntry('Subject');      
    return true;       
}
/******************************************/
function doVerify () {
/******************************************/
global $eMail;
    
    initialize_eMail();
    revise();    
    saveDraft();
    setStage(40);    
    sendVerificationCode();
    return true;       
}
/******************************************/
function doConfirm () {
/******************************************/
global $eMail;
       
    initialize_eMail();
    userEntry('Subject', 'Body');
    return true;
}
/******************************************/
function doSend () {
/******************************************/
global $eMail;
       
    initialize_eMail();    
    makeSendList();
    startClock();
    doSending();
    endClock();
    doTracking();       
    return true;
}
/******************************************/
function doDone () {
/******************************************/
global $eMail;
       
    initialize_eMail();
    setFinal();
    return true;
}
/******************************************/
function doPick () {
/******************************************/
global $eMail;
    
    pickedDraft();
    logBeta();             
    return true;
}
/******************************************/
function doRefine () {
/******************************************/
global $eMail;
   
    initialize_eMail();
    revise();    
    saveDraft();
    altStage();    
    return true;
}
/******************************************/
function doContinue () {
/******************************************/
global $eMail;
                  
    initialize_eMail();
    return true;       
}
/******************************************/
function doReview () {
/******************************************/
global $eMail;
    
    initialize_eMail();
    revise();    
    saveDraft();        
    setStage(30);
    return true;       
}
/******************************************/
function doCheckVC () {
/******************************************/
global $eMail;
    
    initialize_eMail();    
    validateVC();           
    return true;       
}
/******************************************/
function doReVerify () {
/******************************************/
global $eMail;
    
    initialize_eMail();
    setStage(40);    
    sendVerificationCode();    
    return true;       
}
/******************************************/
function doChange () {
/******************************************/
global $eMail;
       
    initialize_eMail();
    resetVerify();
    altStage();
    return true;
}
/******************************************/
function doReset () {
/******************************************/
global $eMail;
    
    initialize_eMail();
    revise();
    clearSelections();
    saveDraft();    
    return true;    
}
/******************************************/
function doSave () {
/******************************************/
global $eMail; 
   
    initialize_eMail();
    revise();    
    saveDraft();
    altStage();                  
    return true;
}
/******************************************/
function doRestart () {
/******************************************/
global $eMail;
    
    initialize_eMail();
    revise();    
    saveDraft();    
    setStage(1);
    return true;    
}
/******************************************/
function doNew () {
/******************************************/
global $eMail;

    initialize_eMail();
    if ($eMail['Type'] == SENT) 
    {
        doFile(); 
        return true;    
    }
    if (unchangedDraft())
    {
        deleteDraft();
        doFile(); 
        return true;
    }
    else 
    {        
        setStage(20);
        return true;
    }      
}
/******************************************/
function doRemoveCC () {
/******************************************/
global $eMail;
             
    initialize_eMail();
    removeCC_Name();        
    revise();
    saveDraft();
    makeSendList();
    setPriorStage();    
    return true;
}
/******************************************/
function doDiscard () {
/******************************************/
global $eMail;

    initialize_eMail();
    
    if (unchangedDraft())
    {
        doFile(); 
        return true;
    }
    if (Stage(21)) 
    {       
        discardDraft(); 
        doFile(); 
        return true;
    }
    else 
    {        
        setStage(20);
        return true;
    }      
}
/******************************************/
function doKeep () {
/******************************************/
global $eMail;

    if (Stage(21)) 
    {
        initialize_eMail();
        altStage();   

        return true;       
    }
    setStage(2);
    initialize_eMail();   
    keepDraft();
    altStage();
    return true;
}
/******************************************/
function doQuit () {
/******************************************/
global $eMail;
    
    initialize_eMail();
    anyChanges();
    setStage(22);    
    return true;       
}
/******************************************/
function doExit () {
/******************************************/
global $eMail, $URL;
        
    initialize_eMail();
    forgetChanges();
    doFile();
}    
?>
