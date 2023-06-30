<?php
session_start();
if(!isset($_SESSION['user']) || empty($_POST['message']) || empty($_SESSION['currect_session_id'])) exit;
$histroyMessageCount = (!empty($_POST['count'])) ? intval($_POST['count']) : 0;
$model = (!empty($_POST['model'])) ? $_POST['model'] : 'gpt-3.5-turbo';
$temperature = (!empty($_POST['temperature'])) ? intval($_POST['temperature']) : 0;
if($temperature<0) $temperature = 0;
if($temperature>2) $temperature = 2;
if($histroyMessageCount == 0 || !isset($_SESSION['data']) || empty($_SESSION['data']))
{
    $_SESSION['data'] = [
        "model" => $model,
        "temperature" => $temperature,
        "stream" => true,
        "messages" => [],
    ];

}
$_SESSION['data']['stream'] = true;
$_SESSION['data']['model'] = $model;
$_SESSION['data']['temperature'] = $temperature;
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
