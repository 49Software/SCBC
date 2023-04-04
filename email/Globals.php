<?php
DEFINE ('CURRENT_VERSION',    'Beta v0.8.6.2');
DEFINE ('OLD',                '180');
DEFINE ('DUPLICATE_DAYS',     '3');
DEFINE ('CURRENT_STAGE',      '*');
DEFINE ('LAST_MAIN_STAGE',    '<');
DEFINE ('MAIN_STAGE_END',     '7');
DEFINE ('SENT',               'Sent');
DEFINE ('DRAFT',              'Draft');
DEFINE ('NEW_',               'New');        
DEFINE ('TEMP',               'temp');
DEFINE ('NEW_DRAFT',          '2022-11-11 Draft v1.0 (Blank)');
DEFINE ('REVIEW',             '**Please Review**: ');
DEFINE ('IGNORED',            '-');
DEFINE ('FUTURE',             'Future Version');

$Actions = array(
  'Select'=>array('type'=>'stdButton',        'text'=>'Select a Sent/Draft eMail'),
  
  'List'=>array('type'=>'stdButton',          'text'=>'Generate List', 
                                                    'title'=>'Create a Recipient list'),  
  'Preview'=>array('type'=>'stdButton',       'text'=>'Preview Draft',       
                                                    'title'=>'View Email'),
  'Verify'=>array('type'=>'stdButton',        'text'=>'Get Verification Code',  
                                                    'title'=>'A 6-digit Verification Code will be sent to your email'),
  'Confirm'=>array('type'=>'stdButton',       'text'=>'Confirm Final Check Complete', 
                                                    'title'=>'Check Email is ready to send'),
  'Send'=>array('type'=>'confirmButton',      'text'=>'Send eMails',
                                                    'title'=>'The system will send eMails; this operation can not be stopped.'),  
  'Done'=>array('type'=>'confirmButton',       'text'=>'Summary Report', 
                                                    'title'=>'Send statistics'),
  'Pick'=>array('type'=>'stdButton',          'text'=>'Pick a Draft eMail'),
  
  'Discard'=>array('type'=>'deleteButton',    'text'=>'Discard Draft',  
                                                    'title'=>'Email will be discarded'),
  'Keep'=>array('type'=>'optionButton',       'text'=>'Keep Draft', 
                                                    'title'=>'Save draft for later changes'),  
  'Refine'=>array('type'=>'cautionButton',    'text'=>'Refine', 
                                                    'title'=>'Make additional changes to draft and selection criteria'),
  'Reset'=>array('type'=>'deleteButton',      'text'=>'Restore Selection Defaults', 
                                                    'title'=>'Erase current selections'),
  'Continue'=>array('type'=>'stdButton',      'text'=>'Continue'), 
  
  'Change'=>array('type'=>'cautionButton',    'text'=>'Change',                                                
                                                    'title'=>'Continue refining list or editing email at a previous stage'),    
  'New'=>array('type'=>'cautionButton',       'text'=>'New Draft', 
                                                    'title'=>'Start over; current Email is saved'),
  'Review'=>array('type'=>'optionButton',     'text'=>'Review Draft',   
                                                    'title'=>'Check selections and Email'),
  'Copy'=>array('type'=>'confirmButton',      'text'=>'Send Copy', 
                                                    'title'=>'Send copy out for review'),
  'Save'=>array('type'=>'optionButton',       'text'=>'Save Draft',
                                                    'title'=>'Save working draft'), 
  'CheckVC'=>array('type'=>'stdButton',       'text'=>'Enter Verification Code', 'genButton'=>'verify',                                                           
                                                    'title'=>'The Verification Code is at the bottom of the email you were sent'),
  'ReVerify'=>array('type'=>'optionButton',   'text'=>'Request Another Verification Code',  
                                                    'title'=>'A 6-digit Verification Code will be sent to your email'),

  'Restart'=>array('type'=>'cautionButton',   'text'=>'Restart',
                                                    'title'=>'Edit and refine list from beginning'),                                                  
  'Quit'=>array('type'=>'deleteButton',       'text'=>'Quit', 
                                                    'title'=>'Check for unsaved changes, then exit'),
  'Exit'=>array('type'=>'deleteButton',       'text'=>'Exit',
                                                    'title'=>'Return to home page') );


