<?php
    // DataProcessor Trigger

    ini_set('max_execution_time', 0);
    define('ENGINEROOT', dirname(__FILE__).'/');

    // Required Files For Crawler
    require_once('DataProcessor/dataProcessor.php');
    require_once('Common/commonMethods.php');
    require_once('Common/engineHelper.php');

    $processor = new dataProcessor;
    $common = new commonMethods;

    $page_doc = $processor->processRepoData();
    
    $links = $common->getLinks();
    
    foreach($links as $key=>$link_info){

        $check_link = $common->linkCrawlStatus($link_info);

        if($check_link == true){

            $page_doc = $processor->getDataFromRepo($link_info);

            $links = $processor->get_links($link_info);  // Get all links from the page content

            $links = $processor->distinct_array($links); // Get distinct links

            $processor->savePageLinks($links,$link_info);

            $page_id = $processor->savePageDoc($page_doc);

            $fresh_words = $processor->processPageDocIndex($page_doc,$link_info['link_id']);

            $processor->savePageDocIndex($fresh_words,$page_id);
        
        }
        
    }

    
     
      
      
 ?>    