<?php
//Mirrors the SQL result. Should be interchangable to avoid an unnecessary constructor.
//Really only used for creating a new entry.
class Entry
{
    public $cumulative_password_hash = null;
    public $website_name_hash = null;
    public $min_length = null;
    public $max_length = null;
    public $avoid_dictionary_attacks = null;
    public $timestamp = null;
}
?>