$Steps = array(
/*
    Stage is set by form and nextStage OR by Global [mode][nextStage]
    Exceptions to above handled by altStage
   altStage handles all modes
   default: nextStage;
   altStage codes: 
            "*":  Stay in current stage; 
            "<":  Return to previous "main line stage"

*/   
   0=>array('i'=>'Select An eMail Template',  'nextStage'=>1,   
      'Sections'=>array('File'),
      'buttons'=>array('select'=>array(0=>array('Select')))),          
   
   1=>array('i'=>'<span class="black">Make</span> Selections & Edit', 'nextStage'=>2,   
       'buttons'=>array(
            'select'=>array(0=>array('List')), 
            'edit'=>array(0=>array('Save', 'New'), 1=>array('Discard', 'Quit'))),             
       'altStage'=>array('Save'=>"*", 'New'=>0)),
   
   2=>array('i'=>'Check Recipients',         'nextStage'=>3,   
       'buttons'=>array(
            'select'=>array(0=>array('Refine', 'Reset')), 
            'edit'=>array(0=>array('Preview', 'Save', 'New'), 1=>array('Restart', 'Discard', 'Quit'))),
       'altStage'=>array('Refine'=>"*",'Reset'=>"1", 'Save'=>"*", 'New'=>0, 'Discard'=>0),
       'doList'=>true),         
   
   3=>array('i'=>'Refine Recipients',      'nextStage'=>4,    
       'buttons'=>array(
            'select'=>array(0=>array('Refine', 'Reset')), 
            'edit'=>array(0=>array('Review', 'Save', 'Change'), 1=>array('Restart', 'New', 'Discard', 'Quit')), 
            'send'=>array(0=>array('Verify'))),        
       'Review'=>array('nextStage'=>5),       
       'altStage'=>array('Refine'=>2,'Reset'=>1, 'Review'=>"*", 'Save'=>"*", 'Change'=>"2", 'New'=>0, 'Discard'=>0),
       'doList'=>true),
   
   4=>array('i'=>'Check Verification Email', 'nextStage'=>5,   
       'Sections'=>array('Confirm', 'Preview'),   
       'buttons'=>array( 
            'edit'=>array(0=>array('Save', 'Change'), 1=>array('Restart', 'New', 'Discard', 'Quit')), 
            'send'=>array(0=>array('Confirm'))),        
       'altStage'=>array('Save'=>"*", 'Change'=>"2", 'New'=>0, 'Discard'=>0),
       'doList'=>true),
   
   5=>array('i'=>'Confirm Sending #eMails', 'nextStage'=>6,     
       'Sections'=>array('showSelection', 'Preview'),
       'buttons'=>array( 
            'edit'=>array(0=>array('Send', 'Change'), 1=>array('Restart', 'Discard', 'Quit'))),
       'altStage'=>array('Change'=>"2", 'Discard'=>0),
       'Test'=>array('i'=>'Test Completed',
            'buttons'=>array(
                'edit'=>array(0=>array('Change'), 1=>array( 'Discard', 'Quit')))),                
       'doList'=>true),
   
   6=>array('i'=>'Sent #eMails',  'nextStage'=>7,       
       'Sections'=>array('showSelection', 'Preview'),     
       'buttons'=>array(
            'edit'=>array(0=>array('Done', 'Exit'))),       
       'Review'=>array('i'=>'Review Completed',
            'buttons'=>array(
                'edit'=>array(0=>array('Change', 'Done'), 1=>array('Quit')))),                
       'altStage'=>array('Change'=>"2", 'Restart'=>"0"),
       'doList'=>true),
   
   7=>array('i'=>'Completed Sending #eMails',   'nextStage'=>0,   
       'Sections'=>array('Summary', 'showSelection', 'Preview'),
       'buttons'=>array(
            'edit'=>array(0=>array('New', 'Exit'))),       
       'Review'=>array(
            'buttons'=>array(
                'edit'=>array(0=>array('Change', 'Restart'),)),
                'altStage'=>array('Change'=>"2", 'Restart'=>"0")),                
       'altStage'=>array('New'=>0, 'Change'=>"2", 'Restart'=>"0"),       
       'doList'=>true),

  10=>array('i'=>'Pick Template for Working Draft', 'nextStage'=>1, 
       'Sections'=>array('PickDraft', 'Preview'),
       'buttons'=>array('select'=>array(0=>array('Pick', 'Exit')))),
   
  20=>array('i'=>'Confirm Discard',            'nextStage'=>21, 
       'Sections'=>array('ConfirmDiscard', 'Preview'),
       'buttons'=>array('select'=>array(0=>array('Discard', 'Keep', 'Restart'))),
       'Review'=>array(
            'buttons'=>array(
                'edit'=>array(0=>array('Discard', 'Keep', 'Restart')))),                
       'altStage'=>array('Keep'=>"<")),
     
  22=>array('i'=>'Confirm Quit', 
       'Sections'=>array('ConfirmQuit', 'Preview'),
       'buttons'=>array('select'=>array(0=>array('Keep', 'Exit'))),
       'altStage'=>array('Keep'=>"<")),
     
  30=>array('i'=>'Review Draft & Recipients', 'nextStage'=>3,    
       'Sections'=>array('showSelection', 'Preview'),
       'buttons'=>array('edit'=>array(0=>array('Continue', 'Change', 'New'), 1=>array('Restart', 'Discard', 'Quit'))),
       'altStage'=>array('Change'=>"2", 'New'=>0, 'Restart'=>0, 'Discard'=>0),
       'doList'=>true),
  
  40=>array('i'=>'Enter Verification Code',    'nextStage'=>4,  
       'Sections'=>array('Verify', 'Preview'),
       'buttons'=>array(
          'select'=>array(0=>array('CheckVC')),
          'edit'=>array(0=>array('ReVerify', 'Change'), 1=>array('Restart', 'Discard', 'Exit'))),         
       'altStage'=>array('Change'=>2, 'Restart'=>0, 'Discard'=>0),
       'doList'=>true),
  
  41=>array('i'=>'Request New Verification Code', 'nextStage'=>4,
       'Sections'=>array('ReVerify', 'Preview'),
       'buttons'=>array(
          'select'=>array(0=>array('CheckVC')),
          'edit'=>array(0=>array('ReVerify', 'Change'), 1=>array('Restart', 'Discard', 'Quit'))),         
       'altStage'=>array('Change'=>3, 'Restart'=>0, 'Discard'=>0),
       'doList'=>true)
);
   
