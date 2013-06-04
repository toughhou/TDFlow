<?php

class MysqlUtility
{

    public $con;

    private $table_columns = array();

    private $host;
    private $database;
    private $user;
    private $passwd;
    private $charset;

    function __construct()
    {
        global $C;
        $this->host = $C['mysql_host'];
        $this->database = $C['mysql_database'];
        $this->user = $C['mysql_user'];
        $this->passwd = $C['mysql_passwd'];
        $this->charset = $C['mysql_charset'];
    }


    /**
     * @return   false when fail to connect
     *           connection when success
     */
    function connect()
    {

        $db = mysql_connect($this->host, $this->user, $this->passwd);
        if ($db === false) {
            return false;
        }
        $this->con = $db;

        if (isset($this->charset))
            mysql_query("set names {$this->charset}", $this->con);
        if (isset($this->database))
            mysql_select_db($this->database, $this->con);

        logger("Mysql connected to {$this->host} : {$this->database}", true);
        return true;
    }

    /**
     * @param $table_name
     * @return array(xx,xx,xx)
     */
    function get_table_columns_array($table_name)
    {
        if (isset($this->table_columns[$table_name])) {
            return $this->table_columns[$table_name];
        } else {
            $sql = "SELECT COLUMN_NAME FROM information_schema.`COLUMNS` WHERE TABLE_NAME='$table_name' AND TABLE_SCHEMA= '{$this->database}'";
            $columns = $this->results($sql);
            $columns = aia_extract_vals_from_key($columns, 'COLUMN_NAME');
            $this->table_columns[$table_name] = $columns;
            return $columns;
        }
    }


    /**
     * @param $sql
     * @return true if ok
     *         false if err
     */
    function exec($sql)
    {
        $log = "Start to mysql query:" . PHP_EOL . "$sql" . PHP_EOL;
        logger($log);
        $rs = mysql_query($sql, $this->con);
        if ($rs === false) {
            $msg = mysql_error($this->con);
            $log = "Query Failed!!!     $msg";
            err($log);
            return false;
        }
        $affected_rows = mysql_affected_rows($this->con);
        $log = "Query Succ!!!     Affected Rows:$affected_rows ";
        logger($log);
        return true;
    }

    function affect_rows()
    {
        $affected_rows = mysql_affected_rows($this->con);
        return $affected_rows;
    }


    /**
     * @param $sql
     * @param $limit
     * @return false when error
     *         assoc array when success, but may be empty when no results are found
     */
    function results($sql, $limit = null)
    {
        $db = $this->con;
        if (!empty($limit)) {
            $sql .= " limit 0,$limit ";
        }
        $log = "Start to mysql fetch:" . PHP_EOL . "$sql" . PHP_EOL;
        logger($log);
        $rs = mysql_query($sql, $db);
        if ($rs === false) {
            $msg = mysql_error($db);
            $log .= "Fetch Failed!!!     $msg";
            err($log);
            return false;
        }

        $affected_rows = mysql_affected_rows($db);
        $log = "Fetch Succ!!!     Affected Rows:$affected_rows ";

        logger($log);

        $result_arr = array();
        while ($row = mysql_fetch_assoc($rs)) {
            $result_arr[] = $row;
        }
        mysql_free_result($rs);

        return $result_arr;
    }

    /**
     * @param $sql
     * @param $limit
     * @return false when error
     *         resource
     */
    function resource($sql, $limit = null)
    {
        $db = $this->con;
        if (!empty($limit)) {
            $sql .= " limit 0,$limit ";
        }
        $log = "Start to mysql fetch:" . PHP_EOL . "$sql" . PHP_EOL;
        logger($log);
        $rs = mysql_query($sql, $db);
        if ($rs === false) {
            $msg = mysql_error($db);
            $log .= "Fetch Failed!!!     $msg";
            err($log);
            return false;
        }

        $affected_rows = mysql_affected_rows($db);
        $log = "Fetch Succ!!!     Affected Rows:$affected_rows ";

        logger($log);

        return $rs;
    }

    /**
     * @param $rs
     * @return assoc array
     *         false if no more rows
     */
    function fetch_assoc($rs)
    {
        return mysql_fetch_assoc($rs);
    }

    /**
     * @param $tablename
     * @param $insertsqlarr
     * @return false when error
     *         inserted_id if pk is generated
     *         true if sucess and no pk is generated
     */
    function insert($tablename, $insertsqlarr)
    {
        return $this->insert_helper($tablename, $insertsqlarr, 'INSERT');
    }

    /**
     * @param $tablename
     * @param $insertsqlarr
     * @return false when error
     *         inserted_id if pk is generated
     *         true if sucess and no pk is generated
     */
    function replace($tablename, $insertsqlarr)
    {
        return $this->insert_helper($tablename, $insertsqlarr, 'REPLACE ');
    }

    function insert_helper($tablename, $insertsqlarr, $action)
    {
        $insertkeysql = $insertvaluesql = $comma = '';
        $column_names = $this->get_table_columns_array($tablename);
        foreach ($insertsqlarr as $insert_key => $insert_value) {
            if (!in_array($insert_key, $column_names)) {
                continue;
            }
            $insertkeysql .= $comma . '`' . $insert_key . '`';
            if ($insert_value === NULL)
                $insertvaluesql .= $comma . 'NULL';
            else
                $insertvaluesql .= $comma . '\'' . $insert_value . '\'';
            $comma = ', ';
        }
        $sql = "$action INTO $tablename ($insertkeysql) VALUES ($insertvaluesql)";
        $log = "Start to mysql insert:" . PHP_EOL . "$sql" . PHP_EOL;
        logger($log);
        $ret = mysql_query($sql, $this->con);
        if ($ret === false) {
            $msg = mysql_error($this->con);
            $log .= "Insert Failed!!!     $msg";
            err($log, true);
            return false;
        }
        $log = "Insert Succ!!!";
        logger($log);
        $id = mysql_insert_id($this->con);
        if ($id)
            return $id;
        return true;
    }

    /**
     * @param $table
     * @param $where_arr
     * @return true when the record is found
     *         false when the record is not found or error
     */
    function check_if_exist($table, $where_arr)
    {
        $sql = 'SELECT * FROM ' . $table . ' WHERE ';
        if (is_array($where_arr)) {
            $comma = ' ';
            foreach ($where_arr as $key => $val) {
                if ($val !== NULL)
                    $sql .= $comma . ' `' . $key . "`='" . $val . "' ";
                else
                    $sql .= $comma . ' `' . $key . "` IS NULL ";
                $comma = ' AND ';
            }
        } else {
            $sql .= $where_arr;
        }
        $res = $this->results($sql);
        if (!empty($res)) {
            return true;
        } else {
            return false;
        }
    }

    function close()
    {
        mysql_close($this->con);
    }


    function create_table_like($new_table, $old_table)
    {
        mysql_query("drop table if exists $new_table");
        mysql_query("create table $new_table like $old_table");
    }

    function err()
    {
        return mysql_error($this->con);
    }
}

?>