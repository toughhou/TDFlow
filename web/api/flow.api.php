<?php

/**********************************************************/
/***      @GREP @LKP @TABLES   ** */
/**********************************************************/
if ($cat === 'lkp_tables' && not_empty($term)) {
    $dao = new DataFlowDao();
    $tables = $dao->grep_flow_lkp_tables($term, $limit);
    $tables = array_map(function ($table) {
        return $table['tb'];
    }, $tables);
    exit(json_encode($tables));
}


/**********************************************************/
/***      @DATA @FLOW @GRAPH   ** */
/**********************************************************/
if ($cat === 'graph' && not_empty($date, $type)) {
    ($type === 'downstream' || $type === 'upstream') or ajax_ret(-1, "Type should be 'upstream' or 'downstream'. ");
    $graph = new DataFlowGraph($date, $type);
    if (!empty($sessionid)) {
        $graph->set_root_id($sessionid);
    } elseif (!empty($table)) {
        $root_id = $graph->set_root($table);
        must($root_id, "Can not find data flow");
    } else {
        ajax_ret(-1, "Parameter error");
    }
    log_disable();
    $graph->build_network();
    logger_enable();
    $edges = $graph->get_edges();
    $nodes = $graph->get_nodes();
    $nodes = array_rearrange($nodes);
    $edges = array_rearrange($edges);
    $root_id = $graph->get_root_id();
    $ret = array('graph' => array('nodes' => $nodes, 'edges' => $edges), 'root' => $root_id);
    ajax_ret(1, $ret);
}

/**********************************************************/
/***     @complete @the @last @stage @of @the @work   ** */
/**********************************************************/

ajax_ret(-1, 'Unexpected parameter');

?>