$eMail_Fields = array(
        'Id'=>          array('key'=>true, 'NotNull' =>true),
        'Type'=>        array('key'=>true, 'NotNull' =>true,    'default'=>'Draft'),
        'Author'=>      array('key'=>true, 'NotNull' =>true,    'default'=>'SCBC000'),
        //eMail Components
        'Subject'=>     array('edit'=>true, 'clean'=>true), 
        'Salutation'=>  array('edit'=>true, 'NotNull' =>true,   'default'=>'Std'),
        'FromACBL'=>    array('edit'=>true, 'function'=>true,   'default'=>'SCBC000'), 
        'FromName'=>    array('edit'=>true, 'function'=>true,   'default'=>'Unit 550 Board of Directors'), 
        'FromeMail'=>   array('edit'=>true, 'transform'=>true,  'default'=>'info@santacruzbridge.org'),
        'Sign'=>        array('edit'=>true,                     'default'=>'Officer'),
        'Contact'=>     array('edit'=>true, 'OnOff'=>true),
        'CCs'=>         array('edit'=>true, 'transform'=>true),
        'Body'=>        array('edit'=>true, 'clean'=>true, 'transform'=>true),
        //Program Control//
        'Stage'=>       array('pgmCtrl'=>true),
        'PriorStage'=>  array('pgmCtrl'=>true),
        'Sections'=>    array('pgmCtrl'=>true),
        'Final'=>       array('pgmCtrl'=>true),
        'Sender'=>      array('pgmCtrl'=>true),
        'ProcessError'=>array('pgmCtrl'=>true),        
        'Mode'=>        array('pgmCtrl'=>true, 'show'=>true,    'default'=>'Test'),        
        'To'=>          array('pgmCtrl'=>true),                
        'Changed'=>     array('pgmCtrl'=>true),     /*Used to indicate a normal change in eMail attributes*/
        'Changes'=>     array('pgmCtrl'=>true),     /*Used to indicate a change in eMail, but show in Pending*/
        'Pending'=>     array('pgmCtrl'=>true),     /*Used to indicate a change in eMail attributes but */ 
                                                    /*  keep revise under user control*/        
        'SQL'=>         array('pgmCtrl'=>true),
        'Send'=>        array('pgmCtrl'=>true),
        'Error'=>       array('pgmCtrl'=>true),
        //Selection//         
        'Status'=>      array('selection'=>true, 'transform'=>true, 'show'=>true, 'default'=>'(A)'),
        'Board'=>       array('selection'=>true, 'OnOff'=>true, 'show'=>true),
        'ACBL'=>        array('selection'=>true),  
        'Sectional'=>   array('selection'=>true, 'show'=>true), 
        'Game'=>        array('selection'=>true, 'transform'=>true),  
        'MPsMin'=>      array('selection'=>true), 
        'MPsMax'=>      array('selection'=>true), 
        'YTDMPs'=>      array('selection'=>true), 
        'BBO'=>         array('selection'=>true), 
        'Covid'=>       array('selection'=>true),
        //v2 Implementation//
        'Group'=>       array('selection'=>true, 'version'=>'v2'),
        'AlreadySent'=> array('selection'=>true, 'version'=>'v2'),        
        //Tracking//
        'Created'=>     array('tracking'=>true),
        'Updated'=>     array('tracking'=>true),
        'Discarded'=>   array('tracking'=>true),
        'Verify'=>      array('tracking'=>true),
        'Copies'=>      array('tracking'=>true),
        'SentBy'=>      array('tracking'=>true),
        'DateSent'=>    array('tracking'=>true),
        'DoneAt'=>      array('tracking'=>true),
        'TotalQ'=>      array('tracking'=>true),
        'TotalSent'=>   array('tracking'=>true));            

