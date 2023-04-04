<?php
/////////////////////////Documentation Module//////////////////////////
/**************************************************
Externalize the interface for specific board members; initially the President & Secretary
This app generates and sends HTML eMails using a Mandrill API interface
/**************************************************
    
    ===================================================          
    Architecture & File Stucture:
    ===================================================
    Sent files are templates for future emails
    Draft created from Sent file gets a new id: current date+old name
    New drafts use a  blank template
    If an author attempts to create a new draft from a Sent file where a draft already exists,
        the author may use existing draft or give the draft a different name
    There are three modes: Test, Review and Send
    Each mode has a workflow with required steps:
        -Select Draft
        -Generate List of Recipients/Reviewers
        -Preview Draft
        -Verify (via an email with code sent to author)
        -Confirm
        -Send
        -Summary Report 
    In Review and Test mode the verification email is replaced with a message to the author containing the code and 
        the Confirm step is skipped.
    All actions save the current selection(s) and draft except Quit, which allows the user to leave without saving the 
        latest changes.
    Std buttons (blue) indicate an action required for the next stage.
    Red buttons typically lead to ending current work but save the users work (discarded emails may be recovered by SysAdm).
    Yellow buttons backup the workflow.
    Green buttons indicate a sending or reporting action.
    Gray/White buttons indicate an option within the current step, 
        e.g., Save, remain at current stage to allow for refinement/changes
    Mode buttons take priority over Std buttons (see Global definitions)
    
    If an Action needs a dialog, the Action must test an eMail attribute and return to Main 
        before executing more funtionality
    
    CKEditor: Modified styles.js and contents.css to add News p styles & others
    To add a style: Update styles.js & contents.css
    ===================================================          
    Bugs:
    =================================================== 
    Status does not correctly move from draft on Restart or even at the beginning.
     
                
    ===================================================          
    TODO:
    ===================================================
    Clean up Keep, Mode, take Action, eMail Stage
    Check for what combination of Type, Author and Sender    
    
    Expand e_styles css for sent email (eMail.css is subset)    
    Add styles to Preview and sent email that ckeditor inserts 
      
                     
    ===================================================          
    Production v1.0:
    ===================================================
    Deactivate authorizedRecipients?
    Decide on this limitation: 
        Non-System User limited to two messages per n days 
        and not more than 1 per day
    sql in log    
   
    ===================================================          
    v2.0+
    ===================================================
    News
    Attachments
    Sectionals
    Excludes (see code in sendList) 
    Rename Draft (?)   
    Insert images
    Letterhead
    Upgrade Restart
    Manage Drafts & Discards (SysAdm & Adm) 
    InfoCheck to Greeting [Member] (for sectionals)
    Selection by Units 
    
    Catch and add "bounced" to Summary; send notice to SysAdm
    Implement Try-Catch-Finally for sendHTML
    *Update auth menu to "exclude" menu items when e.g. SECRETARY is logged in but has a higher 
     role in system
    Auto save
    Highlight unsaved changes on Quit
    Show/Clean up Discarded drafts    
    Externalize Authorized User List, e.g. when Unit officers change
    Fix MPsMax/Min to work when year turns over but 
        MPs haven't been initialized for new year but its January
    Detect user simultaneous conflicts (give warning msg)            
    ===================================================          
    Operational Considerations
    ===================================================
    Cannot create a second email draft with same id
    Cannot send two emails in same day nor more than two in three days (Production)
    Nothing gets sent unless You (just to You) or List is selected
    Only drafts by Author and Unit BOD when Author is SysAdm are available as a template
    Ctrl-z does not work if you press a button and then use Ctrl-z in Edit; use Undo button        
    ===================================================          
    Daily Task List
    ===================================================
    2023-04-01  [2 days] Fixing Keep (Issue: Stage 21 non-existant!!! [6hr]
                Bug Fixed: In Review Mode, selecting Change after getting a Summary restarts;
                Apostrophe in Subject
    2023-03-31  Extend Global for Mode altStage for "current stage", e.g. Keep
    2023-03-30  Bug in set Status for SQL when in Test mode [2hr] 
    2023-03-29  Continuing with Change b/c Status reverts to default; 
    2023-03-29  Fixing Change in Review mode [2hr; subtotal: 49] 
    2023-03-28  Fixing New if invoked immediately after loading a Sent draft [5hr; 47]  
    2023-03-27  Research attachments; fix altStage to integrate with new Mode [6hr] 
    2023-03-26  Added Review mode via Global rather than "reviewStage"; 
                fixed New FileName issue with illegal characters [4hr]  
    2023-03-24  [2 days] Generalize Mode (modify Glodal definitions) and test; [7hr]
                move altStage to initialize_eMail  
    2023-03-23  Fix error msgs for Special Selectors; ensure Mode can't change after Preview [3hr]
    2023-03-22  Fix Signature for various combinations of From and Author [4hr]
    2023-03-21  Fix bugs and general clean-up [2hr]
    2023-03-20  Fix Summary & action info, "i", for "Test Mode" [2hr]  
    2023-03-19  [3 days] v0.8.1 Generalize Review; fix Summary report for Review;  [14hr]
    2023-03-14  Eliminate duplicate addresses; Add count for extra emails (to System, Author and CC)
                fix Change for Review [3hr: 45 subtotal]
    2023-03-13  v0.7 If Review, bypass Verification email [5hr]     
    2023-03-04  [5 days] Working on  Review implementation; send 1st bulk email; 
                add method for deleting a Game specification [18hr]
    2023-03-04  [2 days] Add option for "Review" copy to the Board [7+hr]
    2023-03-03  Bug in Remove CC [4h]
    2023-03-02  Trying to make header() work in SCBCmailer [5hr]
    2023-03-01  Copying old emails [3hr]
    2023-02-28  Testing sectional email and pasting from outlook and gmail [2hr: 54hr; subtotal: 133] 
    2023-02-27  Add Signature SCBC Members; further testing on config [6hr]
    2023-02-19 thru -26 Working on CKEditor config and testing [33hr]
    2023-02-18: Quit process FINALLY complete (over 30 test iterations). 
                Clean New Id and makeClean [4hr]
    2023-02-17: Quit process incomplete. [4hr]
    2023-02-16: Get Verification doesn't save; Quit with save or no save [7hr]
    2023-02-15: Restricted list of drafts by Author; [7hr; HTD: 79]
                If there's changes, Quit asks before not saving (Not working for some inputs) 
    2023-02-14: Quit & Save [2hr]
    2023-02-13: Final on Preview after Generate; Version before Actions; Beta v01 TURN OVER [6hr]
                    Fix bug in New Draft 
    2023-02-12: Authority issue b/c doc; Add content=; Only Drafts by Author [3+hr]
    2023-02-11: Beta Library failing to open [6hr]
    2023-02-10: Add title to buttons; check for FromeMail; prep Beta v0.1 [6+hr]
    2023-02-09: Implement 3 day range for duplicate drafts, added readyStage [4+hr]
    2023-02-08: Fixed duplicate draft problem [2+hr]
    2023-02-07: Fixing duplicate entry [6+hr]
    2023-02-06: Renamed CC_ACBLs and result, bug in Change, fixing Game, rearrange Selection panel [9hr]
                duplicate entry allowed
    2023-02-05: Added library; formatted legends [7+hr]
    2023-02-04: Fixing stage 4; Associate Status only (fixed) [3+hr]
    2023-02-03: Working on Re-Verification bug; Restart added; Subject and Body checks [7+hr]
    2023-02-02: Signature and FromeMail fixed. Testing single ACBL. Re-Verification bug [7hr]
    2023-02-01: Fixing Signature [4+hr]           
                Interrupted by GD migration
    2023-01-28: Verification bug and sending to "You" bugs, CC Summary; started on addCSS [3hr; 55h since 1/15]
    2023-01-27: Contnuing SQL bug..fixed. Verification bug [5hr]
    2023-01-26: Working on SQL bug (invalid characters in Body) [2hr]
    2023-01-24: Working on Verify bug; [5hr]
    2023-01-23: Fixed CheckCC and showCC; added focus to verify [3hr]
    2023-01-22: Working on Summary; added Signature to email; CheckCC [5hr]
    2023-01-21: Implemented Done bug fix; implemented tracking; replaced get eMail. [7hr]
    2023-01-20: Continuing work on Done; identified Done fix. [3hr]
    2023-01-19: Complete Confirm and Send; Working on Done Crash![6+hr]    
    2023-01-18: Add Change; working on Confirm and Send [5hr]
    2023-01-17: Continuing showSelection (completed) [4hr]
    2023-01-16: Continuing Verify, fixing button, adding show-Selection [7+hr] 
    2023-01-15: Continuing Verify, working on Status and TotalMPs bugs introduced by Verify and fixed Discard Draft [5+hr; Total TD: 50hr]
    2023-01-14: Working on Verify [2hr]
    2023-01-13: Starting on Verify [1hr]
    2023-01-12: Completed Preview [2hr]
    2023-01-11: Starting on Preview [1hr]
    2023-01-10: Continuing on Elements [1+hr]
    2023-01-09: Working on Elements [3+hr]
    2023-01-08: Working on CC [7hr]
    2023-01-04: Working on CC [10hr]
    2023-01-03: Working on eMail components, To, Greeting, etc [7hr]
    2023-01-02: Fixed Stage. Started work on eMail components, To, Greeting, etc [6hr]
    2023-01-01: Fixed Save, eMail Body, Time (PST) bugs; Status, Stage still problems [5hr];
    2022-12-31: Bug in Save; [2hr]
    2022-12-30: Working on new structure; getting draft to work; selections to work [9hr]
    2022-12-29: Decided to switch to SQL-based drafts, so all components of a draft are in one place.[2hr]
    2022-12-28: Fixed revert to Ignore or null. Discard dialogue [3hr]
    2022-12-27: Fixed existing draft; implemented showDraft-sent eMail; handle revert to Ignore [5hr]
    Covid
    2022-12-24: Fixed existing draft issue; added setStage [2hr]
    2022-12-23: Testing; Using existing draft works with Status updated; Existing draft broken again! [8hr]
    2022-12-22: Added "Sent" (instead of "Final" to previously file copies; fixed functions; bug in Status [6hr]
    2022-12-21: Added functions makeDateId();  makeFileLocation() [9hr]
                From, Name, address on a new draft- FIXED by reinitializing blank draft; 
                Existing draft from Sent is overwritten - Fixed
                Status is incorrect (DB is (A,S) but form is (A) - Fixed
    2022-12-20: Testing; add New (Quit+Select) and button group; style ::placeholder [6hr]
    2022-12-19: Changed design for new drafts; fixing List, duplicate name, misc. [24hr]
    2022-12-15: Status fixed; New version name and conflict resolution not working;
                draft file name incorrect, using sent name
    2022-12-15: New version (bug in get_DB); New version not yet tested. Status not working [9hr]
                Also broke List
    2022-12-14: Select File method improved (new day doesn't automatically create another version)[6hr]
    2022-12-13: Draft and eMail saved correctly
    2022-12-11: SendingTo 
    2022-12-07: Selection/List
    2022-11-24: Add ckeditor
    2022-11-15: Separate into two code bases
    2022-11-11: Update common code 
    2015-04-03: Initial design & test
  

*/

?>
