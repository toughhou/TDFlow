<?php

define("FUNC", 'flow_target_parser');
define("PROCESSED_MARK_ELAPSED", 1000);
require_once __DIR__ . '/../common.php';
command_mode();

/**********************************************************/
/***    @global @variable @definition   ** */
/**********************************************************/

$con = $G['mysql_utility'];


/**********************************************************/
/***       @prepare @data && @prepare @action   ** */
/**********************************************************/
$con->exec("delete from flow_tgt_id ");
$parser = new DataFlowTargetParser();
$qry_log_rs = $con->resource("select * from flow_dbql_raw");

log_disable();
/**********************************************************/
/***      @main @process @phase   ** */
/**********************************************************/
$processed_count = 0;
while ($qry_log = $con->fetch_assoc($qry_log_rs)) {
    $qry_log['querytext'] = querytext($qry_log['querytext']);
    $table = $parser->parse_target_table($qry_log['querytext']);
    if (!$table) {
        continue;
    }
    $qry_log['db'] = db($table['db']);
    $qry_log['tb'] = tb($table['tb']);
    $con->insert('flow_tgt_id', $qry_log);
    $processed_count++;
    if ($processed_count % PROCESSED_MARK_ELAPSED === 0)
        must_logger("$processed_count is parsed successfully...", 1);
}
must_logger("Totally $processed_count is parsed successfully.", 1);

/**********************************************************/
/***       @complete @the @last @stage @of @the @work   ** */
/**********************************************************/

logger_enable();

$con->exec("delete t from flow_tgt_id t, flow_dbql_raw r
            where t.queryid=r.queryid and r.lastresptime<r.starttime");

$con->close();

/**********************************************************/
/***     @function @definition   ** */
/**********************************************************/

/**
 * querytext pre-process
 * @param $querytext
 * @return pre-processed querytext
 */
function querytext($querytext)
{
    $str = str_replace("\r", PHP_EOL, $querytext);
    return $str;
}

?>