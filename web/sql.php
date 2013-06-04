<?php

define('FUNC', 'FETCH_SCRIPT');
require_once 'source/common.php';
$sessionid = $_GET['sessionid'];
$date = $_GET['date'];
if (empty($sessionid) || empty($date))
    exit('Need sessionid and date');

$con = $G['mysql_utility'];
$rs = $con->results("select * from flow_dbql_raw_hist where acctstringdate='$date' and sessionid=$sessionid order by starttime asc");
$qtxt = '';
$line = PHP_EOL . PHP_EOL . '-----------------------------------------------------------------------' . PHP_EOL . PHP_EOL;
foreach ($rs as $r) {
    $qtxt .= $line . $r['querytext'];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>SQL</title>
    <link rel="stylesheet" href="css/myshCore.css"/>
    <link rel="stylesheet" href="css/shThemeDefault.css"/>


</head>
<div id="my_content">
    <div id="sql_code">
        <pre class="brush:sql">
            <?php echo $qtxt
            ?>
        </pre>
    </div>
</div>

<script type="text/javascript" src="js/sytxhigh/shCore.js"></script>
<script type="text/javascript" src="js/sytxhigh/shBrushSql.js"></script>
<script type="text/javascript">

    SyntaxHighlighter.config.tagName = "pre";
    SyntaxHighlighter.all();

</script>
</body>
</html>