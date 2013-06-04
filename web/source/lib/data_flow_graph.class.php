<?php

class DataFlowGraph extends FlowGraph
{
    private $date;
    private $type;
    private $dao;

    function  __construct($date, $type, $dao = null)
    {
        parent::__construct();
        $this->date = $date;
        $this->type = $type;
        if ($dao !== null)
            $this->dao = $dao;
        else
            $this->dao = new DataFlowDao();
    }

    /**
     * @param $target_id
     * @return array(id1,id2,id3......)
     */
    public function get_network_source_ids($target_id)
    {

        if ($this->type == 'upstream')
            $rs = $this->dao->get_upstream_flow_sessionids($target_id, $this->date);
        if ($this->type == 'downstream')
            $rs = $this->dao->get_downstream_flow_sessionids($target_id, $this->date);
        $ids = aia_extract_vals_from_key($rs, 'targetid');
        return $ids;
    }


    private function cut_time($time)
    {
        $ret = substr($time, 11, 8);
        if ($ret) {
            return $ret;
        }
        return '';
    }

    /**
     * @param $id
     * @return array('sessionid'=>xx,'queryband'=>'xx'......)
     */
    public function wrap_node($id)
    {
        $node = $this->dao->get_flow_node_by_sessionid($id, $this->date);
        $sessionid = $node['sessionid'];
        $wrap_node = array('id' => $sessionid
        , 'cpu' => $node['myeffectivecpu']
        , 'starttime' => $this->cut_time($node['starttime'])
        , 'lastresptime' => $this->cut_time($node['lastresptime'])
        , 'username' => $node['username']
        , 'tables' => $node['tables']);
        return $wrap_node;
    }


    function set_root($table)
    {
        $node = $this->dao->get_init_flow_node($table, $this->date, $this->type);
        if ($node === false)
            return false;
        $this->set_root_id($node['sessionid']);
        return $node['sessionid'];
    }


}

?>