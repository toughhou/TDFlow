<?php

include_once S_ROOT . './source/array_functions.php';


function saddslashes($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = saddslashes($val);
        }
    } else if (is_string($string)) {
        $string = addslashes($string);
    }
    return $string;
}


function shtmlspecialchars($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = shtmlspecialchars($val);
        }
    } else {
        $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
            str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
    }
    return $string;
}

function logger_enable()
{
    global $G;
    $G['logged'] = true;
}

function log_disable()
{
    global $G;
    $G['logged'] = false;
}

function my_log_err($str, $write = -1)
{
    logger($str, $write);
    err($str);
}


function logger($str, $must_write = -1)
{
    global $G;
    if ($must_write == false) // specified 0 ,null or false
        return;

    if ($must_write === -1) //not specified
        $must_write = false;

    if ($G['logged'] == true || $must_write == true) {
        $date = date('Y-m-d H:i:s');
        $str = $str . "        $date " . PHP_EOL;
        if ($G['echo_log'] == true) {
            echo $str;
        }
        fwrite($G['log_handle'], $str);
        fflush($G['log_handle']);
    }
}

function must_logger($str)
{
    logger($str, 1);
}

function err($str)
{
    global $G;

    $date = date('Y-m-d H:i:s');
    $str = $str . "        $date " . PHP_EOL;
    if ($G['echo_err'] == true) {
        echo $str;
    }
    fwrite($G['err_handle'], $str);
    fflush($G['err_handle']);
}


function swap(&$a, &$b)
{
    $tmp = $b;
    $b = $a;
    $a = $tmp;
}


function not_empty()
{
    $num = func_num_args();
    for ($i = 0; $i < $num; $i++) {
        $val = func_get_arg($i);
        if (!isset($val) || trim($val) === '') {
            return false;
        }
    }
    return true;
}


function mexplode($del, $str)
{
    $arr = explode($del, $str);
    $arr = array_remove_empty($arr);
    return $arr;
}

function strim($arr)
{
    if (is_array($arr)) {
        foreach ($arr as $key => $val) {
            $arr[$key] = strim($val);
        }
    } else if (is_string($arr)) {
        $arr = trim($arr);
    }
    return $arr;
}

function stolowercase($arr)
{
    if (is_array($arr)) {
        foreach ($arr as $key => $val) {
            $arr[$key] = stolowercase($val);
        }
    } else if (is_string($arr)) {
        $arr = strtolower($arr);
    }
    return $arr;
}

function stouppercase($arr)
{
    if (is_array($arr)) {
        foreach ($arr as $key => $val) {
            $arr[$key] = stouppercase($val);
        }
    } else if (is_string($arr)) {
        $arr = strtoupper($arr);
    }
    return $arr;
}

function ajax_ret($code, $data = '')
{
    ob_clean();
    if ($code === false)
        $code = '-1';
    $code = '' . $code;
    $arr = array('code' => $code, 'data' => $data);
    $txt = json_encode($arr);
    exit($txt);
}

function ajax_may_ret()
{
    $val = func_get_arg(0);
    $arg_num = func_num_args();
    for ($i = 1; $i < $arg_num; $i += 2) {
        $k = func_get_arg($i);
        $v = func_get_arg($i + 1);
        $k === $val and  ajax_ret($k, $v);
    }


}


function exec_cmd($cmd, $out, $err)
{
    $descriptorspec = array(
        0 => array("pipe", "r"), // stdin is a pipe that the child will read from
        1 => array("file", $out, "a"), // stdout is a pipe that the child will write to
        2 => array("file", $err, "a") // stderr is a file to write to
    );


    $process = proc_open($cmd, $descriptorspec, $pipes);

    return is_resource($process);

}


function d($v)
{
    var_dump($v);
    exit();
}


/**
 * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
 * @param    string   $str    String in camel case format
 * @return    string            $str Translated into underscore format
 */
function from_camel_to_underline($str)
{
    $str[0] = strtolower($str[0]);
    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
    return preg_replace_callback('/([A-Z])/', $func, $str);
}

/**
 * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
 * @param    string   $str                     String in underscore format
 * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
 * @return   string                              $str translated into camel caps
 */
function from_underline_to_camel($str, $capitalise_first_char = false)
{
    if ($capitalise_first_char) {
        $str[0] = strtoupper($str[0]);
    }
    $func = create_function('$c', 'return strtoupper($c[1]);');
    return preg_replace_callback('/_([a-z])/', $func, $str);
}


function command_mode()
{
    global $G;
    ob_end_clean();
    echo_log();
    echo_err();
    $G['assert_callback'] = 'command_assert_handler';
}

function echo_log()
{
    global $G;
    $G['echo_log'] = true;
}

function no_echo_log()
{
    global $G;
    $G['echo_log'] = false;
}

function echo_err()
{
    global $G;
    $G['echo_err'] = true;
}

function no_echo_err()
{
    global $G;
    $G['echo_err'] = false;
}

function tranform_like_item($s)
{
    $s = str_replace('_', '\\_', $s);
    $s = str_replace('.', '\\.', $s);
    return $s;
}

function check($val, $desc = null)
{
    must($val, $desc, 1);
}

function must($val, $desc = null, $continue = null)
{
    global $G;

    if ($val === false) {
        $stack = debug_backtrace();
        $stack = $stack[0];
        $file = $stack['file'];
        $line = $stack['line'];
        if ($G['assert_callback']) {
            call_user_func($G['assert_callback'], $file, $line, $desc);
        } else {
            command_assert_handler($file, $line, $desc);
        }
        if (!$continue) {
            exit();
        }
    }
}


function command_assert_handler($file, $line, $desc = null)
{
    $output = "Assertion failed in $file on line $line" . PHP_EOL;
    if ($desc === null) {
        err($output);
    } else {
        $output .= "Message : $desc" . PHP_EOL;
        err($output);
    }
}

function api_assert_handler($file, $line, $desc = null)
{
    ajax_ret(-1, !empty($desc) ? $desc : 'Server Exception!');
}


function send_html_email($to, $subject, $message)
{
    $headers = "MIME-Version: 1.0" . PHP_EOL;
    $headers .= "Content-type:text/html;charset=utf8" . PHP_EOL;
    $headers .= "From: <$to>" . PHP_EOL;
    mail($to, $subject, $message, $headers);
}

function curl_get_file_contents($URL)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) return $contents;
    else return false;
}


function test_assert($val)
{
    $stack = debug_backtrace();
    $stack = $stack[0];
    $file = $stack['file'];
    $line = $stack['line'];
    if ($val === false) {
        command_assert_handler($file, $line);
    }
}

function load_ini(&$C,$path)
{
        $str=file_get_contents($path);
        $ini_list = explode(PHP_EOL,$str);
        foreach($ini_list as $item){
            $one_item = explode("=",trim($item));
            if(isset($one_item[0])&&isset($one_item[1])){
                $C[trim($one_item[0])] = trim($one_item[1]);
            }
        }
}

?>