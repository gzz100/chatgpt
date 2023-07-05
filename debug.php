<?php
session_start();
if(!isset($_SESSION['user']) || empty($_SESSION['data'])) exit;
echo json_encode($_SESSION['data']);
?>