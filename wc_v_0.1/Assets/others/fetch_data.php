<?php

require_once('config.php');

//$crawlApi = new crawlApi();
$modification = new modification();

ini_set('max_execution_time', 0);

/*
Beepi > site_id = 1
Carvana > site_id = 2
Vroom > site_id = 3
vendezvotrevoiture > site_id = 4
*/
 
if(isset($_GET['site_id']) && $_GET['site_id'] == 4 ){
    
  

                    $data_arr = array(
                        'step1[manufacturer]'=>"Renault",
                        'step1[maintype]'=>"Scénic",
                        'step1[builtDate]'=>"Monospace",
                    );
                    
                    $data_url_1 = $modification->postdata_url($data_arr);
                    $data = str_replace(' ','+',$data_url_1);
                    

                    echo $modification->httpcurl("https://www.aramisauto.com/reprise/vos-coordonnees/check?",$data);

            
            
            if($crawlDateInfo['finished'] == 1){
                $crawlApi->addCrawlsSales($site_id);
                
                $crawlDateCalculateInfo = $crawlApi->getCrawlDateCalculateInfo($site_id);
                
                $crawlDateInfo['online_car_no'] = isset($crawlDateCalculateInfo['online_car_no'])?$crawlDateCalculateInfo['online_car_no']:0;
                $crawlDateInfo['avg_online_car_price'] = isset($crawlDateCalculateInfo['avg_online_car_price'])?$crawlDateCalculateInfo['avg_online_car_price']:0;
                $crawlDateInfo['avg_online_car_mileage'] = isset($crawlDateCalculateInfo['avg_online_car_mileage'])?$crawlDateCalculateInfo['avg_online_car_mileage']:0;
                $crawlDateInfo['avg_online_car_age'] = isset($crawlDateCalculateInfo['avg_online_car_age'])?$crawlDateCalculateInfo['avg_online_car_age']:0;
                $crawlDateInfo['avg_stock_age'] = isset($crawlDateCalculateInfo['avg_stock_age'])?$crawlDateCalculateInfo['avg_stock_age']:0;
                
                $crawlDateInfo['sales_car_no'] = isset($crawlDateCalculateInfo['sales_car_no'])?$crawlDateCalculateInfo['sales_car_no']:0;
                $crawlDateInfo['avg_sales_car_price'] = isset($crawlDateCalculateInfo['avg_sales_car_price'])?$crawlDateCalculateInfo['avg_sales_car_price']:0;
                $crawlDateInfo['avg_sales_car_mileage'] = isset($crawlDateCalculateInfo['avg_sales_car_mileage'])?$crawlDateCalculateInfo['avg_sales_car_mileage']:0;
                $crawlDateInfo['avg_sales_car_age'] = isset($crawlDateCalculateInfo['avg_sales_car_age'])?$crawlDateCalculateInfo['avg_sales_car_age']:0;
                $crawlDateInfo['avg_stock_rotation'] = isset($crawlDateCalculateInfo['avg_stock_rotation'])?$crawlDateCalculateInfo['avg_stock_rotation']:0;
                
                $crawlDateInfo['avg_sales_car_repricing'] = isset($crawlDateCalculateInfo['avg_sales_car_repricing'])?$crawlDateCalculateInfo['avg_sales_car_repricing']:0;
                $crawlDateInfo['avg_sales_car_repricing_no'] = isset($crawlDateCalculateInfo['avg_sales_car_repricing_no'])?$crawlDateCalculateInfo['avg_sales_car_repricing_no']:0;
                $crawlDateInfo['sales_car_with_repricing_no'] = isset($crawlDateCalculateInfo['sales_car_with_repricing_no'])?$crawlDateCalculateInfo['sales_car_with_repricing_no']:0;
            }
            
            $crawlApi->saveCrawlDateInfo($crawlDateInfo);
       
    
}

