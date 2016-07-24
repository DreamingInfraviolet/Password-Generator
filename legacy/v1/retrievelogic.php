<?php
require_once "commonlogic.php";

function verifyInput()
{
    return true;
}

function generate($sqlio)
{
    $idHash = getIdHash($_POST['siteid']);
    $passHash1 = getPassHash($_POST['password1'], $_POST['password2']);
    $entry = null;
    if(doesEntryExistPermute($sqlio, $idHash, $_POST['password1'], $_POST['password2']))
        $entry = $sqlio->retrieveEntry($passHash1, $idHash);
    else
        return "Entry Does Not Exist";

    return generatePassword($entry->website_name_hash, $entry->cumulative_password_hash,
        $entry->min_length, $entry->max_length, $entry->avoid_dictionary_attacks!=null);
}
