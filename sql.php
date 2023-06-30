<?php
// 连接数据库

try {
    $conn = new SQLite3("../../chatgpt.sqlite3");
} catch (Exception $e) {
    echo json_encode(array('msg' => 'Database connection failed','code' => 1));
}

function executeSQL($conn, $sql, ...$args)
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // 预处理创建失败，返回错误的消息
        echo json_encode(array('msg' => 'Prepare statement failed','code' => 2));
        exit;
    }
    $allArgs = func_get_args();
    //if(!empty($argtypestr))
    {
        for($i=2;$i<count($allArgs);$i++) {
            $keyname = ":p" . ($i-1);
            $stmt->bindValue($keyname, $allArgs[$i]);
        }
    }
    $result = $stmt->execute();
    if (!$result) {
        // SQL执行失败，返回错误的消息
        echo json_encode(array('msg' => 'SQL execution failed','code' => 3));
        exit;
    }
    return $result;
}
?>