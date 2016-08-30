<?php
require_once('config.php');
authFailRedirect();

$getPrice = new getPrice();
$crawl_data = $getPrice->get_crawl_data();
$get_condition = $getPrice->get_all_condition();
$get_cron_data = $getPrice->get_cron_data();
if($get_cron_data){
    $cron_start_time = date("d/m/Y H:i:s", strtotime($get_cron_data[0]['cron_start_time']));
    $cron_end_time = date("d/m/Y H:i:s", strtotime($get_cron_data[0]['cron_end_time']));
}
//print_r($get_cron_data);exit;
if(isset($_POST['form_action']) && $_POST['form_action']){
    
  //print_r($_POST);exit;
    if(isset($_POST['condition_id'])){
        $condition_id = $_POST['condition_id'];
    }
    if($_POST['from_date']){
        $from_date = date("d-m-Y", strtotime($_POST['from_date']));
    }
    if($_POST['to_date']){
        $to_date = date("d-m-Y", strtotime($_POST['to_date']));
    }
    if($_POST['from_month']){
        $from_month = $_POST['from_month'];
    }
    if($_POST['to_month']){
        $to_month = $_POST['to_month'];
    }
    if($_POST['time_period']){
        $time_period = $_POST['time_period'];
    }
    
    $crawl_data = $getPrice->get_search_data($_POST);
    //print_r($crawl_data);exit;
    
    //print_r($_POST);
    
    // Making graph data
    if(isset($crawl_data) && $crawl_data != ''){
        
        
        if( $_POST['time_period'] == 'monthly' && isset($_POST['from_month']) && isset($_POST['to_month']) && isset($_POST['condition_id']) ){
            
            $graph_data = $crawl_data;
            $from_month = $_POST['from_month'];
            $to_month = $_POST['to_month'];
            $count_condition = count($_POST['condition_id']);

            $month_array = array(
                '01' => 'Jan',               
                '02' => 'Feb',               
                '03' => 'Mar',               
                '04' => 'Apr',               
                '05' => 'May',               
                '06' => 'Jun',               
                '07' => 'Jul',               
                '08' => 'Aug',               
                '09' => 'Sep',               
                '10' => 'Oct',               
                '11' => 'Nov',               
                '12' => 'Dec',               
            );
            
            // X-axis data
            $x_data = $getPrice->get_month_x_axis_data($_POST);
            foreach($x_data as $xd){
                $graph_x_data[] = $month_array[$xd['month_name']];
            }
            
            // Y-axis data
            $graph_ara_y_data = array();
            for($i=0; $i<$count_condition; $i++){          
                foreach($graph_data as $key=>$val){
                    if($_POST['condition_id'][$i] ==  $val['condition_id']){

                        $graph_ara_y_data[$_POST['condition_id'][$i]]['name'] = 'AAm'.$val['condition_id'];
                        $graph_ara_y_data[$_POST['condition_id'][$i]]['data'][] = $val['aramisauto_price'];

                        $graph_ven_y_data[$_POST['condition_id'][$i]]['name'] = 'VVm'.$val['condition_id'];
                        $graph_ven_y_data[$_POST['condition_id'][$i]]['data'][] = $val['vendezvotrevoiture_price'];

                    }
                }
            }

            $g_data = array();
            if(isset($graph_ara_y_data) && isset($graph_ven_y_data)){
                foreach($graph_ara_y_data as $k1){
                    $g_data[] = $k1;
                }
                foreach($graph_ven_y_data as $k2){
                    $g_data[] = $k2;
                }
                //print_r($g_data);exit;
                $g_data = json_encode($g_data);
                $g_title = 'Monthly Price Graph';
            } else {
                $g_data = '';
            }   
            
        } else if ( $_POST['time_period'] == 'weekly' && isset($_POST['from_month']) && isset($_POST['to_month']) && isset($_POST['condition_id']) ) {
            
            $graph_data = $crawl_data;
            $from_month = $_POST['from_month'];
            $to_month = $_POST['to_month'];
            $count_condition = count($_POST['condition_id']);
            
            // X-axis data
            foreach($graph_data as $xd){
                $graph_x_data[] = $xd['crawl_date'];
            }

            // Y-axis data
            $graph_ara_y_data = array();
            for($i=0; $i<$count_condition; $i++){          
                foreach($graph_data as $key=>$val){
                    if($_POST['condition_id'][$i] ==  $val['condition_id']){

                        $graph_ara_y_data[$_POST['condition_id'][$i]]['name'] = 'AAm'.$val['condition_id'];
                        $graph_ara_y_data[$_POST['condition_id'][$i]]['data'][] = (float) $val['aramisauto_price'];

                        $graph_ven_y_data[$_POST['condition_id'][$i]]['name'] = 'VVm'.$val['condition_id'];
                        $graph_ven_y_data[$_POST['condition_id'][$i]]['data'][] = (float) $val['vendezvotrevoiture_price'];

                    }
                }
            }

            $g_data = array();
            if(isset($graph_ara_y_data) && isset($graph_ven_y_data)){
                foreach($graph_ara_y_data as $k1){
                    $g_data[] = $k1;
                }
                foreach($graph_ven_y_data as $k2){
                    $g_data[] = $k2;
                }
                //print_r($g_data);exit;
                $g_data = json_encode($g_data);
                $g_title = 'Weekly Price Graph';
            } else {
                $g_data = '';
            }   
            
        } else if ( $_POST['time_period'] == 'daily' && isset($_POST['from_month']) && isset($_POST['to_month']) && isset($_POST['condition_id']) ) {
            
            $graph_data = $crawl_data;
            $from_month = $_POST['from_month'];
            $to_month = $_POST['to_month'];
            $count_condition = count($_POST['condition_id']);
            //print_r($graph_data);exit;
            
            // X-axis data
            foreach($graph_data as $xd){
                $graph_x_data[] = $xd['ven_email'];
            }

            // Y-axis data          
            $graph_ara_y_data = array();
                foreach($graph_data as $key=>$val){                  
                    $graph_ara_y_data['name'] = 'AAm';
                    $graph_ara_y_data['data'][] = (float) $val['aramisauto_price'];

                    $graph_ven_y_data['name'] = 'VVm';
                    $graph_ven_y_data['data'][] = (float) $val['vendezvotrevoiture_price'];                  
                }

            
            $g_data = array();
            if(isset($graph_ara_y_data) && isset($graph_ven_y_data)){
                foreach($graph_ara_y_data as $k1){
                    $g_data[] = $k1;
                }
                foreach($graph_ven_y_data as $k2){
                    $g_data[] = $k2;
                }
                $g_data = '['.json_encode($graph_ara_y_data).','.json_encode($graph_ven_y_data).']';
                $g_title = 'Daily Price Graph';
            } else {
                $g_data = '';
            }   
            
        } else {
            $g_title = '';
            $graph_x_data = '';
            $g_data = '';
        }

    } else {
        $g_title = '';
        $graph_x_data = '';
        $g_data = '';
    }
    
   // print_r(json_encode($graph_x_data));exit;
    
} else {
    $condition_id = '';
    $from_date = '';
    $to_date = '';
    $g_title = '';
    $graph_x_data = '';
    $g_data = '';
}

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="" />
        <meta name="author" content="" />

        <title>AramisAuto.com :: Data</title>

        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet" />
        <link href="css/pocketgrid.min.css" rel="stylesheet" />
        <link href="css/font-awesome.min.css" rel="stylesheet" />
        <link href="js/magnific-popup/magnific-popup.css" rel="stylesheet" />
        <link href="js/sweetalert/sweet-alert.css" rel="stylesheet" />
        <link href="js/sweetalert/ie9.css" rel="stylesheet" />
        <link href="js/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" />
        


        <!-- Custom styles for this template -->
        <link href="css/custom.css" rel="stylesheet" />
        
  
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>
        <nav id="siteTopBar" class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <img class="img-responsive" src="img/logo.png" alt="AramisAuto.com" title="AramisAuto.com" />
                </div>
                <div class="pull-right">
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fa fa-power-off"></i>
                        <b>Logout</b>
                    </a>
                </div>
            </div>
        </nav>
        
        
        
        <div class="container">
            
            <div id="siteBody">
                
                <div id="siteBodyTop" class="block-group">
                    <div class="block">
                        <div class="panel panel-primary">

                            <div class="panel-body">
                                    <div class="row">

                                        <div class="col-md-12">

                                        <p class="bg-success"><b> Crawling Start Date Time: <?php echo $cron_start_time ?></b></p>                                   
                                        <p class="bg-success"><b> Crawling End Date Time: <?php if($cron_end_time == '0000-00-00 00:00:00'){ echo 'In Progress';}else{echo $cron_end_time;} ?></b></p>                                   



                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="siteBodyTop" class="block-group">
                    <div class="block">
                        <div class="panel panel-primary">

                            <div class="panel-body">
                                    <div class="row">

                                        <div class="col-md-12">

                                            <form id="filterForm" action="" class="form-inline" method="post">
                                                <input type="hidden" id="resetFilter" name="filter[reset]" value="" />

                                                    <div class="form-group">
                                                        <select id="condition_id" name="website" class="form-control crawl_select">
                                                            <option value="">Select Website</option>                                                      
                                                            <option value="all">Select All</option>                                                      
                                                            <option <?php if(isset($company_name)) echo 'selected="selected"'; ?> value="aramis">Aramisauto.com</option>                                                           
                                                            <option <?php if(isset($company_name)) echo 'selected="selected"'; ?> value="vendez">Vendezvotrevoiture.fr</option>                                                           
                                                        </select>
                                                    </div>


                                    <select class="form-control multiselect" multiple="multiple" name="condition_id[]" id="invoiceBookings">
                                        
                                        <option <?php if(isset($_POST['condition_id'][0]) && $_POST['condition_id'][0] == 'all'){ echo 'selected';} ?>  value="all">Select All Model</option>
                                        <?php foreach($get_condition as $site){ ?>
                                            <option <?php if(isset($condition_id) && is_array($condition_id) && count($condition_id) > 0 ){if(in_array($site['condition_id'],$condition_id)) echo "selected"; }?> value="<?php echo $site['condition_id']; ?>"><?php echo $site['condition_name']; ?></option>
                                        <?php } ?>
                                    </select>

                                                    <div class="form-group">
                                                        <select id="time_period" name="time_period" class="form-control" id="time_period">
                                                            <option value="">Select Time Period</option>                                                                                                         
                                                            <option <?php if(isset($time_period) && $time_period == 'monthly') echo 'selected="selected"'; ?> value="monthly">Monthly</option>                                                           
                                                            <option <?php if(isset($time_period) && $time_period == 'weekly') echo 'selected="selected"'; ?> value="weekly">Weekly</option>                                                           
                                                            <option <?php if(isset($time_period) && $time_period == 'daily') echo 'selected="selected"'; ?> value="daily">Daily</option>                                                           
                                                        </select>
                                                    </div>


                                                    <div class="form-group first_datepicker">

                                                        <select class="form-control" name="from_month" id="from_month">
                                                            <option value="">From Month</option>
                                                            <option <?php if(isset($from_month) && $from_month == '01') echo 'selected="selected"'; ?> value="01">January</option>
                                                            <option <?php if(isset($from_month) && $from_month == '02') echo 'selected="selected"'; ?>value="02">February</option>
                                                            <option <?php if(isset($from_month) && $from_month == '03') echo 'selected="selected"'; ?>value="03">March</option>
                                                            <option <?php if(isset($from_month) && $from_month == '04') echo 'selected="selected"'; ?>value="04">April</option>
                                                            <option <?php if(isset($from_month) && $from_month == '05') echo 'selected="selected"'; ?>value="05">May</option>
                                                            <option <?php if(isset($from_month) && $from_month == '06') echo 'selected="selected"'; ?>value="06">June</option>
                                                            <option <?php if(isset($from_month) && $from_month == '07') echo 'selected="selected"'; ?>value="07">July</option>
                                                            <option <?php if(isset($from_month) && $from_month == '08') echo 'selected="selected"'; ?>value="08">August</option>
                                                            <option <?php if(isset($from_month) && $from_month == '09') echo 'selected="selected"'; ?>value="09">September</option>
                                                            <option <?php if(isset($from_month) && $from_month == '10') echo 'selected="selected"'; ?>value="10">October</option>
                                                            <option <?php if(isset($from_month) && $from_month == '11') echo 'selected="selected"'; ?>value="11">November</option>
                                                            <option <?php if(isset($from_month) && $from_month == '12') echo 'selected="selected"'; ?>value="12">December</option>
                                                        </select>


                                                    <div class="form-group first_datepicker">
                                                        <select class="form-control" name="to_month" id="to_month">
                                                                 <option value="">From Month</option>
                                                            <option <?php if(isset($to_month) && $to_month == '01') echo 'selected="selected"'; ?> value="01">January</option>
                                                            <option <?php if(isset($to_month) && $to_month == '02') echo 'selected="selected"'; ?>value="02">February</option>
                                                            <option <?php if(isset($to_month) && $to_month == '03') echo 'selected="selected"'; ?>value="03">March</option>
                                                            <option <?php if(isset($to_month) && $to_month == '04') echo 'selected="selected"'; ?>value="04">April</option>
                                                            <option <?php if(isset($to_month) && $to_month == '05') echo 'selected="selected"'; ?>value="05">May</option>
                                                            <option <?php if(isset($to_month) && $to_month == '06') echo 'selected="selected"'; ?>value="06">June</option>
                                                            <option <?php if(isset($to_month) && $to_month == '07') echo 'selected="selected"'; ?>value="07">July</option>
                                                            <option <?php if(isset($to_month) && $to_month == '08') echo 'selected="selected"'; ?>value="08">August</option>
                                                            <option <?php if(isset($to_month) && $to_month == '09') echo 'selected="selected"'; ?>value="09">September</option>
                                                            <option <?php if(isset($to_month) && $to_month == '10') echo 'selected="selected"'; ?>value="10">October</option>
                                                            <option <?php if(isset($to_month) && $to_month == '11') echo 'selected="selected"'; ?>value="11">November</option>
                                                            <option <?php if(isset($to_month) && $to_month == '12') echo 'selected="selected"'; ?>value="12">December</option>
                                                       
                                                        </select>
                                                    </div> 


                                                    </div> 



                                                        <div class="form-group second_datepicker">
                                                            <div class="input-group daterange">
                                                                <input id="fromDate" type="text" name="from_date" class="form-control" placeholder="dd-mm-yyyy" value="<?php if(isset($from_date)){ echo $from_date; } ?>" />
                                                                <span class="add-on input-group-addon">to</span>
                                                                <input id="toDate" type="text" name="to_date" class="form-control" placeholder="dd-mm-yyyy" value="<?php if(isset($to_date)){ echo $to_date; } ?>" />
                                                            </div>
                                                        </div>




                                                    <button id="filterBtn" type="submit" class="btn btn-primary"><i class="fa fa-search"></i><b> Filter</b></button>
                                                    <button id="form_clear" class="btn btn-danger"><i class="fa fa-remove"></i><b> Clear</b></button>
                                                    <button id="filterExportBtn" type="button" class="btn btn-success"><i class="fa fa-download"></i> <b>Export</b></button>
                                                    <input type="hidden" name="form_action" value="1" />


                                            </form>

                                            <form id="filterExportForm" action="export_data.php" class="form-inline" method="post">
                                                <input type="hidden" name="export" value="1" />
                                                <input id="export_condition_id" type="hidden" name="export_condition_id" value="" />
                                                <input type="hidden" id="filterExportFrom" name="export_from_date" value="" />
                                                <input type="hidden" id="filterExportTo" name="export_to_date" value="" />
                                                
                                                <input type="hidden" id="export_from_month" name="export_from_month" value="" />
                                                <input type="hidden" id="export_to_month" name="export_to_month" value="" />
                                                <input type="hidden" id="export_time_period" name="export_time_period" value="" />
                                            </form>


                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Showing graph -->
                <?php  if(  isset($g_data) && $g_data != '' && isset($graph_x_data) && $graph_x_data != ''){ ?>
                <div id="siteBodyTop" class="block-group">
                    <div class="block">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php  } ?>
            
            </div>
   

        <div>
            <div id="siteBodyTop" class="block-group">
                <div class="block">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><b>Crawl Data</b></div>
                        <div class="panel-body">
                                <div class="row">
                                   
                                    <div class="col-md-12">
                                       <table class="table table-striped">
                                            <thead>
                                              <tr>
                                                
                                                
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    <th>Year</th>
                                                <?php }else{ ?>
                                                    <th>#</th>
                                                <?php } ?>
                                                
                                                
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    <th>Month</th>
                                                <?php }else{ ?>
                                                    <th>Crawl Date</th>
                                                <?php } ?>
                                                    
                                                <th>Condition Name</th>
                                                
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    <th>Aramisauto Price (AVG)</th>
                                                <?php }else{ ?>
                                                    <th>Aramisauto Price</th>
                                                <?php } ?>
                                               
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    <th>Vendezvotrevoiture Price (AVG)</th>
                                                <?php }else{ ?>
                                                     <th>Vendezvotrevoiture Price</th>
                                                <?php } ?>
                                                  
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    
                                                <?php }else{ ?>
                                                     <th>Ven Email</th>
                                                <?php } ?>
                                                     
                                                
                                                
                                              </tr>
                                            </thead>
                                            <tbody>
                                              <?php if(isset($crawl_data) && $crawl_data != NULL){foreach($crawl_data as $crawl){ ?>  
                                              <tr>
                                                
                                                
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    <td><?php echo $crawl['year_name']; ?></td>
                                                <?php }else{ ?>
                                                   <th scope="row"> <?php echo $crawl['crawl_id']; ?>  </th>
                                                <?php } ?>
                                                
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    <td><?php echo $crawl['month_name']; ?></td>
                                                <?php }else{ ?>
                                                    <td><?php echo $crawl['crawl_date']; ?></td>
                                                <?php } ?>
                                                
                                                <td><?php echo $crawl['condition_name']; ?></td>
                                                <td>€ <?php echo $getPrice->formateNumber($crawl['aramisauto_price']); ?></td>
                                                <td>€ <?php echo $getPrice->formateNumber($crawl['vendezvotrevoiture_price']); ?></td>
                                               
                                                
                                                <?php if(isset($_POST['time_period']) && $_POST['time_period'] == 'monthly'){ ?>
                                                    
                                                <?php }else{ ?>
                                                    <td><?php echo $crawl['vend_email']; ?></td>
                                                <?php } ?>
                                                
                                              </tr> 
                                              <?php }} else { ?>
                                                  
                                                <tr>
                                                    <th></th>
                                                    <th> <?php echo 'No Data Is Available'; ?>  </th>                                                   
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>  
                                                  
                                              <?php }?>
                                            </tbody>
                                          </table>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
            
            
        <div id="accordion">
          <?php 
            $condition = $getPrice->get_condition_name();
            foreach($condition as $con){
                $field_name = explode("|", $con['condition_field']);
                $aramis_condition = explode("|", $con['aramis_condition']);
                $ven_condition = explode("|", $con['ven_condition']);
          ?>  
                <h3><?php echo $con['condition_name']; ?></h3>
                <div>
                    <table class="table table-striped">
                        <thead>
                          <tr>

                            <th>Field Name</th>
                            <th>Aramisauto Condition</th>
                            <th>Vendezvotrevoiture Condition</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if(isset($field_name) && $field_name != NULL){
                              
                              foreach($field_name as $key=>$field_name){ ?>  
                          <tr>
                            <th scope="row"> <?php echo $field_name; ?>  </th>
                            <td><?php echo $aramis_condition[$key]; ?></td>
                            <td><?php echo $ven_condition[$key]; ?></td>
                          </tr> 
                          <?php }
                          
                              } else{ echo 'Nodata Available'; }?>
                        </tbody>
                      </table>
                </div>
          <?php } ?>
        </div>

            
        </div>    
        
        <script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/jquery.form.min.js"></script>
        <script type="text/javascript" src="js/magnific-popup/jquery.magnific-popup.min.js"></script>
        <script type="text/javascript" src="js/sweetalert/sweet-alert.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        
        <!-- Accordion -->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        
        <!-- Bootstrap Multi select -->
        <script src="js/multiselect/js/bootstrap-multiselect.js"></script>
        <link rel="stylesheet" href="js/multiselect/css/bootstrap-multiselect.css">
        
        <!-- High chart -->
        <!--<script src="js/highchart/exporting.js"></script>-->
        <script src="js/highchart/highcharts.js"></script>
        
        <script type="text/javascript">
            

            
                $(function () {
                    
                
                    var graph_x_data = <?php echo json_encode($graph_x_data) ?>;
                    var graph_av_y_data = <?php echo $g_data; ?>;
                    var g_title = "<?php echo $g_title; ?>";

                    $('#container').highcharts({
                        
                      
                        
                        title: {
                            text: g_title,
                            x: -20 //center
                        },
                        subtitle: {
                            text: 'Source: aramisauto.com and vendezvotrevoiture.com',
                            x: -20
                        },
                        
                        xAxis: {
                            categories: graph_x_data
                        },
                        yAxis: {
                            title: {
                                text: 'Price'
                            },
                            plotLines: [{
                                value: 0,
                                width: 1,
                                color: '#808080'
                            }]
                        },
                        tooltip: {
                            valueSuffix: '€'
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle',
                            borderWidth: 0
                        },
                        series: graph_av_y_data
                    });
                });
        </script>

        
        
        <script>
            $(function() {
              $( "#accordion" ).accordion({ header: "h3", collapsible: true, active: false });
            });
        </script>
        
        <script type="text/javascript">
            
            
            jQuery(function(){
                
                 jQuery('.first_datepicker').hide();
                 jQuery('.second_datepicker').hide();
                //$('#datepicker').datepicker({ dateFormat: 'dd-mm-yy' }).val();
                
                
                
                jQuery('#fromDate').datepicker({
                    dateFormat: 'dd-mm-yy',
                    autoclose: true,
                    todayBtn: "linked"
                });
                
                jQuery('#toDate').datepicker({
                    dateFormat: 'dd-mm-yy',
                    autoclose: true,
                    todayBtn: "linked"
                });
                
                jQuery('body').delegate('#form_clear', "click", function(e){
                    e.preventDefault();
                    jQuery('#condition_id').val('');
                    jQuery('#fromDate').val('');
                    jQuery('#toDate').val('');
                    
                    window.location.href = 'data.php';
                    
                });
                
                    jQuery('body').delegate('#time_period','change', function(e){

                        var time_period = jQuery(this).val();;
                        jQuery('#export_time_period').val(time_period);                     
                        
                        if(time_period == 'monthly'){
                            jQuery('.first_datepicker').show();
                            jQuery('.second_datepicker').hide();
                        } else if(time_period == 'weekly'){
                            jQuery('.second_datepicker').show();
                            jQuery('.first_datepicker').hide();
                        } else if(time_period == 'daily'){
                            jQuery('.second_datepicker').hide();
                            jQuery('.first_datepicker').hide();
                        } else {
                           // Do nothing 
                        } 
                        
                        var from_month = jQuery('#from_month').val();
                        jQuery('#export_from_month').val(from_month);
                        
                        var to_month = jQuery('#to_month').val();
                        jQuery('#export_to_month').val(to_month);
                        
                        var invoiceBookings = jQuery('#invoiceBookings').val();
                        jQuery('#export_condition_id').val(invoiceBookings);
                        
                        var fromDate = jQuery('#fromDate').val();
                        jQuery('#fromDate').val(fromDate);
                        
                        var toDate = jQuery('#toDate').val();
                        jQuery('#toDate').val(toDate);
                        
                    });
                
                    jQuery('#time_period').trigger('change');              

                    jQuery('body').delegate('#from_month', "change", function(e){
                        var condition_id = jQuery('#from_month').val();
                        jQuery('#export_from_month').val(condition_id);
                    });


                    jQuery('body').delegate('#to_month', "change", function(e){
                        var condition_id = jQuery('#to_month').val();
                        jQuery('#export_to_month').val(condition_id);
                    });
                
                
                jQuery('body').delegate('#invoiceBookings', "change", function(){
                    var condition_id = jQuery(this).val();
                    jQuery('#export_condition_id').val(condition_id);
                });
                
                    jQuery('body').delegate('#fromDate', "change", function(){
                        var fromDate = jQuery(this).val();
                        jQuery('#filterExportFrom').val(fromDate);
                    });

                    jQuery('body').delegate('#toDate', "change", function(){
                        var toDate = jQuery(this).val();
                        jQuery('#filterExportTo').val(toDate);
                    });
                
                jQuery('#filterExportBtn').click(function(e){
                    jQuery('#filterExportForm').trigger('submit');
                });
                
                
                jQuery('#invoiceBookings').multiselect({
                        enableCaseInsensitiveFiltering: true,
                        numberDisplayed:0		
                });
                   
            
            });
        </script>
        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="js/ie10-viewport-bug-workaround.js"></script>
    </body>
</html>