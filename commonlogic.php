<?php

$specialCharactersAllowed = array("#", "!", "@", "_", "?");
$letters =array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o",
    "p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I",
    "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
$numbers = array("0","1","2","3","4","5","6","7","8","9");
$randTable = array_merge($letters, $numbers, $specialCharactersAllowed);

$specialCharactersAllowedRegex="[";
foreach($specialCharactersAllowed as $c)
    $specialCharactersAllowedRegex.=$c;
$specialCharactersAllowedRegex.="]";

function normaliseInput()
{
    if(isset($_POST["siteid"]))
        $_POST["siteid"] = strtolower(trim($_POST["siteid"]));
    if(isset($_POST["siteic"]))
        $_POST["siteic"] = strtolower(trim($_POST["siteic"]));
    if(isset($_POST["password1"]))
        $_POST["password1"] = trim($_POST["password1"]);
    if(isset($_POST["password2"]))
        $_POST["password2"] = trim($_POST["password2"]);
    if(isset($_POST["password1c"]))
        $_POST["password1c"] = trim($_POST["password1c"]);
    if(isset($_POST["password2c"]))
        $_POST["password2c"] = trim($_POST["password2c"]);
    if(isset($_POST["minlen"]))
        $_POST["minlen"] = intval($_POST["minlen"]);
    if(isset($_POST["maxlen"]))
        $_POST["maxlen"] = intval($_POST["maxlen"]);
}

function isPassValid($pass, $minLength, $maxLength)
{
    global $specialCharactersAllowedRegex;
    $valid = true;
    $valid &= $pass >= $minLength and $pass <= $maxLength;
    $valid &= preg_match_all("/".$specialCharactersAllowedRegex."/", $pass)>0;
    $valid &= preg_match_all("/[a-z]/", $pass)>0;
    $valid &= preg_match_all("/[A-Z]/", $pass)>0;
    $valid &= preg_match_all("/[0-9]/", $pass)>0;
    return $valid;
}

function getIdHash($id)
{
    return md5($id);
}

function getPassHash($pass1, $pass2)
{
    return md5($pass1 . md5($pass2));
}

function doesEntryExistPermute($sqlio, $idhash, $pass1, $pass2)
{
    return $sqlio->doesEntryExist(getPassHash($pass1, $pass2), $idhash)
        || $sqlio->doesEntryExist(getPassHash($pass2, $pass1), $idhash);
}

function loadWords()
{
    return file("dictionary.txt");
}

function capitaliseRandom($pass)
{
    $strout="";
    $strlen = strlen($pass);
    for( $i = 0; $i < $strlen; $i++)
    {
        $char = $pass[$i];
        //Skip characters that are difficult to read if capitalised:
        if($char=="i")
            $strout.=$char;
        else
            $strout.= mt_rand(0,100) < 20 ? strtoupper($char):$char;
    }
    return $strout;
}

function generatePassword($idHash, $passHash, $minLength, $maxLength, $noDict)
{
    global $specialCharactersAllowed, $letters, $numbers, $randTable;

    $h = md5($idHash . $passHash);
    $seed = intval(substr($h, 0, 8), 16) ^
            intval(substr($h, 8, 8), 16) ^
            intval(substr($h, 16, 8), 16) ^
            intval(substr($h, 24, 8), 16);
    mt_srand($seed);

    $words = null;

    if($noDict)
        $words = $randTable;
    else
        $words = loadWords();

    $wordLen = count($words);
    $pass=null;
    do
    {
        $pass="";
        $previousPass="";
        $retries=0;
        $longestPass = $pass;

        while(true)
        {
            $typeToInsert = mt_rand(0,20);
            $previousPass = $pass;

            //Determine what we should insert.
            if($typeToInsert<3)
                $pass .= $numbers[mt_rand()%count($numbers)];
            else if($typeToInsert<5)
                $pass .= $specialCharactersAllowed[mt_rand()%count($specialCharactersAllowed)];
            else
                $pass .= trim($words[mt_rand()%count($words)]);

            //If the word is bigger than it should be, try again!
            if(strlen($pass)>$maxLength)
            {
                $pass = $previousPass;
                ++$retries;
            }
            //If we found a bigger password on the last iteration, update it.
            else if(strlen($pass)>strlen($longestPass))
                $longestPass = $pass;

            //If we had too many retries, quit! The current password should be good enough.
            if($retries>20)
                break;
        }

        //Add random capitalisations
        $pass = capitaliseRandom($pass);

        //Only quit if this password satisfies important properties. Otherwise retry.
    } while(!isPassValid($pass, $minLength, $maxLength));

    return $pass;
}

?>
