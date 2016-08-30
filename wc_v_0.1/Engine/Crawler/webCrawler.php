<?php

// Required File For DataProcessor 
require_once('dbClass/objMysqliDb.php');

class webCrawler extends objMysqliDb{
      
    // WC1
    public function getLinks(){        
        $result = $this->query("SELECT link_id, link FROM `links` WHERE `link_count` = 0");
        return $result;        
    }
    
    // WC2
    public function getPageContent($links){
        
        foreach($links as $val){
            $page_content = file_get_contents($val['link']);  
            
            // WC2.1
            $this->savePageContentInPageRepo($page_content,$val['link_id']);
        }

    }
   
    
    // WC2.1
    public function savePageContentInPageRepo($page_content,$link_id){
        
        $repo_name = $link_id.'_page_html.html';
        file_put_contents('Repo/PageHtmlRepo/'.$repo_name, $page_content);

    }
    
}
