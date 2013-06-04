<?php

class Dao
{
    protected $db;

    function __construct()
    {
        global $G;
        $this->db = $G['mysql_utility'];
    }

}


?>