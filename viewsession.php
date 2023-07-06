<?php
session_start();
if(!isset($_SESSION['user'])) exit;
require_once('sql.php');

function unicode2Chinese($str)
{
    return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
        function ($r) {return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));},
        $str);
}

/*if(isset($_SESSION['data']) && !empty($_SESSION['data']))
	echo unicode2Chinese(json_encode($_SESSION['data']));
else
	echo "null";*/
$session = array();
if(empty($_POST['session_id']))
{
    $sql = "SELECT * FROM user_session WHERE user_id=" . $_SESSION['user']['id'] . " ORDER BY update_time DESC LIMIT 0, 1 " ;
    $result = executeSQL($conn,$sql);
    $session = $result->fetchArray(SQLITE3_ASSOC);
}else{
    $sql = "SELECT * FROM user_session WHERE id=:p1 AND user_id=" . $_SESSION['user']['id'];
    $result = executeSQL($conn,$sql,$_POST['session_id']);
    $session = $result->fetchArray(SQLITE3_ASSOC);
}


// 存在对话
if ($session) {
    $sql = "SELECT * FROM chat_content WHERE session_id=" . $session['id'] . " ORDER BY id ASC" ;
    $result = executeSQL($conn,$sql);
    $data = array('messages'=>array());
    while($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        $data['messages'][]=array('role'=>'user','content'=>$row['user_content']);
        $data['messages'][]=array('role'=>'assistant','content'=>$row['ai_content']);
    }
    $_SESSION['currect_session_id']=$session['id'];
    $_SESSION['data']=$data;
    echo unicode2Chinese(json_encode($data));
} else { //创建新对话
    //先找找有没有空对话
    $sql = "SELECT user_session.id FROM user_session LEFT JOIN chat_content ON user_session.id = chat_content.session_id WHERE chat_content.session_id IS NULL";
    $result = executeSQL($conn,$sql);
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if($row)
    {
        $_SESSION['currect_session_id'] = $row['id'];
    }else{
        //创建对话
        $sql = "INSERT INTO user_session('user_id') VALUES (" . $_SESSION['user']['id'] . ")";
        executeSQL($conn,$sql);
        $_SESSION['currect_session_id'] = $conn->lastInsertRowID();
    }
    echo "null";
}
$conn->close();
