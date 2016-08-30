<?php

session_start();

define('ABSPATH', dirname(__FILE__).'/');

// General Configaration For Site
$config = array(
    'password' => md5('123456'),
    'sessionKey' => md5('aramisLogin'),
    'database' => array(
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'db' => 'be_1',
        'port' => 3306,
        'charset' => 'utf8'
    ),
    'listLimit' => 25,
    'pageLinks' => array(
        'login' => 'index.php',
        'home' => 'data.php'
    )
);


require_once(ABSPATH.'include/simple_html_dom.php');  // For Read Html In PHP.

require_once(ABSPATH.'include/functions.php');        // General Functions.

require_once(ABSPATH.'include/objMysqliDb.php');      // Database Class.

require_once(ABSPATH.'curl/phpCurl.php');             // PHP CURL function for Crawling.





