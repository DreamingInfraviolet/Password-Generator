<?php
require_once "commonlogic.php";
require_once "sqlio.php";

try
{
    $expectedInputs = array("username", "site", "flower1", "flower2", "flower1c", "flower2c");

    foreach($expectedInputs as $input)
        if(!isset($_POST[$input]))
            throw new Exception("Missing input $input");

    if(isset($_POST["site"]))
        $_POST["site"] = str_replace(' ', '', strtolower(trim($_POST["site"])));
    if(isset($_POST["flower1"]))
        $_POST["flower1"] = trim($_POST["flower1"]);
    if(isset($_POST["flower2"]))
        $_POST["flower2"] = trim($_POST["flower2"]);
    if(isset($_POST["flower1c"]))
        $_POST["flower1c"] = trim($_POST["flower1c"]);
    if(isset($_POST["flower2c"]))
        $_POST["flower2c"] = trim($_POST["flower2c"]);
    if(isset($_POST["minlen"]))
        $_POST["minlen"] = empty($_POST["minlen"]) ? 6:intval($_POST["minlen"]);
    if(isset($_POST["maxlen"]))
        $_POST["maxlen"] = empty($_POST["maxlen"]) ? 20:intval($_POST["maxlen"]);

    $msg="";
    if(!preg_match("/[a-zA-Z\.\-_]+/", $_POST['username']))
        $msg.="Invalid username. ";
    if(!preg_match("/[a-zA-Z\.\-_]+/", $_POST['site']))
        $msg.="Invalid site id. ";
    if(strlen($_POST['flower1'])<6)
        $msg.="Password 1 too short. ";
    if(strlen($_POST['flower1'])>50)
        $msg.="Password 1 too long. ";
    if(strlen($_POST['flower2'])<6)
        $msg.="Password 2 too short. ";
    if(strlen($_POST['flower2'])>50)
        $msg.="Password 2 too long. ";
    if($_POST['flower1']==$_POST['flower2'])
        $msg.="Password 1 and 2 can not be the same. ";
    if($_POST['flower1c']!=$_POST['flower1'])
        $msg.="Password 1 confirmation does not match. ";
    if($_POST['flower2c']!=$_POST['flower2'])
        $msg.="Password 2 confirmation does not match. ";
    if($_POST['maxlen']-$_POST['minlen']<1)
        echo "Warning: Password range too small, may not be able to satisfy.";
    if($msg!=null)
        throw new Exception($msg);

    $idHash = md5($_POST['site']);
    $passHash = getPassHash($_POST['flower1'], $_POST['flower2']);
    $avoidDictionaryAttacks = (!empty($_POST['avoiddict']) && $_POST['avoiddict'] != "false") ? 1:null;
    $maxlen = $_POST['maxlen'];
    $minlen = $_POST['minlen'];
    
    $sqlio = new SqlIO();
    $sqlio->createEntry($_POST['username'], $_POST['site'], $minlen, $maxlen, $avoidDictionaryAttacks);

    $data = generatePassword($idHash, $passHash, $minlen, $maxlen, $avoidDictionaryAttacks);
    return print json_encode(array("status" => "success", "data" => $data));
}
catch(Exception $e)
{
    return print json_encode(array("status" => "failure", "data" => $e->getMessage()));
}