$sourceOfeMail = array(
                    SENT  => array('title'=>'Sent eMail',  'selectName'=>'sentFileName'),
                    DRAFT => array('title'=>'Draft eMail', 'selectName'=>'draftFileName'),
                    NEW_   => array('title'=>'New eMail',   'selectName'=>'newFileName') );

$To = ['Test', 'Review', 'Send'];

$Elements = array(     
    'Salutation' => array(
        'Std'=>array('Use'=>'Hey!',  'Title'=>'Hey! Bridge Player [Member],', 'default'=>true),
        'Dear'=>array('Use'=>'Dear', 'Title'=>'Dear [Member],'),
        'TOD'=>array('Use'=>'Time',  'Title'=>'Time Of Day: [Morning, Afternoon, etc.]'),
        'BBO'=>array('Use'=>'BBO',   'Title'=>'Hey! BBO Player [BBO Name],'),
        'None'=>array('Use'=>'None', 'Title'=>'[Omit Greeting]')),
    
    'List' => array(
        'Send'=>array('Recipients'  => 'Recipient List',
                      'CC'          => 'Copied (Not on List above)',
                      'Reviewers'   => 'Reviewers',
                      'Others'      => 'Others (Not on List above)'),
        
        'Error'=>array('failed'         => 'Failed emails',
                       'skipped'        => 'Skipped',
                       'Beta'           => 'Not eligible for emails during Beta',
                       'previouslySent' => 'Previously Sent',
                       'excluded'       => 'Excluded Members') ),
    
    'Sign' => array(
        'Org Title'=>array('Use'=>'Officer',    'Title'=>'BOD Officer',     'default'=>true),
        'Personal'=>array('Use'=>'Personal',    'Title'=>'Your First & Last Names'),
        'Members'=>array('Use'=>'Members',      'Title'=>'Members of the SCBC')));

$betaRecipients = array( 
        'SysAdm',
        'R107029', 
        '7067747',
        '2650045',
        '7067747',    
        '5879493',
        'R794573',
        'R092285',
        'K191539',
        'R107029',
        'P746946',
        '4628179',
        'R002766'
);
?>        
