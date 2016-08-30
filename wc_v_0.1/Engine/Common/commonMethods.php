<?php

// Required File For DataProcessor
require_once(ENGINEROOT.'DbClass/objMysqliDb.php');

class commonMethods {

    public function __construct() {
        $this->db = new objMysqliDb;
    }

    public function getLinks(){
        $result = $this->db->query("SELECT * FROM `links`");
        return $result;
    }

    public function linkCrawlStatus($link_info){

        if($link_info['link_count'] == 1){
            return true;
        } else {
            return false;
        }

    }

}
