<?php

define("FUNC", 'flow_walk');
require_once __DIR__ . '/../common.php';
command_mode();

/**********************************************************/
/***      @global @variable @definition   ** */
/**********************************************************/
$con = $G['mysql_utility'];

$flow_relations = array();
$bad_flow_nodes = array();
$bad_index = -1;
$date = fetch_date();
must($date, "no required record found in flow_dbql_raw ");

$user_names = fetch_all_users();

/**********************************************************/
/***       @prepare @data && @prepare @action   ** */
/**********************************************************/
//clean the data of flow_node, delete date of current processing date and expired data
clean_flow_node($date);
//clean the data of flow_relation, delete date of current processing date and expired data
clean_flow_relation($date);
log_disable();
/**********************************************************/
/***      @process @flow @node   ** */
/**********************************************************/

// We use username to partitionly fetch sql logs, it can reduce the ammount
// of sqls we process at one time, and it will not affect result.
// But it maybe cause duplicated, we should do dedup after that.
foreach ($user_names as $username) {
    logger("Processing flow node for user : $username", 1);
    //find all session ids of some user from flow_tgt_id
    $sessionids = fetch_sessionids_of_user($username);
    foreach ($sessionids as $sessionid) {
        $node = wrap_flow_tgt_id_to_flow_node_by_session_id($sessionid);
        //insert into database
        insert_flow_node($node);
    }
}


/**********************************************************/
/***      @process @flow @relation   ** */
/**********************************************************/
foreach ($user_names as $username) {
    logger("Processing flow relation for username : $username", 1);
    //find all session ids of some user
    $flow_nodes = get_flow_nodes_by_username($username);
    foreach ($flow_nodes as $flow_node) {
        process_relation($flow_node);
    }
    foreach ($flow_relations as $flow_relation) {
        //relation has no duplicate
        $con->insert('flow_relation', $flow_relation);
    }

    $flow_relations = array();
}

/**********************************************************/
/***      @process @flow @bad @node ** */
/**********************************************************/
logger("Processing flow nodes", 1);
foreach ($bad_flow_nodes as $bad_flow_node) {
    //bad node has no duplicate
    $con->insert('flow_node', $bad_flow_node);
}


/**********************************************************/
/***       @complete @the @last @stage @of @the @work   ** */
/**********************************************************/
logger_enable();
flow_gen_lkp();
flow_dbql_raw_hist();
$con->close();


/**********************************************************/
/***       @function @definition   ** */
/**********************************************************/

/**
 * @param $username
 * @return array(123,345,......)
 */
