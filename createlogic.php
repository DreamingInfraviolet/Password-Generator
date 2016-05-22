<?php
require_once "commonlogic.php";
require_once "entry.php";

function verifyInput()
{
    $msg="";
    if(!preg_match("/.+/", $_POST['siteid']))
        $msg.="Invalid site id.<br>";
    if($_POST['siteid']!=$_POST['siteidc'])
        $msg.="Site id confirmation does not match.<br>";
    if(strlen($_POST['password1'])<6)
        $msg.="Password 1 too short.<br>";
    if(strlen($_POST['password1'])>50)
        $msg.="Password 1 too long.<br>";
    if(strlen($_POST['password2'])<6)
        $msg.="Password 2 too short.<br>";
    if(strlen($_POST['password2'])>50)
        $msg.="Password 2 too long.<br>";
    if($_POST['password1']==$_POST['password2'])
        $msg.="Password 1 and 2 can not be the same.<br>";
    if($_POST['password1c']!=$_POST['password1'])
        $msg.="Password 1 confirmation does not match.<br>";
    if($_POST['password2c']!=$_POST['password2'])
        $msg.="Password 2 confirmation does not match.<br>";
    if($_POST['maxlen']-$_POST['minlen']<1)
        echo "Warning: Password range too small, may not be able to satisfy.";
    if($msg!=null)
    {
        echo $msg;
        return false;
    }
    else
        return true;
}

function generate($sqlio)
{
    $idHash = getIdHash($_POST['siteid']);
    $passHash = getPassHash($_POST['password1'], $_POST['password2']);

    $entry = new Entry();
    $entry->cumulative_password_hash = $passHash;
    $entry->website_name_hash = $idHash;
    $entry->min_length = empty($_POST['minlen']) ? 6: max(6, $_POST['minlen']);
    $entry->max_length = empty($_POST['maxlen']) ? 20: min(50, $_POST['maxlen']);
    $entry->avoid_dictionary_attacks = !empty($_POST['avoiddict']) ? 1:null;

    $sqlio->createEntry($entry);

    return generatePassword($entry->website_name_hash, $entry->cumulative_password_hash,
        $entry->min_length, $entry->max_length, $entry->avoid_dictionary_attacks!=null);
}
