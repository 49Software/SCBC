<?php
///////Display errors/////////
ini_set('display_errors', 1);
error_reporting(E_ERROR | E_PARSE);
///////////////////////////////
/////////////////////////Main Module//////////////////////////
if (!check_authority(setContent())) {return false;}

$eMail = $template = array();
$eMailSCBC = true; //Modify SCBC_Average behavior for eMailSCBC

if (!takeAction()) {doFile();}
else {$f = 'do'.takeAction(); $f();}

makeSendList(); 
$newContents .= getForm();                                 
?>
