<?php

/**
 * Generate global v : $G,$C,$E
 */

define('S_ROOT', dirname(dirname(__FILE__) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
$G = array();

$G['date'] = date('Y_m_d_H_i_s', time());
$G['log_path'] = S_ROOT . '/log/' . FUNC . '.log';
$G['err_path'] = S_ROOT . '/log/' . FUNC . '.err';

$C = array();
require_once S_ROOT . './source/function_common.php';
require_once S_ROOT . './source/functions.php';

load_ini($C,S_ROOT.'/../config.ini');

ini_set('xdebug.max_nesting_level', 200000);

if ($C['env'] == 'dev') {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}
ini_set('error_reporting', E_ERROR);
ini_set('error_log', $G['log_path']);
ini_set('log_errors', 1);

$fp = fopen($G['log_path'], 'a+');
$G['log_handle'] = $fp;

$fp = fopen($G['err_path'], 'a+');
$G['err_handle'] = $fp;
$G['logged'] = true;
$G['echo_log'] = false;
$G['echo_err'] = false;

$G['assert_callback'] = 'api_assert_handler';

$magic_quote = get_magic_quotes_gpc();
if (empty($magic_quote)) {
    $_GET = saddslashes(strim($_GET));
    $_POST = saddslashes(strim($_POST));
}

function __autoload($class_name)
{
    $new_class_name = from_camel_to_underline($class_name);
    $file = S_ROOT . '/source/dao/' . $new_class_name . '.class.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    $file = S_ROOT . '/source/lib/' . $new_class_name . '.class.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

$G['mysql_utility'] = new MysqlUtility();
($G['mysql_con'] = $G['mysql_utility']->connect()) or ajax_ret(-1, "Mysql connect exception.");


ob_start();

?>