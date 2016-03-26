<?php
require_once "mysqlw.php";

class SqlIO
{
    public function createEntry($entry)
    {
        Mysqlw::instance()->wqueryE("INSERT INTO entries
            (
                cumulative_password_hash,
                website_name_hash,
                min_length,
                max_length,
                avoid_dictionary_attacks
            ) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE
            cumulative_password_hash=?,
            website_name_hash=?,
            min_length=?,
            max_length=?,
            avoid_dictionary_attacks=?",
            array
            (
            $entry->cumulative_password_hash,
            $entry->website_name_hash,
            $entry->min_length,
            $entry->max_length,
            $entry->avoid_dictionary_attacks,
            $entry->cumulative_password_hash,
            $entry->website_name_hash,
            $entry->min_length,
            $entry->max_length,
            $entry->avoid_dictionary_attacks
            ),
            "failed executing creation SQL");
    }
    public function retrieveEntry($passHash, $entrySiteHash)
    {
        $obj = Mysqlw::instance()->wqueryE("SELECT * from entries
            WHERE cumulative_password_hash=? and website_name_hash=?", array($passHash, $entrySiteHash),
            "unable to check if entry exists")->fetch_object();
        if(Mysqlw::instance()->affected_rows==0)
            throw new Exception("Entry not found");
        else
            return $obj;
    }
    public function doesEntryExist($passHash, $entrySiteHash)
    {
        Mysqlw::instance()->wqueryE("SELECT cumulative_password_hash from entries
            WHERE cumulative_password_hash=? and website_name_hash=?", array($passHash, $entrySiteHash),
            "unable to check if entry exists");
        return Mysqlw::instance()->affected_rows!=0;
    }
}
?>
