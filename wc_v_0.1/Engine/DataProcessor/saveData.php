<?php

class saveData {

    public function __construct() {
        $this->db = new objMysqliDb;
    }
    
    // Saving Links of the crawled data
    public function save_links($data){

        // Insert Data In Links Table
        $this->db->insert($data,'links');
                
    }  
    
    public function savePageContent($data){

        // Insert Data In Links Table
        $this->db->insert($data,'page_docs');
        return $this->db->getLastInsertId();

    }  
    
    public function insertPagedocIndex($data){

        // Insert Data In Links Table
        $this->db->insert($data,'page_doc_indexs');
                       
    }  
    
    function getPageDoc($doc_index){     
        //$doc_index = 'web';
        $result =  $this->db->query("SELECT * FROM `page_doc_indexs` WHERE `doc` = '$doc_index'");
        return $result;
    }
    
    function updatePagedocIndex($data){    
        
        $doc = $data['doc'];
        //$doc = 'web';
        $doc_page_ids = $data['doc_page_ids'];

        $this->db->query("UPDATE `page_doc_indexs` SET `doc_page_ids` = '$doc_page_ids' WHERE `doc` = '$doc' ");

    }
    
    public function getLinks(){        
        $result =  $this->db->query("SELECT * FROM `links` WHERE `link_count` = 0");
        return $result;        
    }
    
    public function insert_page_link($data){

        // Insert Data In Links Table
        $this->db->insert($data,'links');

    } 
	
}