if(isset($_GET['site_id1']) && $_GET['site_id1']){
    $site_id = (int) $_GET['site_id'];
    
    if(in_array($site_id, array(1,2,3,4))){
        $crawlDateInfo = $crawlApi->getCurrentCrawlDateInfo($site_id);
        
        if($crawlDateInfo['finished'] != 1){
            if($site_id == 1){
                $runLoop = 5;
                while($runLoop){
                    $beepiArgs = array(
                        'PageId'=>++$crawlDateInfo['page'],
                        'SearchQueryId'=>6
                    );

                    $beepiData = $crawlApi->curl('https://www.beepi.com/v1/listings/carsPageResults', $beepiArgs);

                    $beepiDataArr = json_decode($beepiData,true);

                    if($beepiDataArr && isset($beepiDataArr['carsOnSale']) && $beepiDataArr['carsOnSale']){
                        $crawlDateInfo['found_cars'] = $crawlDateInfo['found_cars'] + count($beepiDataArr['carsOnSale']);

                        foreach($beepiDataArr['carsOnSale'] as $fetchKey => $fetchData){
                            if(isset($fetchData['saleId']) && $fetchData['saleId']){
                                $crawlData = array(
                                    'site_id' => $site_id,
                                    'stock_id' => $fetchData['saleId'],
                                    'vin' => isset($fetchData['vin'])?$fetchData['vin']:'',
                                    'make' => isset($fetchData['makeName'])?$fetchData['makeName']:'',
                                    'model' => isset($fetchData['modelName'])?$fetchData['modelName']:'',
                                    'year' => isset($fetchData['year'])?$fetchData['year']:'',
                                    'price' => isset($fetchData['salePrice'])?$fetchData['salePrice']:'',
                                    'mileage' => isset($fetchData['mileage'])?$fetchData['mileage']:0,
                                    'autocheck_url' => 'https://www.beepi.com/Buy/AutoCheckReport.ashx?SaleId='.$fetchData['saleId'],
                                );

                                $crawlApi->addCrawl($crawlData);
                            }
                        }
                        $runLoop--;
                    } else {
                        $crawlDateInfo['finished'] = 1;
                        $runLoop = 0;
                    }
                }
            } else if($site_id == 2) {
                $runLoop = 5;
                while($runLoop){
                    $carvanaArgs = array(
                        'BodyStyle' => array(),
                        'Color' => array(),
                        'DownPayment' => null,
                        'DriveTrain' => array(),
                        'Features' => null,
                        'FilterToExclude' => null,
                        'MileageMax' => null,
                        'Models' => array(),
                        'MonthlyPayment' => null,
                        'Page' => ++$crawlDateInfo['page'],
                        'PriceMax' => null,
                        'PriceMin' => null,
                        'SortBy' => "Newest",
                        'YearMax' => null,
                        'YearMin' => null
                    );

                    $carvanaData = $crawlApi->curl('http://www.carvana.com/search/runsearch', $carvanaArgs);

                    $carvanaDataArr = json_decode($carvanaData,true);

                    if($carvanaDataArr && isset($carvanaDataArr['results']) && $carvanaDataArr['results']){
                        $crawlDateInfo['found_cars'] = $crawlDateInfo['found_cars'] + count($carvanaDataArr['results']);

                        foreach($carvanaDataArr['results'] as $fetchKey => $fetchData){
                            if(isset($fetchData['StockNumber']) && $fetchData['StockNumber']){
                                $fetchCarInfo = $crawlApi->curl('http://www.carvana.com/api/vehicle-details?stockNumber='.$fetchData['StockNumber']);

                                $carDataArr = json_decode($fetchCarInfo,true);

                                $crawlData = array(
                                    'site_id' => $site_id,
                                    'stock_id' => $fetchData['StockNumber'],
                                    'vin' => isset($carDataArr['features']['vin'])?$carDataArr['features']['vin']:'',
                                    'make' => isset($carDataArr['vehicle']['make'])?$carDataArr['vehicle']['make']:'',
                                    'model' => isset($carDataArr['vehicle']['model'])?$carDataArr['vehicle']['model']:'',
                                    'year' => isset($carDataArr['vehicle']['year'])?$carDataArr['vehicle']['year']:'',
                                    'price' => isset($carDataArr['vehicle']['price'])?$carDataArr['vehicle']['price']:'',
                                    'mileage' => isset($carDataArr['vehicle']['mileage'])?$carDataArr['vehicle']['mileage']:0,
                                    'autocheck_url' => 'http://www.carvana.com/search/vehiclehistoryreport?stocknumber='.$fetchData['StockNumber'],
                                );

                                $crawlApi->addCrawl($crawlData);
                            }
                        }
                        $runLoop--;
                    } else {
                        $crawlDateInfo['finished'] = 1;
                        $runLoop = 0;
                    }
                }
            } else if($site_id == 3) {
                
                function phpCurl($url = '', $post = array(), $params = array()) {
                    $resp = '';

                    if ($url) {
                        $url = str_replace(' ','+',$url);
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
                        //curl_setopt($ch, CURLOPT_HEADER, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                        if ($post) {
                            $post_str = '';
                            if(is_array($post)){
                                $post_str = http_build_query($post);
                            }
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
                        }
                        if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
                            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                        }

                        if(isset($params['cookiefile']) && $params['cookiefile'] && file_exists($params['cookiefile'])){
                            curl_setopt($ch, CURLOPT_COOKIEFILE, $params['cookiefile']);
                        }

                        $content = curl_exec($ch);
                        //$response = curl_getinfo($ch);

                        //pr($response);

                        curl_close($ch);

                        $resp = $content;
                    }

                    return $resp;
                }
                

                function getAutoCheckReport($url = '', $post = array(), $params = array()){
                    $AutoCheckReport = array(
                        'first_reg_date' => ''
                    );

                    //pr($url);

                    $htmlData = trim(phpCurl($url, $post, $params));

                //    pr($htmlData);
                //    exit();

                    if($htmlData){
                        $htmlObj = str_get_html($htmlData);

                        $first_reg_date = '';

                        $isTr = false;
                        foreach($htmlObj->find('#full-history-list tr') as $element){
                            if($isTr){
                                $rowObj = str_get_html($element->innertext);
                                $eventDateTime = trim($rowObj->find('td',0)->innertext);
                                if($eventDateTime){
                                    $eventDateTimeArr = explode('/', $eventDateTime);
                                    if(isset($eventDateTimeArr[0]) && isset($eventDateTimeArr[1]) && isset($eventDateTimeArr[2])){
                                        $odometerReading = $rowObj->find('td',2)->innertext;
                                        $odometerReading = str_ireplace('<div>', '', $odometerReading);
                                        $odometerReading = str_ireplace('</div>', '', $odometerReading);
                                        $odometerReading = str_replace(',', '', $odometerReading);
                                        $odometerReading = str_replace(' ', '', $odometerReading);
                                        $odometerReading = str_replace('\n', '', $odometerReading);
                                        $odometerReading = trim($odometerReading);
                                        if($odometerReading)  {
                                            $odometerReading = (float) $odometerReading;
                                            if ($odometerReading < 300) {
                                                $first_reg_date = $eventDateTimeArr[2] . "-" . $eventDateTimeArr[0] . "-" . $eventDateTimeArr[1];
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                            $isTr = true;
                        }

                        if($first_reg_date){
                            $AutoCheckReport['first_reg_date'] = $first_reg_date;
                        }
                    }

                    return $AutoCheckReport;
                }
                
                $runLoop = 2;
                while($runLoop){           
                    $page = ++$crawlDateInfo['page'];

                    $vroomArgs = array(
                        'PageSize' => 25,
                        'SkipVehiclesAmount' => 0
                    );

                    $vroomArgs['SkipVehiclesAmount'] = $vroomArgs['PageSize'] * ($page - 1);

                    $vroomData = $crawlApi->curl('https://www.vroom.com/catalog', $vroomArgs);

                    $vroomDataArr = json_decode($vroomData,true);

                    if($vroomDataArr && isset($vroomDataArr['Data']['Vehicles']) && $vroomDataArr['Data']['Vehicles']){
                        $crawlDateInfo['found_cars'] = $crawlDateInfo['found_cars'] + count($vroomDataArr['Data']['Vehicles']);

                        $autoCheckVins = array();
                        foreach($vroomDataArr['Data']['Vehicles'] as $fetchKey => $fetchData){
                            if(isset($fetchData['PageUrl']) && $fetchData['PageUrl']){
                                $fetchCarInfo = $crawlApi->curl('https://www.vroom.com'.$fetchData['PageUrl']);

                                $vin = '';
                                
                                preg_match("/<span class=\"product-row-value\" itemprop=\"productID\">([^`]*?)<\/span>/", $fetchCarInfo, $vin_matches);

                                if(isset($vin_matches[1]) && $vin_matches[1]){
                                    $vin = trim($vin_matches[1]);
                                }
                                
                                if($vin){
                                    $crawlData = array(
                                        'site_id' => $site_id,
                                        'stock_id' => $fetchData['StockId'],
                                        'vin' => $vin,
                                        'make' => isset($fetchData['Make'])?$fetchData['Make']:'',
                                        'model' => isset($fetchData['Model'])?$fetchData['Model']:'',
                                        'year' => isset($fetchData['Year'])?$fetchData['Year']:'',
                                        'price' => isset($fetchData['Price'])?$fetchData['Price']:'',
                                        'mileage' => isset($fetchData['Mileage'])?$fetchData['Mileage']:0,
                                        //'autocheck_url' => 'http://team.vroom.com/Home/AutoCheck?vin='.$vin,
                                    );

                                    $crawlDataFinal = $crawlApi->addCrawl($crawlData);
                                    
                                    if(isset($crawlDataFinal['car_id']) && $crawlDataFinal['car_id']){
                                        $autoCheckVins[$crawlDataFinal['car_id']] = $vin;
                                    }
                                }
                            }
                        }
                        
                        if($autoCheckVins){
                            foreach($autoCheckVins as $car_id=>$vin){
                                $AutoCheckReport = getAutoCheckReport('http://team.vroom.com/Home/AutoCheck?vin='.$vin);
                                
//                                $crawlApi->pr($car_id);
//                                $crawlApi->pr($vin);
//                                $crawlApi->pr($AutoCheckReport);
                                
                                if(isset($AutoCheckReport['first_reg_date']) && $AutoCheckReport['first_reg_date']){
                                    $crawlApi->updateFirstRegDate($AutoCheckReport['first_reg_date'], array('id' => $car_id));
                                }
                            }
                        }
                        
                        $runLoop--;
                    } else {
                        $crawlDateInfo['finished'] = 1;
                        $runLoop = 0;
                    }
                }
            }
            
            if($crawlDateInfo['finished'] == 1){
                $crawlApi->addCrawlsSales($site_id);
                
                $crawlDateCalculateInfo = $crawlApi->getCrawlDateCalculateInfo($site_id);
                
                $crawlDateInfo['online_car_no'] = isset($crawlDateCalculateInfo['online_car_no'])?$crawlDateCalculateInfo['online_car_no']:0;
                $crawlDateInfo['avg_online_car_price'] = isset($crawlDateCalculateInfo['avg_online_car_price'])?$crawlDateCalculateInfo['avg_online_car_price']:0;
                $crawlDateInfo['avg_online_car_mileage'] = isset($crawlDateCalculateInfo['avg_online_car_mileage'])?$crawlDateCalculateInfo['avg_online_car_mileage']:0;
                $crawlDateInfo['avg_online_car_age'] = isset($crawlDateCalculateInfo['avg_online_car_age'])?$crawlDateCalculateInfo['avg_online_car_age']:0;
                $crawlDateInfo['avg_stock_age'] = isset($crawlDateCalculateInfo['avg_stock_age'])?$crawlDateCalculateInfo['avg_stock_age']:0;
                
                $crawlDateInfo['sales_car_no'] = isset($crawlDateCalculateInfo['sales_car_no'])?$crawlDateCalculateInfo['sales_car_no']:0;
                $crawlDateInfo['avg_sales_car_price'] = isset($crawlDateCalculateInfo['avg_sales_car_price'])?$crawlDateCalculateInfo['avg_sales_car_price']:0;
                $crawlDateInfo['avg_sales_car_mileage'] = isset($crawlDateCalculateInfo['avg_sales_car_mileage'])?$crawlDateCalculateInfo['avg_sales_car_mileage']:0;
                $crawlDateInfo['avg_sales_car_age'] = isset($crawlDateCalculateInfo['avg_sales_car_age'])?$crawlDateCalculateInfo['avg_sales_car_age']:0;
                $crawlDateInfo['avg_stock_rotation'] = isset($crawlDateCalculateInfo['avg_stock_rotation'])?$crawlDateCalculateInfo['avg_stock_rotation']:0;
                
                $crawlDateInfo['avg_sales_car_repricing'] = isset($crawlDateCalculateInfo['avg_sales_car_repricing'])?$crawlDateCalculateInfo['avg_sales_car_repricing']:0;
                $crawlDateInfo['avg_sales_car_repricing_no'] = isset($crawlDateCalculateInfo['avg_sales_car_repricing_no'])?$crawlDateCalculateInfo['avg_sales_car_repricing_no']:0;
                $crawlDateInfo['sales_car_with_repricing_no'] = isset($crawlDateCalculateInfo['sales_car_with_repricing_no'])?$crawlDateCalculateInfo['sales_car_with_repricing_no']:0;
            }
            
            $crawlApi->saveCrawlDateInfo($crawlDateInfo);
        }
    }
}