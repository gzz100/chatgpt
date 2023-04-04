<?php
//$context = json_decode($_POST['context'] ?: "[]") ?: [];
$histroyMessageCount = intval(($_POST['count'] ?: "0"));
if($histroyMessageCount == "0" || !isset($_SESSION['data']) || empty($_SESSION['data']))
{
$postData = [
    "model" => "gpt-3.5-turbo",
    "temperature" => 1,
    "stream" => true,
    "messages" => [],
];
}else{
	$postData = json_decode($_SESSION['data']);
}
$realCount = count($postData['messages']);
if($realCount > $histroyMessageCount)
{
	$postData['messages']= array_slice($postData['messages'], -$histroyMessageCount);
	$realCount = $histroyMessageCount;
}
/*
if (!empty($context)) {
    $context = array_slice($context, -5);
    foreach ($context as $message) {
        $postData['messages'][] = ['role' => 'user', 'content' => str_replace("\n", "\\n", $message[0])];
        $postData['messages'][] = ['role' => 'assistant', 'content' => str_replace("\n", "\\n", $message[1])];
    }
}
*/
$postData['messages'][] = ['role' => 'user', 'content' => $_POST['message']];
$postData = json_encode($postData);
session_start();
$_SESSION['data'] = $postData;
if ((isset($_POST['key'])) && (!empty($_POST['key']))) {
    $_SESSION['key'] = $_POST['key'];
}
echo '{"success":true,"count":'. $realCount . '}';
