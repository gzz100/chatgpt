<?php
session_start();
if(!isset($_SESSION['user']) || empty($_SESSION['data'])) exit;

function unicode2Chinese($str)
{
    return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
        function ($r) {return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));},
        $str);
}

echo unicode2Chinese(json_encode($_SESSION['data']));
?>