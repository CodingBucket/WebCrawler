<?php

session_start();

define('ABSPATH', dirname(__FILE__).'/');

$config = array(
    'password' => md5('Admin12345'),
    'sessionKey' => md5('aramisLogin'),
    'database' => array(
        'host' => 'localhost',
        'username' => 'pge4fov_hasan',
        'password' => 'Am5WGRlhOi8aOvwM',
        'db' => 'pge4fov_php_crawler_v1',
        'port' => 3306,
        'charset' => 'utf8'
    ),
    'listLimit' => 25,
    'pageLinks' => array(
        'login' => 'index.php',
        'home' => 'data.php'
    )
);

require_once(ABSPATH.'inc/simple_html_dom.php');

require_once(ABSPATH.'inc/functions.php');

require_once(ABSPATH.'inc/objMysqliDb.php');

require_once(ABSPATH.'inc/crawlApi.php');

require_once(ABSPATH.'inc/getPrice.php');
