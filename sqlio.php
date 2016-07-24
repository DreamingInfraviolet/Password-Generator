<?php
require_once "mysqlw.php";

class SqlIO
{
    private static function hashText($text)
    {
        for($i = 0; $i < 20000; ++$i)
            $text = md5(hash("sha256", $text.$i));
        return $text;
    }

    public function createEntry($username, $site, $min_length, $max_length, $avoid_dictionary_attacks)
    {
        $username = $this->hashText($username);
        $site = $this->hashText($site);

        Mysqlw::instance()->wqueryE("DELETE FROM entries WHERE username_hash=? and website_hash=?",
            array($username, $site), "Unable to clean previous entry");

        Mysqlw::instance()->wqueryE("INSERT INTO entries
            (
                username_hash, website_hash, min_length, max_length, avoid_dictionary_attacks
            ) VALUES (?,?,?,?,?)",
            array($username, $site, $min_length, $max_length, $avoid_dictionary_attacks),
            "failed executing creation SQL");
    }

    public function retrieveEntry($username, $site)
    {
        $username = $this->hashText($username);
        $site = $this->hashText($site);

        $obj = Mysqlw::instance()->wqueryE("SELECT * from entries WHERE username_hash=? and website_hash=?",
            array($username, $site), "unable to check if entry exists");

        if(Mysqlw::instance()->affected_rows==0)
            throw new Exception("Entry not found");
        else
            return $obj->fetch_object();
    }

    public function doesEntryExist($username, $site)
    {
        try
        {
            $this->retrieveEntry($username, $site);
            return true;
        }
        catch(Exception $e) { return false; }
    }

    public function logSuccessRetrieval($entryId)
    {
        Mysqlw::instance()->wqueryE("INSERT INTO `successful_requests` (`entryId`, `ip`) VALUES(?, ?)", array($entryId, $_SERVER['REMOTE_ADDR']));
    }

    public function logFailedRetrieval()
    {
        Mysqlw::instance()->wqueryE("INSERT INTO `failed_requests` (`ip`) VALUES(?)", array($_SERVER['REMOTE_ADDR']));
    }

    public function shouldBeBlocked()
    {
        //Select all failed requests that happened in the last n seconds. If it exists, block request.
        $res = Mysqlw::instance()->wqueryE("SELECT * FROM `failed_requests` WHERE `ip`=?
                    AND TIMESTAMPDIFF(SECOND, `timestamp`, CURRENT_TIMESTAMP) < 1",
                array($_SERVER['REMOTE_ADDR']));
        if(Mysqlw::instance()->affected_rows == 0)
            return false;
        else
            return true;
    }
}
?>
