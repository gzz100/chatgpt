<?php
// 启动Session
session_start();

// 清空Session
$_SESSION = array();
session_destroy();

header('Content-Type: application/json');
// 返回成功的消息
echo json_encode(array('msg' => 'Logout successful'));
// 跳转到登录页面
//header('Location: ../page/login.html');
exit;
?>