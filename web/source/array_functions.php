<?php

function array_remove_empty($arr)
{
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            $arr[$key] = array_remove_empty($value);
            if (empty($arr[$key]))
                unset($arr[$key]);
        } else {
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    unset($arr[$key]);
                }
            } else {
                if ($value === null) {
                    unset($arr[$key]);
                }
            }
        }
    }
    return $arr;
}


function aia_unique_by_key($array, $key)
{
    $ids = array();
    $array2 = array();
    foreach ($array as $item) {
        if (isset($ids[$item[$key]]))
            continue;
        $array2[] = $item;
        $ids[$item[$key]] = 1;
    }

    return $array2;
}


function aia_extract_vals_from_key($arr, $key)
{
    $ret = array();
    foreach ($arr as $k => $v) {
        if (isset($v[$key])) {
            $ret[$k] = $v[$key];
        }
    }
    return $ret;
}

function aia_contain_kv($arr, $id, $val)
{
    foreach ($arr as $n) {
        if (isset($n[$id]) && $n[$id] === $val)
            return true;

    }
    return false;
}


function insert_uniq_id_array($arr, $id, $val)
{
    foreach ($arr as $n) {
        if ($n[$id] === $val[$id]) {
            return $arr;
        }
    }
    $arr[] = $val;
    return $arr;
}

function array_last_val($arr, $last)
{
    for ($i = 0; $i <= $last; $i++) {
        $ele = array_pop($arr);
    }
    return $ele;
}

function array_rearrange($arr)
{
    $index = 0;
    $ret = array();
    foreach ($arr as $val) {
        $ret[$index] = $val;
        $index++;
    }
    return $ret;
}


function aia_get_indexes_by_kv($arr, $k, $v)
{
    $ret = array();
    foreach ($arr as $i => $a) {
        if ($a[$k] === $v) {
            $ret[] = $i;
        }
    }
    return $ret;
}

function aia_get_arrs_by_kv($arr, $k, $v)
{
    $ret = array();
    foreach ($arr as $key => $a) {
        if ($a[$k] === $v) {
            $ret[$key] = $a;
        }
    }
    return $ret;
}


function aia_get_arrs_by_indexes($arr, $indexes)
{
    $ret = array();
    foreach ($indexes as $index) {
        $ret[] = $arr[$index];
    }
    return $ret;
}


function array_flat($arr)
{
    $new_arr = array();
    array_walk_recursive($arr, function ($ele) use (&$new_arr) {
        $new_arr[] = $ele;
    });
    return $new_arr;
}

?>