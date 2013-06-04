<?php

class DataFlowTargetParser
{
    private function first_parse($sql)
    {
        $patterns = array(
            "\\W+(?:insert|ins)\\s[\\w\\s]*?\\b((?!into)(?:\\w+\\s*\\.\\s*\\w+|\\w+))\\b",
            "\\W+merge\\s[\\w\\s]*?\\b((?!into)(?:\\w+\\s*\\.\\s*\\w+|\\w+))\\b",
            "\\W+(?:update|upd)\\s+\\b(\\w+\\s*\\.\\s*\\w+|\\w+)\\b",
            "\\W+(?:delete|del)\\s[\\w\\s]*?\\b((?!from)(?:\\w+\\s*\\.\\s*\\w+|\\w+))\\b");

        $patterns = join('|', $patterns);
        $ret = preg_match("/$patterns/i", $sql, $matches);
        if (empty($ret)) {
            return false;
        }
        for ($i = 1; $i < count($matches); $i++) { //$matches[0] is whole string
            if (empty($matches[$i])) { //$matches suppose to be array(0=>'delete from g.t',1=>'',2=>'',3=>'g.t')
                continue;
            }
            return $matches[$i];
        }
    }

    private function wrap_tgt($tgt)
    {
        $ret = explode('.', $tgt);
        return array('db' => $ret[0], 'tb' => $ret[1]);
    }

    /**
     * @param $sql
     * @return array("db"=>"db1","tb"=>"tb1")
     *         false if not match target table
     */
    function parse_target_table($sql)
    {
        $sql = $this->transform_sql($sql);
        $tgt = $this->first_parse($sql);
        if ($tgt === false) //has not found target table
            return false;
        if (strpos($tgt, '.') !== false) { //it is target table directly, gdw_tables.dw_attr_detail
            return $this->wrap_tgt($tgt);
        }
        $tgt = $this->maybe_alias($tgt, $sql);
        if ($tgt === false)
            return false; // volatile table no .
        else
            return $this->wrap_tgt($tgt); //extract table from alias
    }

    private function maybe_alias($tgt, $sql)
    {
        $pattern = "\\bfrom\\b.*?\\b(\\w+\\s*\\.\\s*\\w+)\\b\\s+(as\\s+|\\s)?$tgt\\b";
        $ret = preg_match("/$pattern/i", $sql, $matches);
        if (empty($ret)) {
            return false;
        }
        return $matches[1];
    }

    function transform_sql($sql)
    {
        $sql = " " . $sql . " ";
        $sql = preg_replace(array('/\\"/', '/\\,/', '/\\s+/', '/\\`/', "/\\'/"), array('', ' ', ' ', '', ''), $sql);
        return $sql;
    }

    function is_volatile_table_related($sql)
    {
        return preg_match('/(create\\s+volatile\\s+table\\s+)
        |(create\\s+set\\s+volatile\\s+table\\s+
        |(create\\s+multiset\\s+volatile\\s+table\\s+)
        |(create\\s+volatile\\s+set\\s+table\\s+)
        |(create\\s+volatile\\s+multiset\\s+table\\s+)/i', $sql) !== 0;
    }

    function is_other_valid_sql_type($sql)
    {
        return preg_match('/(check\\s+workload\\s+for)
        |(replace\\s+view\\s+)
        |(help\\s+table\\s+)
        |(help\\s+column\\s+)
        |(create\\s+macro\\s+)/i', $sql) !== 0;
    }

}


?>