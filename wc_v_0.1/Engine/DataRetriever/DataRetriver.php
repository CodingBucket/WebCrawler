<?php

// Required Files For Crawler
require_once('lib/simple_html_dom.php');

// Required File For DataProcessor 
require_once('../DataProcessor/dataProcessor.php');

class webCrawler {
    
    public function __construct() {
            global $config;
            //parent::__construct($config['database']);
    }
        
    public function do_crawl(){
        
        // Get All HTML of this link using php curl
        $html = $this->curl("http://stackoverflow.com/");
        
        // Read all HTMl using php simple html dom
        $html = str_get_html($html);
        //print_r($html);exit;

        // Find all links
        foreach($html->find('a') as $element){
               $href[] = $element->href; 
        } 
        //print_r($href);exit;
        
        // Sending all href to save	
        $dataProcessor = new dataProcessor;	
        $dataProcessor->links_processing($href);
			  
    }
    
    
    private function curl($url = '') {
        
            $resp = '';

            if ($url) {
                $url = str_replace(' ','+',$url);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                if(stripos($url, 'https://') !== false){
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);                            
               
                $content = curl_exec($ch);

                curl_close($ch);

                $resp = $content;
            }

            return $resp;
    }
            
         
	
}
