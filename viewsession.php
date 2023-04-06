<?php

function unicode2Chinese($str)
{
    return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
        function ($r) {return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));},
        $str);
}

session_start();
if(isset($_SESSION['data']) && !empty($_SESSION['data']))
	echo unicode2Chinese(json_encode($_SESSION['data']));
else
	echo "null";