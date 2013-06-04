<?php
class DataFlowDao extends Dao
{

    /**
     * @param $table
     * @param $date
     * @param $type
     * @return false not found
     *         array when found
     */
    function get_init_flow_node($table, $date, $type)
    {
        $table = tranform_like_item($table);
        if ($type == 'upstream') {
            $sql = "select * from flow_node where acctstringdate='{$date}' and tables like '%{$table}|%'  and sessionid>0 order by starttime desc ";
        }
        if ($type == 'downstream') {
            $sql = "select * from flow_node where acctstringdate='{$date}'  and tables like '%{$table}|%'   and sessionid>0  order by starttime  ";
        }
        $rs = $this->db->results($sql);

        if (empty($rs)) {
            return false;
        }
        $ret = $rs[0];
        return $ret;
    }

    /**
     * @param $sessionid
     * @param $date
     * @return false when not found
     *         array when found
     */
    function get_flow_node_by_sessionid($sessionid, $date)
    {
        $sql = "select * from flow_node where sessionid={$sessionid} and acctstringdate='{$date}'";
        $rs = $this->db->results($sql);

        if (empty($rs)) {
            return false;
        }
        $ret = $rs[0];
        return $ret;
    }

    /**
     * @param $sessionid
     * @param $date
     * @return false when error
     *         array when success
     */
    function get_upstream_flow_sessionids($sessionid, $date)
    {
        $sql = "select fromsessionid as targetid from flow_relation
        where tosessionid=$sessionid and acctstringdate='$date'";
        $rs = $this->db->results($sql);
        return $rs;
    }

    /**
     * @param $sessionid
     * @param $date
     * @return false when error
     *         array when success
     */
    function get_downstream_flow_sessionids($sessionid, $date)
    {
        $sql = "select tosessionid as targetid from flow_relation
        where fromsessionid=$sessionid and acctstringdate='$date'  ";
        $rs = $this->db->results($sql);
        return $rs;
    }

    /**
     * @param $table
     * @param $limit
     * @return false when error
     *         array when success
     */
    function grep_flow_lkp_tables($table, $limit)
    {
        $table = tranform_like_item($table);
        $sql = "select distinct  concat(db,'.',tb) tb from flow_tb_lkp where concat(db,'.',tb) like '%$table%' order by length(concat(db,'.',tb)) asc";
        $rs = $this->db->results($sql, $limit);
        return $rs;
    }

}

?>