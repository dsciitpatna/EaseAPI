<?php
require("../config.php");
require("model/user.php");
require("model/relation.php");

$user = new User($mysqli);

//echo $user->MailOrUsername("deepanjan05");

$user->loginUser("deepanjan05", '1234');

$followers_data = new Relation($mysqli, $user);

$follower = $followers_data->getAllFollowersOfLoggedIn()['result'][0];

echo $user->getName() . " is followed by " . $follower->getName() . "<br>";

$follower = $followers_data->getAllUsersFollowedByLoggedIn()['result'][1];

echo $user->getName() . " follows " . $follower->getName() . "<br>";

echo $user->checkUsername("deepanjan0") . "<br>";
 