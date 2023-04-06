<?php
session_start();
if(!isset($_SESSION['user'])) exit;
$histroyMessageCount = intval(($_POST['count'] ?: "0"));
if($histroyMessageCount == 0 || !isset($_SESSION['data']) || empty($_SESSION['data']))
{
$_SESSION['data'] = [
    "model" => "gpt-3.5-turbo",
    "temperature" => 1,
    "stream" => true,
    "messages" => [],
];
}
$realCount = count($_SESSION['data']['messages']);
if($realCount > $histroyMessageCount)
{
	$_SESSION['data']['messages']= array_slice($_SESSION['data']['messages'], -$histroyMessageCount);
	$realCount = $histroyMessageCount;
}
$_SESSION['data']['messages'][] = ['role' => 'user', 'content' => $_POST['message']];
if ((isset($_POST['key'])) && (!empty($_POST['key']))) {
    $_SESSION['key'] = $_POST['key'];
}
echo '{"success":true,"count":'. $realCount . '}';
