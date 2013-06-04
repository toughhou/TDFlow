<?php



function db($db)
{
    return strtolower(trim($db));
}

function tb($tb)
{
    return strtoupper(trim($tb));
}


/**********************************************************/
/***     @Test @Helper   ** */
/**********************************************************/
function change_to_test_env()
{
    global $C, $G;
    $C['mysql_database'] = 'tdflow_test';
    $G['mysql_utility'] = new MysqlUtility();
    ($G['mysql_con'] = $G['mysql_utility']->connect()) or ajax_ret(-1, "Mysql connect exception.");
}

function test_requice_php_process($str)
{
    global $G, $C, $E, $con;
    require $str;
    $G['mysql_utility'] = new MysqlUtility();
    ($G['mysql_con'] = $G['mysql_utility']->connect()) or ajax_ret(-1, "Mysql connect exception.");
    $con = $G['mysql_utility'];
}


?>