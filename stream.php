<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/event-stream");
header("X-Accel-Buffering: no");
session_start();
if(!isset($_SESSION['user']) || empty($_SESSION['currect_session_id'])) exit;
require_once('sql.php');
$postData = json_encode($_SESSION['data']);
$_SESSION['response'] = "";
$ch = curl_init();

if (isset($_SESSION['key'])) {
    $OPENAI_API_KEY = $_SESSION['key'];
}else{
    $sql = "SELECT * FROM setting WHERE key='OPENAI_API_KEY'";
    $result = executeSQL($conn,$sql);
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $OPENAI_API_KEY = $row['value'];
    }
}

$headers  = [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $OPENAI_API_KEY
];

setcookie("errcode", ""); //EventSource无法获取错误信息，通过cookie传递
setcookie("errmsg", "");

$callback = function ($ch, $data) {
    $complete = json_decode($data);
    if (isset($complete->error)) {
        setcookie("errcode", $complete->error->code);
        setcookie("errmsg", $data);
        if (strpos($complete->error->message, "Rate limit reached") === 0) { //访问频率超限错误返回的code为空，特殊处理一下
            setcookie("errcode", "rate_limit_reached");
        }
        if (strpos($complete->error->message, "Your access was terminated") === 0) { //违规使用，被封禁，特殊处理一下
            setcookie("errcode", "access_terminated");
        }
        if (strpos($complete->error->message, "You didn't provide an API key") === 0) { //未提供API-KEY
            setcookie("errcode", "no_api_key");
        }
        if (strpos($complete->error->message, "You exceeded your current quota") === 0) { //API-KEY余额不足
            setcookie("errcode", "insufficient_quota");
        }
        if (strpos($complete->error->message, "That model is currently overloaded") === 0) { //OpenAI服务器超负荷
            setcookie("errcode", "model_overloaded");
        }
    } else {
        echo $data;
        $_SESSION['response'] .= $data;
    }
    return strlen($data);
};

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);
$sql = "SELECT * FROM setting WHERE key='PROXY'";
$result = executeSQL($conn,$sql);
$row = $result->fetchArray(SQLITE3_ASSOC);
if ($row) {
    curl_setopt($ch, CURLOPT_PROXY, $row['value']);
}


curl_exec($ch);

$answer = "";
if (substr(trim($_SESSION['response']), -6) == "[DONE]") {
    $_SESSION['response'] = substr(trim($_SESSION['response']), 0, -6) . "{";
}
$responsearr = explode("}\n\ndata: {", $_SESSION['response']);

foreach ($responsearr as $msg) {
    $contentarr = json_decode("{" . trim($msg) . "}", true);
    if (isset($contentarr['choices'][0]['delta']['content'])) {
        $answer .= $contentarr['choices'][0]['delta']['content'];
    }
}
$answer = trim($answer);
//$filecontent = $_SERVER["REMOTE_ADDR"] . " | " . date("Y-m-d H:i:s") . "\n";
//$filecontent .= "Q:" . end($_SESSION['data']['messages'])['content'] .  "\nA:" . $answer . "\n----------------\n";

$sql = "INSERT INTO chat_content('user_content','ai_content','model','temperature','history_count','session_id') VALUES (:p1,:p2,:p3,:p4,:p5," . $_SESSION['currect_session_id'] . ")";
executeSQL($conn,$sql,end($_SESSION['data']['messages'])['content'],$answer,$_SESSION['data']['model'],$_SESSION['data']['temperature'],count($_SESSION['data']['messages'])-1);
$sql = "UPDATE user_session SET update_time = datetime('now') WHERE id = " . $_SESSION['currect_session_id'];
executeSQL($conn,$sql);


$_SESSION['data']['messages'][] = ['role' => 'assistant', 'content' => $answer];



//$myfile = fopen(__DIR__ . "/chat.txt", "a") or die("Writing file failed.");
//fwrite($myfile, $filecontent);
//fclose($myfile);
curl_close($ch);
$conn->close();
