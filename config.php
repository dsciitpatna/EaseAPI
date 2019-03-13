<?php
date_default_timezone_set('Asia/Calcutta');

require_once("global.php");
// Mysql credentials and details

$host = 'localhost';
$username = 'root';
$password = 'Deep@0526';
$db = 'EaseAPI';

// Connect to mysql
$mysqli = new mysqli($host, $username, $password, $db);

// Check if there is any error in creating db connection.
if ($mysqli->connect_error) {
  die('Connect Error: Could not connect to database');
}