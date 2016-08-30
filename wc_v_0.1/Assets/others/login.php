<?php
require_once('config.php');

$password = '';

if(isset($_POST['password']) && $_POST['password']){
    $password = $_POST['password'];
}

//pr($password);
//exit();

loginRedirect($password);