<?php
session_start();
if(!isset($_SESSION['user'])) exit;
// 连接数据库
require_once('sql.php');

$page = (!empty($_POST['page'])) ? $_POST['page'] : 1;
$limit = (!empty($_POST['limit'])) ? $_POST['limit'] : 0;

// 查询商品总数
$result = executeSQL($conn, "SELECT COUNT(*) AS total FROM user_session WHERE user_id = " . $_SESSION['user']['id']);
$total = $result->fetchArray()['total'];

// 查询商品列表
if ($limit > 0) {
    $sql = "SELECT id,title FROM user_session WHERE user_id = " . $_SESSION['user']['id'] . " ORDER BY created_at DESC LIMIT :p1, :p2";
} else {
    $sql = "SELECT id,title FROM user_session WHERE user_id = " . $_SESSION['user']['id'] . " ORDER BY created_at DESC";
}
$result = executeSQL($conn, $sql, $page,$limit);
$sessions = array();
while($row = $result->fetchArray(SQLITE3_ASSOC))
{
    $sessions[] = $row;
}

// 返回商品列表和分页信息
echo json_encode(array('data' => $sessions, 'count' => $total, 'page' => $page, 'limit' => $limit, 'code' => 0));
?>
