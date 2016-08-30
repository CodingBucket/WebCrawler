<?php 

    error_reporting(E_ALL ^ E_NOTICE);
	
    function dd ($parem) {  // Debug function
        echo'<pre>';
        print_r($parem);
        echo'</pre>';
    }
    
    function de ($parem) {  // Debug function with exit
        echo'<pre>';
        print_r($parem);
        echo'</pre>';
        exit;
    }
    
    function ck($t=null){   // Check function
        if(!empty($t)){
            echo $t;exit;
        } else {
            echo '1';exit;
        }
        
    }

?>
