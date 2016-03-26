<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "dbinfo.php";
?>

<?php
/** @author Volodymyr Sereda
    * This class is designed to be the only entry point into the database.
    * It offers better error handling, and only allows a parameter interface.
    * The w in mysqlw stands for wrapper :)
    * It never needs to be instantiated explicitly. Just use the static instance() method. Singleton pattern is used!
    * BEWARE. Sqli is tricky, so this class may have BUGS. I found all that I noticed, but remain vigilant!
    *
    * The name of the query function has been changed from query() to wquery() for easy searching of legacy code.
    * wqueryE accepts an extra argument, which is the error message upon failure (E=exception). It throws an exception
    * upon failure and offers much better error reporting.
    * wquery should be considered legacy, and was only created for existing code. Prefer to use wqueryE where possible.
    *
    * Do NOT put SQL parameters straight into the search string! That leads to SQL injection issues. Instead, all parameters
    * must be bound programmatically. Insert ? markers into the query, and provide the parameters in the array (second parameter).
    * For example, the old query->("SELECT * from stuff WHERE a=$one and b=$two and c=$three") is equivalent to the new
    *                     wquery->("SELECT * from stuff WHERE a=? and b=? and c=?", array($one, $two, $three)).
    *
    * Example usage:
    * Original mysqli:
    * --------------------------------------------------------------------------
    * con = new mysqli(HOST, USER, PASSWORD, DATABASE);
    * if (con->connect_errno)
    *     die("merp");
    * $injection = "'hi";
    * $result = $con->query("SELECT * FROM somewhere WHERE entry=$injection");
    * if(!$result)
    *     die("derp");
    * $entry = $result->fetch_object()->entry;
    * --------------------------------------------------------------------------
    * New mysqlw:
    * --------------------------------------------------------------------------
    * $nonInjection = "'hi";
    * $entry = Mysqlw::instance()->wqueryE("SELECT * FROM somewhere WHERE entry=?",
        array($nonInjection), "herp")->fetch_object()->entry;
    * --------------------------------------------------------------------------
    */

class Mysqlw
{
    public $con;
    public $affected_rows=0;
    public $error="";

    private static $instance=null;

    public static function instance()
    {
        if(Mysqlw::$instance==null)
            return Mysqlw::$instance=new Mysqlw();
        else
            return Mysqlw::$instance;
    }

    private function __construct()
    {
        global $us_mysql_username, $us_mysql_password, $dbHost, $dbDatabase;
        $this->con = new mysqli($dbHost, $us_mysql_username, $us_mysql_password, $dbDatabase);
        if ($this->con->connect_errno)
            throw new Exception("Connect failed: " . $this->con->connect_error);
        Mysqlw::$instance=null;
    }

    public function __destruct()
    {
        if($this->con)
            $this->con->close();
    }

    public function wquery($query, $parameters=array())
    {
        $answer=null;
        $prepared = $this->con->prepare($query);
        if($prepared)
        {
            if($this->bindParams($prepared, $parameters) && $prepared->execute())
            {
                    $answer=$prepared->get_result(); //returns false for non select statements
            }
        }


        $this->affected_rows = $this->con->affected_rows;
        $this->error=$this->con->error;
        if($prepared)
            $prepared->close();
        if($answer!==null)
            return ($answer===FALSE) ? TRUE:$answer; //If it is false, then this was not a select statement. Still passed.
        else
            return false;
    }

    public function wqueryE($query, $parameters=array(), $msg="")
    {
        $ex=null;
        $answer = null;
        $prepared = $this->con->prepare($query);
        try
        {
            if(!$prepared)
                throw new Exception($msg . " (failed preparing statement: " . $this->con->error . ")");

            if(!$this->bindParams($prepared, $parameters))
                throw new Exception($msg . " (unable to bind parameters: " . $this->con->error . ")");

            //Parameters should be bound. Execute!
            if($prepared->execute())
                $answer=$prepared->get_result(); //returns false for non select statements
            else
                throw new Exception($msg . " (failed executing: " . $this->con->error . ")");
        }
        catch(Exception $e)
        {
            $ex=$e;
        }


        $this->affected_rows = $this->con->affected_rows;
        $this->error=$this->con->error;
        if($prepared)
        $prepared->close();

        if($ex!=null)
            throw $ex;

        return $answer===FALSE ? TRUE:$answer; //If it is false, then this was not a select statement. Still passed.
    }

    private function bindParams($prepared, $params)
    {
        if(count($params)==0)
            return true;

        $typestr = str_repeat("s", count($params));
        $refarray = array();

        for($i = 0; $i < count($params); ++$i)
            $refarray[] = &$params[$i];

        if(!call_user_func_array(array($prepared, "bind_param"), array_merge(array($typestr), $refarray)))
            return false;
        else
            return true;
    }
}

?>