function fetch_sessionids_of_user($username)
{
    global $con;
    $sessionids = $con->results("select t.sessionid
                        from
                        flow_tgt_id t, flow_dbql_raw r
                        where r.username='$username'
                        and r.sessionid=t.sessionid
                        group by t.sessionid");
    $sessionids = aia_extract_vals_from_key($sessionids, 'sessionid');
    return $sessionids;
}


/**
 * @param $session_id
 * @return array('lastresptime'=>xxx,starttime=>xxx,sessionid=>xxx,username=>xxx,db=>xx,tb=>xx)
 */
function wrap_flow_tgt_id_to_flow_node_by_session_id($session_id)
{
    global $con;
    //fetch useful info
    $node = $con->results("select
                             max(lastresptime) lastresptime
                            ,min(starttime) starttime
                            , sum(myeffectivecpu) myeffectivecpu
                            ,max(acctstringdate) acctstringdate
                            ,sessionid
                            ,max(username) username
                            from flow_dbql_raw r
                            where
                            sessionid=$session_id");
    $node = $node[0];
    //fetch db tb
    $tables = $con->results("select db,tb
                            from flow_tgt_id
                            where
                            sessionid=$session_id
                            group by db,tb");
    $node['tables'] = $tables;
    return $node;
}


function get_flow_node_by_sessionid($sessionid)
{
    global $con, $date;
    $ret = $con->results("select * from flow_node where sessionid=$sessionid  and acctstringdate='$date'");
    if (empty($ret)) {
        return false;
    }
    return $ret[0];
}

function get_flow_nodes_by_username($username)
{
    global $con, $date;
    $ret = $con->results("select * from flow_node where username='$username' and acctstringdate='$date' ");
    return $ret;
}


/**
 * Source tables contain all the tables participate in the session.
It will always include current session's target tables. It is very tricky when in fastload session that there's no tables in flow_obj_usg_raw
 * @param $sessionid
 * @return array()
 */
function db_tb_sources($sessionid)
{
    global $con;

    $source_tables = $con->results("select db,tb from flow_obj_usg_raw o
                    where o.sessionid= $sessionid
                    group by db,tb
                    union
                    select db,tb from flow_tgt_id t
                    where t.sessionid=$sessionid
                    group by db,tb");
//    check(!empty($source_tables), "session id $sessionid has no source db tb");
    return $source_tables;
}

/**
 * @param $db
 * @param $tb
 * @param $starttime
 * @return sessionid if found
 *         false if not found
 */
function find_source_sessionid_by_db_tb($db, $tb, $starttime)
{
    global $con, $date;
    $rs = $con->results("select n.sessionid from flow_tgt_id t, flow_node n where
                             t.sessionid = n.sessionid
                             and n.starttime<'$starttime'
                             and t.db='$db'
                             and t.tb='$tb'
                             and n.acctstringdate='$date'
                             order by n.starttime desc
                             limit 0,1");
    if (!empty($rs[0]['sessionid']))
        return $rs[0]['sessionid'];
    else
        return false; //$rs maybe empty because some table is the very beginning one, they maybe lookup table
}

function already_processed_node($sessionid)
{
    global $processed_nodes;
    return isset($processed_nodes[$sessionid]);

}

function already_has_relation($source_id, $target_id)
{
    global $flow_relations;
    return isset($flow_relations[$source_id . '-vt-' . $target_id]);
}

/**
 * @param $arr
 * @return string ex. gdw_tables.dw_attr_detail|gdw_tables.dw_lstg_item|
 *         '' if $arr is empty or null
 */
function tables2str($arr)
{
    if (empty($arr))
        return '';
    $arr = array_map(function ($table) {
        return $table['db'] . '.' . $table['tb'];
    }, $arr);
    return implode('|', $arr) . '|';
}

/**
 * @param $target
 */
function add_relation($source_id, $target_id)
{
    global $flow_relations, $date;
    //maybe override
    $flow_relations[$source_id . '-vt-' . $target_id] = array('fromsessionid' => $source_id, 'tosessionid' => $target_id
    , 'acctstringdate' => $date);
}


function insert_flow_node($node)
{
    global $con;
    $node['tables'] = tables2str($node['tables']);
    $con->insert('flow_node', $node);
}


/**
 * @param $db
 * @param $tb
 * @return array,  wrapped source_node
 */
function add_to_bad_node($db, $tb)
{
    global $bad_flow_nodes, $bad_index, $date;
    if (isset($bad_flow_nodes[$db . '.' . $tb])) {
        return $bad_flow_nodes[$db . '.' . $tb];
    }
    $source_node = array('sessionid' => $bad_index, 'acctstringdate' => $date);
    $source_node['tables'] = tables2str(array(array('db' => $db, 'tb' => $tb)));
    $bad_flow_nodes[$db . '.' . $tb] = $source_node;
    $bad_index--;
    return $source_node;
}

function process_relation($node)
{
    $username = $node['username'];
    $node_sessionid = $node['sessionid'];
    $node_starttime = $node['starttime'];
    $source_tables = db_tb_sources($node_sessionid);
    //$source_tables is just tables, not the actual session node, maybe general or maybe bad
    foreach ($source_tables as $source_table) {
        $source_db = $source_table['db'];
        $source_tb = $source_table['tb'];
        //try to find the actual session point of some table, the source session is always earlier than current session node in starttime
        $source_session_id = find_source_sessionid_by_db_tb($source_db, $source_tb, $node_starttime);
        if ($source_session_id !== false) { //means we find true source node
            if (already_has_relation($source_session_id, $node_sessionid)) {
                // If we encounter this situation, means we have already record
                // the relation then we can skip it.
                continue;
            }
            //wrap the session node according to the source_session_id
            $source_node = get_flow_node_by_sessionid($source_session_id);
//            check(!empty($source_node), "session id has no flow node : $source_session_id");
            //record node and relation into inter results, will be insert into table later
            add_relation($source_session_id, $node_sessionid);
            //
            // We only recursive process when curnode and sourcenode has
            // common username.
            //
            if ($source_node['username'] == $username) {
                process_relation($source_node);
            }

        } else { //did not find actual source sessionid, it is a bad node
            //
            // If t1 comes from t2 and we cannot find true point of t2, then
            // we said t2->t1 is bad. We create a empty instance for t2
            // node. If t1==t2 then we do not display, if t1<>t2 we display t2
            if (strpos( $node['tables'],"$source_db.$source_tb") !== false) // bad table is part of current node's tables
                continue;

            $bad_node = add_to_bad_node($source_db, $source_tb);
            add_relation($bad_node['sessionid'], $node_sessionid);
        }
    }
}


/**fetch processing date , if fail to fetch, program will exit.
 * @param
 * @return date
 */
function fetch_date()
{
    global $con;
    $rs = $con->results("select acctstringdate from flow_dbql_raw  limit 0,1");
    if ($rs === false)
        return false;
    return $rs[0]['acctstringdate'];
}


/**
 * @return array('u1,',u2'......)
 */
function fetch_all_users()
{
    global $con;
    $usernames = $con->results("select username from flow_dbql_raw   group by username");
    $usernames = aia_extract_vals_from_key($usernames, 'username');
    return $usernames;
}

function clean_flow_node()
{
    global $con, $date, $C;
    $con->exec("delete from flow_node where
            datediff('$date',acctstringdate)>{$C['flow_expire']}
            or acctstringdate='$date' ");
}

function clean_flow_relation()
{
    global $con, $date, $C;
    $con->exec("delete from flow_relation where
            datediff('$date',acctstringdate)>{$C['flow_expire']}
            or acctstringdate='$date' ");
}

function flow_gen_lkp()
{
    global $con;
    $con->exec("delete lkp from flow_tb_lkp lkp,flow_tgt_id tgt
            where lkp.db= tgt.db
            and lkp.tb=tgt.tb;");

    $con->exec("insert into flow_tb_lkp
            select db,tb
            from flow_tgt_id
            group by
            db,tb;");
}

function flow_dbql_raw_hist()
{
    global $con, $date, $C;
    $con->exec("delete from flow_dbql_raw_hist where
            datediff('$date',acctstringdate)>{$C['flow_expire']}
            or acctstringdate='$date'");
    $con->exec("insert into flow_dbql_raw_hist select * from flow_dbql_raw");
}


?>