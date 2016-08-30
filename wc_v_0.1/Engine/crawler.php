<?php
    // WebCrawler Trigger

    ini_set('max_execution_time', 0);
    define('FILEPATH', dirname(__FILE__).'/');

    // Required Files For Crawler
    require_once('Crawler/webCrawler.php');
    require_once('Common/engineHelper.php');

    $wc = new webCrawler;
    
    $links = $wc->getLinks();  // WC1
    
    $page_content = $wc->getPageContent($links); // WC2
    //de($page_content);
      
 ?>    