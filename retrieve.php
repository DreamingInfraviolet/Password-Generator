<?php 

//Includes
require_once "commonlogic.php";
require_once "sqlio.php";

//SQL io helper
$sqlio = new SqlIO();

try
{
	$expectedInputs = array("username", "site", "flower1", "flower2");

    foreach($expectedInputs as $input)
        if(empty($_POST[$input]))
            throw new Exception("Missing input $input");

	//Get post variables
	$username = $_POST['username'];
	$site     = $_POST['site'];
	$flower1  = $_POST['flower1'];
	$flower2  = $_POST['flower2'];

	if($sqlio->shouldBeBlocked())
		throw new Exception("Too many requests.");

	//Get insecure password hash for generation (not stored)
	$passHash = getPassHash($flower1, $flower2);

	//Get entry from database if it exists
	$entry = null;
	if($sqlio->doesEntryExist($username, $site))
	    $entry = $sqlio->retrieveEntry($username, $site);
	else
	    throw new Exception("Wrong username or site id.");

	//All seems okay. Generate the password and return it.
	$data = generatePassword(md5($site), $passHash, $entry->min_length, $entry->max_length, $entry->avoid_dictionary_attacks!=null);
	$sqlio->logSuccessRetrieval($entry->id);
	return print json_encode(array("status" => "success", "data" => $data));
}
catch(Exception $e)
{
	$msg = $e->getMessage();
	try { $sqlio->logFailedRetrieval(); }
	catch (Exception $e2) { $msg .= " - Also, could not log failure: " . $e2->getMessage(); }
	return print json_encode(array("status" => "failure", "data" => $msg));
}

?>