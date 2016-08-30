<?php

class getPrice extends objMysqliDb {
    
        public function __construct() {
            global $config;
            parent::__construct($config['database']);
        }
        
        /* Aramisauto Functions Start */
        
            // For Aramis
            function curl_aramis($url = '', $post = array(), $params = array()) {
                $resp = '';

                if ($url) {
                    $url = str_replace(' ','+',$url);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
                    //curl_setopt($ch, CURLOPT_HEADER, 1);
                    if(stripos($url, 'https://') !== false){
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    }
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    //curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                    if ($post) {
                        $post_str = '';
                        if(is_array($post)){
                            $post_str = http_build_query($post);
                        }
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
                    }
                    if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
                        //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                    }

                    $useragent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

                    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
                    //curl_setopt($ch, CURLOPT_REFERER, 'http://www.vendezvotrevoiture.fr/valeur/10-8/step/2/');

                    ////for aramis
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest"));


                    if(isset($params['jar']) && $params['jar']){
                        curl_setopt($ch, CURLOPT_COOKIEJAR, $params['jar']);
                    }
                    if(isset($params['cookiefile']) && $params['cookiefile']){
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $params['cookiefile']);
                    }               
                    //curl_setopt($ch, CURLOPT_HEADER, 1);
                    $content = curl_exec($ch);

                    curl_close($ch);

