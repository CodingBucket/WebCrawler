<?php

require_once('config.php');
authFailRedirect();
ini_set('max_execution_time', 0);

if(isset($_POST['export']) && $_POST['export']){


    require_once(ABSPATH.'inc/parseCSV.php');

    $getPrice = new getPrice();    
    $csv = new parseCSV();

    $data['condition_id'] = $_POST['export_condition_id'];
    $data['from_date'] = $_POST['export_from_date'];
    $data['to_date'] = $_POST['export_to_date'];
    
    $data['from_month'] = $_POST['export_from_month'];
    $data['to_month'] = $_POST['export_to_month'];
    $data['time_period'] = $_POST['export_time_period'];

    if($data['condition_id'] != 'all' && $data['condition_id'] != ''){
        $data['condition_id'] = explode (", ", $data['condition_id']);
    } else { 
        unset($data['condition_id']);
    }

        //print_r($data);
        $crawl_data = $getPrice->get_search_data($data);
        //print_r($crawl_data);exit;
        
        if($data['time_period'] == 'monthly'){
        
            $titles = array(
                'crawling_year' => 'Year',
                'crawling_month' => 'Month',
                'condition_name' => 'Condition Name',
                'aramisauto_price' => 'Aramisauto Price',
                'vendezvotrevoiture_price' => 'Vendezvotrevoiture Price'
            );
        
        } else if ($data['time_period'] == 'weekly') {
            
            $titles = array(
                'crawling_date' => 'Crawl Date',
                'condition_name' => 'Condition Name',
                'aramisauto_price' => 'Aramisauto Price',
                'vendezvotrevoiture_price' => 'Vendezvotrevoiture Price',
                'vend_email' => 'Ven Email'
            );
            
        } else if ($data['time_period'] == 'daily') {
            
            $titles = array(
                'crawling_date' => 'Crawl Date',
                'condition_name' => 'Condition Name',
                'aramisauto_price' => 'Aramisauto Price',
                'vendezvotrevoiture_price' => 'Vendezvotrevoiture Price',
                'vend_email' => 'Ven Email'
            );
             
        } else {
            
            $titles = array(
                'crawling_date' => 'Crawl Date',
                'condition_name' => 'Condition Name',
                'aramisauto_price' => 'Aramisauto Price',
                'vendezvotrevoiture_price' => 'Vendezvotrevoiture Price'
            );
            
        }
            
        foreach($crawl_data as $k=>$v){
            
            
            if($data['time_period'] == 'monthly'){

                $v['crawling_year'] = $v['year_name'];
                $v['crawling_month'] = $v['month_name'];
                $v['aramisauto_price'] = $getPrice->formateNumber($v['aramisauto_price']);
                $v['vendezvotrevoiture_price'] = $getPrice->formateNumber($v['vendezvotrevoiture_price']);           

            } else if ($data['time_period'] == 'weekly') {

                $v['crawling_date'] = date('d/m/Y',strtotime($v['crawl_date']));
                $v['aramisauto_price'] = $getPrice->formateNumber($v['aramisauto_price']);
                $v['vendezvotrevoiture_price'] = $getPrice->formateNumber($v['vendezvotrevoiture_price']);
                $v['vend_email'] = $v['vend_email'];

            } else if ($data['time_period'] == 'daily') {

                $v['crawling_date'] = date('d/m/Y',strtotime($v['crawl_date']));
                $v['aramisauto_price'] = $getPrice->formateNumber($v['aramisauto_price']);
                $v['vendezvotrevoiture_price'] = $getPrice->formateNumber($v['vendezvotrevoiture_price']);
                $v['vend_email'] = $v['vend_email'];

            } else {

                $v['crawling_date'] = date('d/m/Y',strtotime($v['crawl_date']));
                $v['aramisauto_price'] = $getPrice->formateNumber($v['aramisauto_price']);
                $v['vendezvotrevoiture_price'] = $getPrice->formateNumber($v['vendezvotrevoiture_price']);

            }
            
       
            foreach($titles as $tk => $tv){
                $csvData[$k][$tk] = '';
                if(isset($v[$tk])){
                    $csvData[$k][$tk] = $v[$tk];
                }
            }
        }
        //print_r($csvData);exit;
        
        $filename_prefix = 'crawl_data_';
    

    $csv->output($filename_prefix.date('d-m-Y').'.csv', $csvData, $titles, ',');
}