                    $resp = $content;
                }

                return $resp;
            }

            // For Aramis
            function phpCurl($url = '', $post = array(), $params = array()) {
                $resp = '';
                    //echo $url;

                if ($url) {
                    $url = str_replace(' ','+',$url);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


                    //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8"));
                    if ($post) {
                        $post_str = '';
                        if(is_array($post)){
                            $post_str = http_build_query($post);
                        }

                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
                    }
                    if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
                        //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                    }

                    if(isset($params['cookiefile']) && $params['cookiefile'] && file_exists($params['cookiefile'])){
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $params['cookiefile']);
                    }      

                    if(isset($params['jar']) && $params['jar'] && file_exists($params['jar'])){
                       curl_setopt($ch, CURLOPT_COOKIEJAR, $params['jar']);
                    }

                    $content = curl_exec($ch);
                    //        $response = curl_getinfo($ch);
                    //
                    //        pr($response);

                    curl_close($ch);

                    $resp = $content;
                }

                return $resp;
            }

            // For Aramis 
            function get_aramis_price($con){


                $aramis_user_info = explode("|", $con['aramis_condition']);
                $aramis_user_phone = preg_replace('/\b \b/', '', $aramis_user_info[16]);
                
                    $string = $this->generateRandomString();
                    $number = $this->generateRandomNumber();
                    $user_email = $string.$number.'@gmail.com';

                $form_data = array(
                    'aramis_contact[gender]'=>"m",
                    'aramis_contact[lastname]'=>'Model'.$number,
                    'aramis_contact[email]'=>$user_email,
                    'aramis_contact[phone]'=>$aramis_user_phone,
                    'aramis_contact[zipCode]'=>99000,
                    'aramis_contact[cmpid]'=>"",
                );
                
                //echo '<pre>';
                //print_r($form_data);
                //echo '</pre>';
                
                //echo '<pre>';
                //print_r($con['aramisauto_url']);
                //echo '</pre>';


                //$url = "https://www.aramisauto.com/reprise/vos-coordonnees/check?releaseYear=2010&releaseMonth=11&brand=PEUGEOT&model=207&bodywork=Berline&doors=5&fuel=Diesel&transmission=Bo%C3%AEte+manuelle&engine=207+1.4+HDi+70ch+BLUE+LION&package=Active&mileage=76912&op-abtest=&buy_project_choices=4&delay_project_choices=60&autoselected=1";
                $url = $con['aramisauto_url'];  

                $html = $this->curl_aramis($url,$form_data,array());

                $html = json_decode($html, true);


                    $html = str_get_html($html['view']);

                    // Find all links
                    foreach($html->find('a') as $element){
                           $link = $element->href; 
                    } 

                    
                    $url = "https://www.aramisauto.com".$link;
                    $url = preg_replace('/\bamp;\b/', '', $url);
                    
                    
                    //$url = $con['aramisauto_url'];
                    $html =  $this->phpCurl($url);

                    $html = str_get_html($html);


                    foreach($html->find('.valuation-price p[2]') as $value) {
                            $description = $value->innertext.'<br>';
                    }		
                    foreach($html->find('.valuation-price p[3]') as $value) {
                           $price = $value->innertext.'<br>';
                    }
                    
                    $price = $this->parseNumber($price);
                    
                    return $price;


            }
        
        /* Aramisauto Functions End */
            
        
        /* Vendezvotrevoiture Functions Start */    
            
            // For vendezvotrevoiture
            function curl($url = '', $post = array(), $params = array()) {
            $resp = '';

            if ($url) {
                $url = str_replace(' ','+',$url);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
                //curl_setopt($ch, CURLOPT_HEADER, 1);
                if(stripos($url, 'https://') !== false){
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                //curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                if ($post) {
                    $post_str = '';
                    if(is_array($post)){
                        $post_str = http_build_query($post);
                    }
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
                }
                if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
                    //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                }

                $useragent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

                curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
                curl_setopt($ch, CURLOPT_REFERER, 'http://www.vendezvotrevoiture.fr/valeur/10-8/step/2/');



                if(isset($params['jar']) && $params['jar']){
                    curl_setopt($ch, CURLOPT_COOKIEJAR, $params['jar']);
                }
                if(isset($params['cookiefile']) && $params['cookiefile']){
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $params['cookiefile']);
                }               
                //curl_setopt($ch, CURLOPT_HEADER, 1);
                $content = curl_exec($ch);

    //            $response = curl_getinfo($ch);
    //            
    //            $this->pr('$response');
    //            $this->pr($response);

                curl_close($ch);

                $resp = $content;
            }

            return $resp;
        }
            
            // Crawling www.vendezvotrevoiture.fr
            function ven_crawling($con = ''){
                
                    // only getting cookies
                    $html = $this->curl("http://www.vendezvotrevoiture.fr/",array(),array(
                                            'jar'=> ABSPATH.'cookies1/jar1.txt',  // For getting cookies                              
                                        ));


                    $data_first = array(
                        'step1[manufacturer]'=> $con['ven_manufacturer'],
                        'step1[maintype]'=> $con['ven_maintype'],
                        'step1[builtDate]'=> $con['ven_builtDate'],
                        'step1[_is_old_car]'=> "0",
                    );



                   $html = $this->curl("http://www.vendezvotrevoiture.fr/valeur/10-8/",$data_first,array(
                                            'jar'=>ABSPATH.'cookies1/jar2.txt',            // For getting cookies
                                            'cookiefile'=>ABSPATH.'cookies1/jar1.txt'      // For sending cookies
                                        ));


                    $html = str_get_html($html);
                    $nodes = $html->find("input");


                    foreach ($nodes as $node) {
                        $name = $node->name;
                        if($name == 'step2[_token]'){
                            $token = $node->value;
                        }

                    }

                        $string = $this->generateRandomString();
                        $number = $this->generateRandomNumber();
                        $email_account = $this->getEmailAccount();
                        $ven_email = $string.$number.$email_account;

                    $data_second = array(
                        'step2[bodytype]'=>$con['ven_bodytype'],
                        'step2[mtypeDetail]'=>$con['ven_mtypeDetail'],
                        'step2[mtypeSub]'=>$con['ven_mtypeSub'],
                        'step2[km]'=>$con['ven_km'],
                        'step2[email]'=>$ven_email,
                        'step2[_token]'=>$token,
                    );


                    $html = $this->curl("http://www.vendezvotrevoiture.fr/valeur/10-8/step/2/",$data_second,array(
                        'jar'=> ABSPATH.'cookies1/jar3.txt',            // For getting cookies
                        'cookiefile'=> ABSPATH.'cookies1/jar2.txt'      // For sending cookies
                    ));


                    $html = $this->curl("http://www.vendezvotrevoiture.fr/step/result/?name=10-8",$data_second,array(
                            'jar'=> ABSPATH.'cookies1/jar4.txt',            // For getting cookies
                            'cookiefile'=> ABSPATH.'cookies1/jar2.txt'     // For sending cookies
                    )); 



                    // Get lead from the get-jar.txt file
                    $file = file_get_contents(ABSPATH.'cookies1/jar4.txt');
                    $split = explode("\n", $file);
                    $lead = trim(substr($split[9], -33));

                    if(isset($lead) && $lead != NULL){

                        // Getting price using the lead
                        $html = $this->curl("http://www.vendezvotrevoiture.fr/step/result/?name=10-8",array(),array(
                            'jar'=> ABSPATH.'cookies1/jar5.txt',             // For getting cookies
                            'cookiefile'=> ABSPATH.'cookies1/jar4.txt'        // For sending cookies
                        ));



                        // Create DOM from URL or file
                        $html = str_get_html($html);
                        if(isset($html) && $html != NULL){
                            
                            foreach($html->find('.xl') as $value) {
                                $price = $this->parseNumber($value->innertext);
                            }
                            
                            $ven_data['ven_email'] = $ven_email;
                            $ven_data['ven_price'] = $price;
                            
                           
                            
                            return $ven_data;
                        } else {
                            echo 'String is missing';
                        }

                    } else {
                       echo 'Lead is missing';
                    }
            }

            // Getting price of www.vendezvotrevoiture.fr
            function get_ven_price($con = ''){
                
                $v_price = array();
                $v_email = array();
                
                // Checking ven price for twice
                for($i = 0; $i < 2; $i++){
                
                    $ven_data = $this->ven_crawling($con);                    
                    $v_price[] = $ven_data['ven_price'];
                    $v_email[] = $ven_data['ven_email'];
                    
                }
                
                // If two crawling price for ven are same then return the price
                if($v_price[0] == $v_price[1]){
                    
                    $ven_data['ven_price'] = $v_price[0];
                    $ven_data['ven_email'] = $v_email[0];                    
                    return $ven_data;
                    
                } else {
                    
                    $ven_data = $this->ven_crawling($con);                   
                    $v_price[] = $ven_data['ven_price'];
                    $v_email[] = $ven_data['ven_email'];
                    
                    // Checking three prices 
                    // (If any of two prices are the return the price otherwise return the third price)
                    if( ($v_price[0] == $v_price[1]) || ($v_price[0] == $v_price[2]) ){
                        
                        $v_data['ven_price'] = $v_price[0];
                        $v_data['ven_email'] = $v_email[0];                   
                        return $v_data;
                        
                    } else if (($v_price[1] == $v_price[2])) {
                        
                        $v_data['ven_price'] = $v_price[1];
                        $v_data['ven_email'] = $v_email[1];                   
                        return $v_data;
                        
                    } else {
                        
                        $v_data['ven_price'] = $v_price[2];
                        $v_data['ven_email'] = $v_email[2];                   
                        return $v_data;
                        
                    }
                }
                
                //print_r($v_price);
                //print_r($v_email);
                //print_r($v_data);exit;
                
               

            }
            
        /* Vendezvotrevoiture Functions End */      
    
    function insert_price($data){          
        $this->insert($data,'crawl_data');
        return 1;       
    }
    
    function get_condition(){         
        return $this->query("SELECT * FROM `condition` WHERE `crawl_status` = 0");
    }
    
    function all_condition_status_check(){         
        $result = $this->query("SELECT * FROM `condition` WHERE `crawl_status` = 0");
        if(count($result) < 1){
            return true;
        }else {
            return false;
        }
    }
    
    function get_completed_condition(){         
        return $this->query("SELECT * FROM `condition` WHERE `crawl_status` = 1");
    }

    function get_all_condition(){         
        return $this->query("SELECT * FROM `condition`");
    }
    
    function update_condition_status($condition_id){         
        $this->query('UPDATE `condition` SET `crawl_status` = 1 WHERE `condition_id` = '.$condition_id.' ');
    }
    
    function update_cron_dates(){   
        $date = date("Y-m-d");
        $this->query("UPDATE `cron_jobs` SET `cron_jobs_date` = '". $date."' WHERE cron_jobs_id = 1");
    }
    
    function reset_condition(){   
        $this->query('UPDATE `condition` SET `crawl_status` = 0 ');
    }
    
    function get_cron_date(){   
        return $this->query('SELECT * FROM `cron_jobs`');
    }
    
    function get_crawl_data(){         
        return $this->query("SELECT * FROM `crawl_data` LEFT JOIN `condition` ON crawl_data.condition_id = condition.condition_id ");
    }
    
    function get_condition_name(){         
        return $this->query("SELECT * FROM `condition`");
    }
    
    function get_month_x_axis_data ($post){

            $from_month = $post['from_month'];
            $to_month = $post['to_month'];
            
            if( ($from_month > $to_month) || ($from_month == $to_month) ){
                $from_month = date('Y').'-'.$from_month.'-01';
                $from_month = date("Y-m-01", strtotime($from_month));
                $pd = date('Y');
                $to_month = ($pd+1).'-'.$to_month.'-01';
                $to_month = date("Y-m-t", strtotime($to_month));
            }else{
                $from_month = date('Y').'-'.$from_month.'-01';
                $from_month = date("Y-m-01", strtotime($from_month));
                $to_month = date('Y').'-'.$to_month.'-01';
                $to_month = date("Y-m-t", strtotime($to_month));
            }
            
            $condition_id = $post['condition_id'];
            if(isset($condition_id) && $condition_id[0] != 'all'){
                $condition_id = implode (", ", $condition_id);
                $where[] = "cd.condition_id IN ($condition_id)";                    
            }   
            
            if($from_month != '' && $to_month != ''){
                $where[] = "(crawl_date >= '$from_month' AND crawl_date <= '$to_month')";
            }else{

            }
            
            $where_condition = "";
            if($where){
                $where_condition = "WHERE ".implode(' AND ', $where);
            }

           $sql = "   
                    SELECT cd.crawl_id, DATE_FORMAT(crawl_date,'%Y') AS year_name, DATE_FORMAT(crawl_date,'%m') AS month_name
                    FROM `crawl_data` as cd 
                    LEFT JOIN `condition` as c ON cd.condition_id = c.condition_id 
                    $where_condition
                    GROUP BY DATE_FORMAT(crawl_date,'%Y-%m')
                ";
       // print_r($sql);exit;
        return $this->query($sql);
           
    }
    
    function get_search_data($post){ 
        //print_r($post);exit;
        
        if( isset($post['condition_id']) && isset($post['time_period']) && $post['time_period'] != '' ){
        
            $condition_id = $post['condition_id'];

            if($post['from_date'] != ''){            
                $from_date = date("Y-m-d", strtotime($post['from_date']));          
            }
            if($post['to_date'] != ''){
                $to_date = date("Y-m-d", strtotime($post['to_date']));           
            }        

            // Condition where
            if(isset($condition_id) && $condition_id[0] != 'all'){
                $condition_id = implode (", ", $condition_id);
                $where[] = "cd.condition_id IN ($condition_id)";                    
            }
            //print_r($where);exit;

            // Time period where
            if($post['time_period']){
                
                if( ($post['from_month'] != '' && $post['to_month'] != '') ) {
                    $from_month = $post['from_month'];
                    $to_month = $post['to_month'];
                    if( ($from_month > $to_month) || ($from_month == $to_month) ){
                        $from_month = date('Y').'-'.$from_month.'-01';
                        $from_month = date("Y-m-01", strtotime($from_month));
                        $pd = date('Y');
                        $to_month = ($pd+1).'-'.$to_month.'-01';
                        $to_month = date("Y-m-t", strtotime($to_month));
                    }else{
                        $from_month = date('Y').'-'.$from_month.'-01';
                        $from_month = date("Y-m-01", strtotime($from_month));
                        $to_month = date('Y').'-'.$to_month.'-01';
                        $to_month = date("Y-m-t", strtotime($to_month));
                    }
                }

                if($post['time_period'] == 'monthly'){

                    if(isset($from_month) && $from_month != '' && $to_month != '' && isset($to_month)){
                        $where[] = "(crawl_date >= '$from_month' AND crawl_date <= '$to_month')";
                    }else{

                    }

                    $sql_select = ", AVG(cd.aramisauto_price) as aramisauto_price, AVG(cd.vendezvotrevoiture_price) as vendezvotrevoiture_price, DATE_FORMAT(crawl_date,'%Y-%m') as y1";
                    //$sql_groupby = "GROUP BY YEAR(crawl_date), MONTH(crawl_date)";
                    $sql_groupby = "GROUP BY DATE_FORMAT(crawl_date,'%Y-%m'), cd.condition_id";


                } else if ($post['time_period'] == 'weekly'){

                    if(isset($from_date) && $from_date != '' && isset($to_date) && $to_date != ''){
                        $where[] = "(crawl_date >= '$from_date' AND crawl_date <= '$to_date')";
                    }else{

                    }

                    $sql_select = ", cd.aramisauto_price, cd.vendezvotrevoiture_price, DATE_FORMAT(crawl_date,'%d') as d1";
                    $sql_groupby = "";

                } else if ($post['time_period'] == 'daily'){

                    //$present_date = date('Y-m-d');
                    $where[] = "(crawl_date = CURDATE())";
                    $sql_select = ", cd.aramisauto_price, cd.vendezvotrevoiture_price";
                    $sql_groupby = "";

                } else {
                    $sql_select = ", cd.aramisauto_price, cd.vendezvotrevoiture_price";
                    $where_condition = "";
                    $sql_groupby = "";
                }   

            }

            $where_condition = "";
            if($where){
                $where_condition = "WHERE ".implode(' AND ', $where);
            }
        
        } else {
            $sql_select = ", cd.aramisauto_price, cd.vendezvotrevoiture_price";
            $where_condition = "";
            $sql_groupby = "";
        }

        $sql = "    SELECT cd.crawl_id, cd.crawl_date, cd.condition_id, cd.condition_name, cd.vend_email, c.condition_id, c.condition_name, c.ven_email,DATE_FORMAT(cd.crawl_date,'%Y') as year_name, DATE_FORMAT(cd.crawl_date,'%M') as month_name $sql_select
                    FROM `crawl_data` as cd
                    LEFT JOIN `condition` as c ON cd.condition_id = c.condition_id 
                    $where_condition $sql_groupby
                ";
        //print_r($sql);exit;
        return $this->query($sql);
        //$result = $this->query($sql);
        //print_r($result);exit;
    }
    
    function parseNumber($numberStr = '') {
        $number = 0;
        if(is_numeric($numberStr)){
            $number = (float) $numberStr;
        } else {
            $numberStr = str_ireplace('k', '000', $numberStr);
            $numberStr = str_replace(' ', '', $numberStr);
            
            $numberArr = str_split($numberStr);
            
            foreach($numberArr as $k=>$v){
                if(!is_numeric($v)){
                    unset($numberArr[$k]);
                }
            }
            
            $number = (float) implode('', $numberArr);
        }
        
        return $number;
    }
    
    function formateNumber($numberStr = '', $decimal=2, $decimalSep='', $thousandSep='') {
        $number = (float) $numberStr;
        
        if(empty($decimalSep)){
            $decimalSep = ',';
        }
        
        $number = number_format($number, $decimal, $decimalSep, $thousandSep);
        
        return $number;
    }
    
    function is_condition_completed($con){  
        $condition_id = $con['condition_id'];
        $date = date("Y-m-d");
        $result = $this->query("SELECT * FROM `crawl_data` WHERE `crawl_date` = '". $date."' AND condition_id = '".$condition_id."' ");
        if(count($result) < 1){
            return true;
        }else {
            return false;
        }
    }
    
    function generateRandomString($length = 6) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    
    function generateRandomNumber($length = 6) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    
    function getEmailAccount() {
        
        $email_account = array(
            '0' => '@gmail.com',
            '1' => '@yahoo.com',
            '2' => '@hotmail.com',
            '3' => '@live.com',
            '4' => '@outlook.com',
            '5' => '@zoho.com',
            '6' => '@sahajjo.com'
        );
        
        return $email_account[array_rand($email_account)];
        
    }
    
    function update_cron_start_time(){   
        $date = date('Y-m-d H:i:s');
        $this->query("UPDATE `cron_jobs` SET `cron_start_time` = '". $date."', `cron_end_time` = 0 WHERE cron_jobs_id = 1");
    }
    
    function update_cron_end_time(){   
        $date = date('Y-m-d H:i:s');
        $this->query("UPDATE `cron_jobs` SET `cron_end_time` = '". $date."' WHERE cron_jobs_id = 1");
    }
    
    function get_cron_data(){         
        return $this->query("SELECT * FROM `cron_jobs`");
    }


	
}
