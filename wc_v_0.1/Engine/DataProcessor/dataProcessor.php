<?php
// DataProcessor Engine

// Required File For DataProcessor 
require_once('saveData.php');
require_once(ENGINEROOT.'Common/commonMethods.php');

//define('FILEPATH', dirname(__FILE__).'/');

class dataProcessor extends saveData {
    
    
    /* Regex for matching a consonant */

    
    public $check = '1';

    public $Common;

	public function __construct() {
		$this->Common = new commonMethods;
		$this->db = new objMysqliDb;
	}

    public function processRepoData(){       // DP1

        $links = $this->Common->getLinks();
		//de($links);
        
        foreach($links as $val){
			$check_link = $this->Common->linkCrawlStatus($val);
			if($check_link == ture){
				$this->convertHtmlRepo($val);
			}
        }

    } 

    public function convertHtmlRepo($link_info){  // DP1.1
     
        $link_id = $link_info['link_id'];
        $link = $link_info['link'];
        $page_html = file_get_contents('Repo/PageHtmlRepo/'.$link_id.'_page_html.html');
        
        $page_doc = $this->cleanUrl($page_html,$link);
        
        // DP1.1.1
        $this->insertDataInDocRepo($page_doc,$link_id); 
        
    }
    
    // DP1.1.1
    public function insertDataInDocRepo($page_doc,$link_id){
        
        $page_content = array(
                'content' =>  $page_doc['content'],
                'title' => $page_doc['title'],
                'description' => $page_doc['description'],
                'keywords' => $page_doc['cokeywordsntent'],
                'host' => $page_doc['host'],
                'path' => '/',
                'nofollow' => '',
                'noindex' => '',
                'base' => ''
        );  
        
        $serializedData = serialize($page_content); 
        file_put_contents('Repo/PageDocRepo/DocContent/'.$link_id.'_doc_content.txt', '');
        file_put_contents('Repo/PageDocRepo/DocContent/'.$link_id.'_doc_content.txt', $serializedData);
    
        $page_content['fulltext'] = $page_doc['fulltext'];  
        //de($page_content);
        
        file_put_contents('Repo/PageDocRepo/FullContent/'.$link_id.'_full_content.txt', '');
        file_put_contents('Repo/PageDocRepo/FullContent/'.$link_id.'_full_content.txt', serialize($page_content));

            
            
    }

    // DP2
    public function getDataFromRepo($link_info){
        $link_id = $link_info['link_id']; 
        $recoveredData = file_get_contents('Repo/PageDocRepo/DocContent/'.$link_id.'_doc_content.txt');
        $page_doc = unserialize($recoveredData); 
        return $page_doc;
    }
    
    // DP3
    public function savePageDoc($page_doc){

        if(!empty($page_doc)){

			$page_doc['content'] = $this->removeBackSlashFormString($page_doc['content']);
            
			$data = array(
			   'page_link' => $page_doc['host'],
			   'page_title' => $page_doc['title'],
			   'page_keywords' => $page_doc['keywords'],
			   'page_description' => $page_doc['description'],
			   'page_content' => $page_doc['content']
			);

			// Insert Data In Links Table
			$last_insert_id = $this->savePageContent($data);
			return $last_insert_id;

        }  else {
            echo 'E2: page_doc is empty on';exit;
        }    
                
    } 
    
    // DP4
    public function processPageDocIndex($page_doc, $page_id){
        
        if(!empty($page_doc)){           
            
            // 3.1
            $page_doc['content'] = $this->removeApostrpisFormDoc($page_doc);
            
            // 3.2
            $unique_page_words = $this->getUniqueWords($page_doc);  
            
            // 3.3
            $joining_words = $this->getJoiningWords();  
            
            // 3.4
            $fresh_words = $this->removeJoiningWords($unique_page_words,$joining_words); 
            
            return $fresh_words;
                     
                
        } else {
            echo 'E3: page_doc is empty';exit; 
        }   
    
                
    }

	public function removeBackSlashFormString($string){
		$string = stripslashes($string);
		return $string;
	}
    
    // DP4.1
    public function removeApostrpisFormDoc($page_doc){        
        $doc = str_replace("'s","", $page_doc['content']);  
        $doc = str_replace("/","", $doc);
        $doc = stripslashes($doc);
        //de($doc);
        return $doc;               
    }
    
    // DP4.2
    public function getUniqueWords($page_doc){        
        $words_array = explode(' ', $page_doc['content']);  
        $unique_words = array_unique($words_array);
        return $unique_words;               
    }
    
    // DP4.3
    public function getJoiningWords(){
        $file = file_get_contents(ENGINEROOT.'DataProcessor/TextFiles/CommonWords.txt');
        $split = explode(",", $file);
        //de($split);
        return $split;
    }
    
    // DP4.4
    public function removeJoiningWords($unique_page_words,$joining_words){
        $unique_page_words = array_diff($unique_page_words, $joining_words);
        return $unique_page_words;
    }
    
    // DP5
    public function savePageDocIndex($fresh_words,$page_id){      

        if(!empty($fresh_words) && !empty($page_id)){
            //dd($fresh_words);
            //$page_id = 5;
                
                foreach($fresh_words as $k=>$v){
                    
                    if(!empty($v)){
                        
                        $page_doc = $this->getPageDoc($v);
                        
                        // IF doc does not exist in db (Insert Doc)
                        if(empty($page_doc)){
                            $page_doc_count = 1;
                            $doc_page_ids = array(
                                $page_id => $page_doc_count
                            );
                            $doc_page_ids = serialize($doc_page_ids); 

                            $data = array(
                                'doc' => $v,
                                'doc_page_ids' => $doc_page_ids
                            );

                            $this->insertPagedocIndex($data);  
                        
                        //  IF doc exist in db  (Update Doc)
                        } else {
                            
                            $doc_page_ids = unserialize($page_doc[0]['doc_page_ids']);
                           
                            // IF page_id exist for the doc (Update page_id for doc)
                            if (array_key_exists($page_id, $doc_page_ids)) {
                                
                                // Increase doc_count
                                $doc_page_ids[$page_id] = $doc_page_ids[$page_id] + 1;
                                
                                $doc_page_ids = serialize($doc_page_ids); 

                                $data = array(
                                    'doc' => $v,
                                    'doc_page_ids' => $doc_page_ids
                                );

                                $this->updatePagedocIndex($data);  
                            
                            // IF page_id does not exist for the doc  (Insert page_id for doc)
                            } else {
                                
                                $doc_page_ids[$page_id] = 1;
                                
                                $doc_page_ids = serialize($doc_page_ids);
                                
                                $data = array(
                                    'doc' => $v,
                                    'doc_page_ids' => $doc_page_ids
                                );

                                $this->updatePagedocIndex($data); 
                                
                            }
                            //de($doc_page_ids);
                            
                        }
                       
                    }
                   
                }

        }  else {
            echo 'E4';exit;
        }  
    
                
    } 
    
    public function savePageLinks($links,$link_info){

         
        foreach($links as $key=>$val){  
            
            $data = array(
                'link' => $val
            );

            $this->insert_page_link($data);      
            
        }
        
        return true;
            
    } 
    
    
    
    
    
    
    
    
    
    
    
    // Saving Links of the crawled data
    public function links_processing($href = null){
		//echo 'i am in data processor';exit;

        if(!empty($href)){
            foreach($href as $key){
                $data = array(
                   'page_id' => 1,
                   'href' => $key
                );
                //print_r($data);exit;
                
                // Insert Data In Links Table
                $this->save_links($data); 
            }
        }    
    
                
    }    
    
    
    

    
    
    
  
    
    // remove link to css file get all links from file
    function cleanUrl($file,$url, $type='') {
       
        
	$entities = 1; $index_host = 1; $index_meta_keywords = 1;

	$urlparts = parse_url($url);
	$host = $urlparts['host'];
	//remove filename from path
	$path = preg_replace('/([^\/]+)$/i', "", $urlparts['path']);
	$file = preg_replace("/<link rel[^<>]*>/i", " ", $file);
	$file = preg_replace("@<!--sphider_noindex-->.*?<!--\/sphider_noindex-->@si", " ",$file);	
	$file = preg_replace("@<!--.*?-->@si", " ",$file);	
	$file = preg_replace("@<script[^>]*?>.*?</script>@si", " ",$file);
	$headdata = $this->get_head_data($file);
	$regs = Array ();
	if (preg_match("@<title *>(.*?)<\/title*>@si", $file, $regs)) {
		$title = trim($regs[1]);
		$file = str_replace($regs[0], "", $file);
	} else if ($type == 'pdf' || $type == 'doc') { //the title of a non-html file is its first few words
		$title = substr($file, 0, strrpos(substr($file, 0, 40), " "));
	}

	$file = preg_replace("@<style[^>]*>.*?<\/style>@si", " ", $file);

	//create spaces between tags, so that removing tags doesnt concatenate strings
	$file = preg_replace("/<[\w ]+>/", "\\0 ", $file);
	$file = preg_replace("/<\/[\w ]+>/", "\\0 ", $file);
	$file = strip_tags($file);
	$file = preg_replace("/&nbsp;/", " ", $file);

	$fulltext = $file;
	$file .= " ".$title;
	if ($index_host == 1) {
		$file = $file." ".$host." ".$path;
	}
	if ($index_meta_keywords == 1) {
		$file = $file." ".$headdata['keywords'];
	}
	
	
	//replace codes with ascii chars
	//$file = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $file);
        //$file = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $file);
	$file = strtolower($file);
	//reset($entities);
	//while ($char = each($entities)) {
	//	$file = preg_replace("/".$char[0]."/i", $char[1], $file);
	//}
	$file = preg_replace("/&[a-z]{1,6};/", " ", $file);
	$file = preg_replace("/[\*\^\+\?\\\.\[\]\^\$\|\{\)\(\}~!\"\/@#?$%&=`?;><:,]+/", " ", $file);
	$file = preg_replace("/\s+/", " ", $file);
	$data['fulltext'] = addslashes($fulltext);
	$data['content'] = addslashes($file);
	$data['title'] = addslashes($title);
	$data['description'] = $headdata['description'];
	$data['keywords'] = $headdata['keywords'];
	$data['host'] = $host;
	$data['path'] = $path;
	$data['nofollow'] = $headdata['nofollow'];
	$data['noindex'] = $headdata['noindex'];
	$data['base'] = $headdata['base'];

	return $data;

}

    function get_head_data($file) {
	$headdata = "";
        
	preg_match("@<head[^>]*>(.*?)<\/head>@si",$file, $regs);
        
	$headdata = $file;
        

	$description = "";
	$robots = "";
	$keywords = "";
        $base = "";
        
	$res = Array ();
	
		preg_match("/<meta +name *=[\"']?robots[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
		if (isset ($res)) {
			$robots = $res[1];
		}

		preg_match("/<meta +name *=[\"']?description[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
		if (isset ($res)) {
			$description = $res[1];
		}

		preg_match("/<meta +name *=[\"']?keywords[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
                if (isset ($res)) {
			$keywords = $res[1];
		}
        
		preg_match("/<base +href *= *[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
		if (isset ($res)) {
			$base = $res[1];
		}
		$keywords = preg_replace("/[, ]+/", " ", $keywords);
		$robots = explode(",", strtolower($robots));
		$nofollow = 0;
		$noindex = 0;
		foreach ($robots as $x) {
			if (trim($x) == "noindex") {
				$noindex = 1;
			}
			if (trim($x) == "nofollow") {
				$nofollow = 1;
			}
		}
                
		$data['description'] = addslashes($description);
		$data['keywords'] = addslashes($keywords);
		$data['nofollow'] = $nofollow;
		$data['noindex'] = $noindex;
		$data['base'] = $base;
                
	
	return $data;
}

    function unique_array($arr) {
	global $min_word_length;
	global $common;
	global $word_upper_bound;
	global $index_numbers;
        
        $stem_words = 1;
	
	if ($stem_words == 1) {
		$newarr = Array();
		foreach ($arr as $val) {
			$newarr[] = $this->stem($val);
		}
		$arr = $newarr;
	}
        
	sort($arr);
	reset($arr);
	$newarr = array ();

	$i = 0;
	$counter = 1;
	$element = current($arr);

	if ($index_numbers == 1) {
		$pattern = "/[a-z0-9]+/";
	} else {
		$pattern = "/[a-z]+/";
	}

	$regs = Array ();
	for ($n = 0; $n < sizeof($arr); $n ++) {
		//check if word is long enough, contains alphabetic characters and is not a common word
		//to eliminate/count multiple instance of words
		$next_in_arr = next($arr);
		if ($next_in_arr != $element) {
			if (strlen($element) >= $min_word_length && preg_match($pattern, $this->remove_accents($element)) && (@ $common[$element] <> 1)) {
				if (preg_match("/^(-|\\\')(.*)/", $element, $regs))
					$element = $regs[2];

				if (preg_match("/(.*)(\\\'|-)$/", $element, $regs))
					$element = $regs[1];

				$newarr[$i][1] = $element;
				$newarr[$i][2] = $counter;
				$element = current($arr);
				$i ++;
				$counter = 1;
			} else {
				$element = $next_in_arr;
			}
		} else {
				if ($counter < $word_upper_bound)
					$counter ++;
		}

	}
	return $newarr;
}
        



        function stem($word){
		if (strlen($word) <= 2) {
			return $word;
		}

		$word = $this->step1ab($word);
		$word = $this->step1c($word);
		$word = $this->step2($word);
		$word = $this->step3($word);
		$word = $this->step4($word);
		$word = $this->step5($word);

		return $word;
	}

	function step1ab($word){
            //hasan
            //dd($this->regex_vowel);exit;
		
		// Part a
		if (substr($word, -1) == 's') {

			   $this->replace($word, 'sses', 'ss')
			OR $this->replace($word, 'ies', 'i')
			OR $this->replace($word, 'ss', 'ss')
			OR $this->replace($word, 's', '');
		}

		// Part b
		if (substr($word, -2, 1) != 'e' OR !$this->replace($word, 'eed', 'ee', 0)) { // First rule
			$v = $this->regex_vowel;
                        //dd($v);exit;
                        
			// ing and ed
			if (   preg_match("#$v+#", substr($word, 0, -3)) && $this->replace($word, 'ing', '')
				OR preg_match("#$v+#", substr($word, 0, -2)) && $this->replace($word, 'ed', '')) { // Note use of && and OR, for precedence reasons

				// If one of above two test successful
				if (    !$this->replace($word, 'at', 'ate')
					AND !$this->replace($word, 'bl', 'ble')
					AND !$this->replace($word, 'iz', 'ize')) {

					// Double consonant ending
					if (    $this->doubleConsonant($word)
						AND substr($word, -2) != 'll'
						AND substr($word, -2) != 'ss'
						AND substr($word, -2) != 'zz') {

						$word = substr($word, 0, -1);

					} else if ($this->m($word) == 1 AND $this->cvc($word)) {
						$word .= 'e';
					}
				}
			}
		}

		return $word;
	}

	function step1c($word){
            
		$v = $this->regex_vowel;

		if (substr($word, -1) == 'y' && preg_match("#$v+#", substr($word, 0, -1))) {
			$this->replace($word, 'y', 'i');
		}

		return $word;
	}

	function step2($word){
            
		switch (substr($word, -2, 1)) {
			case 'a':
				   $this->replace($word, 'ational', 'ate', 0)
				OR $this->replace($word, 'tional', 'tion', 0);
				break;

			case 'c':
				   $this->replace($word, 'enci', 'ence', 0)
				OR $this->replace($word, 'anci', 'ance', 0);
				break;

			case 'e':
				$this->replace($word, 'izer', 'ize', 0);
				break;

			case 'g':
				$this->replace($word, 'logi', 'log', 0);
				break;

			case 'l':
				   $this->replace($word, 'entli', 'ent', 0)
				OR $this->replace($word, 'ousli', 'ous', 0)
				OR $this->replace($word, 'alli', 'al', 0)
				OR $this->replace($word, 'bli', 'ble', 0)
				OR $this->replace($word, 'eli', 'e', 0);
				break;

			case 'o':
				   $this->replace($word, 'ization', 'ize', 0)
				OR $this->replace($word, 'ation', 'ate', 0)
				OR $this->replace($word, 'ator', 'ate', 0);
				break;

			case 's':
				   $this->replace($word, 'iveness', 'ive', 0)
				OR $this->replace($word, 'fulness', 'ful', 0)
				OR $this->replace($word, 'ousness', 'ous', 0)
				OR $this->replace($word, 'alism', 'al', 0);
				break;

			case 't':
				   $this->replace($word, 'biliti', 'ble', 0)
				OR $this->replace($word, 'aliti', 'al', 0)
				OR $this->replace($word, 'iviti', 'ive', 0);
				break;
		}

		return $word;
	}

	function step3($word){
            
		switch (substr($word, -2, 1)) {
			case 'a':
				$this->replace($word, 'ical', 'ic', 0);
				break;

			case 's':
				$this->replace($word, 'ness', '', 0);
				break;

			case 't':
				   $this->replace($word, 'icate', 'ic', 0)
				OR $this->replace($word, 'iciti', 'ic', 0);
				break;

			case 'u':
				$this->replace($word, 'ful', '', 0);
				break;

			case 'v':
				$this->replace($word, 'ative', '', 0);
				break;

			case 'z':
				$this->replace($word, 'alize', 'al', 0);
				break;
		}

		return $word;
	}

	function step4($word){
            
		switch (substr($word, -2, 1)) {
			case 'a':
				$this->replace($word, 'al', '', 1);
				break;

			case 'c':
				   $this->replace($word, 'ance', '', 1)
				OR $this->replace($word, 'ence', '', 1);
				break;

			case 'e':
				$this->replace($word, 'er', '', 1);
				break;

			case 'i':
				$this->replace($word, 'ic', '', 1);
				break;

			case 'l':
				   $this->replace($word, 'able', '', 1)
				OR $this->replace($word, 'ible', '', 1);
				break;

			case 'n':
				   $this->replace($word, 'ant', '', 1)
				OR $this->replace($word, 'ement', '', 1)
				OR $this->replace($word, 'ment', '', 1)
				OR $this->replace($word, 'ent', '', 1);
				break;

			case 'o':
				if (substr($word, -4) == 'tion' OR substr($word, -4) == 'sion') {
				   $this->replace($word, 'ion', '', 1);
				} else {
					$this->replace($word, 'ou', '', 1);
				}
				break;

			case 's':
				$this->replace($word, 'ism', '', 1);
				break;

			case 't':
				   $this->replace($word, 'ate', '', 1)
				OR $this->replace($word, 'iti', '', 1);
				break;

			case 'u':
				$this->replace($word, 'ous', '', 1);
				break;

			case 'v':
				$this->replace($word, 'ive', '', 1);
				break;

			case 'z':
				$this->replace($word, 'ize', '', 1);
				break;
		}

		return $word;
	}

	function step5($word){
            
		// Part a
		if (substr($word, -1) == 'e') {
			if ($this->m(substr($word, 0, -1)) > 1) {
				$this->replace($word, 'e', '');

			} else if ($this->m(substr($word, 0, -1)) == 1) {

				if (!$this->cvc(substr($word, 0, -1))) {
					$this->replace($word, 'e', '');
				}
			}
		}

		// Part b
		if ($this->m($word) > 1 AND $this->doubleConsonant($word) AND substr($word, -1) == 'l') {
			$word = substr($word, 0, -1);
		}

		return $word;
	}
        
        function replace(&$str, $check, $repl, $m = null){
		$len = 0 - strlen($check);

		if (substr($str, $len) == $check) {
			$substr = substr($str, 0, $len);
			if (is_null($m) OR $this->m($substr) > $m) {
				$str = $substr . $repl;
			}

			return true;
		}

		return false;
	}
        
        function remove_accents($string) {
		return (strtr($string, "�������������������������������������������������������������",
					  "aaaaaaaaaaaaaaoooooooooooooeeeeeeeeecceiiiiiiiiuuuuuuuunntsyy"));
	}
        
        /**
	* What, you mean it's not obvious from the name?
	*
	* m() measures the number of consonant sequences in $str. if c is
	* a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
	* presence,
	*
	* <c><v>       gives 0
	* <c>vc<v>     gives 1
	* <c>vcvc<v>   gives 2
	* <c>vcvcvc<v> gives 3
	*
	* @param  string $str The string to return the m count for
	* @return int         The m count
	*/
	function m($str){
		$c = $this->regex_consonant;
		$v = $this->regex_vowel;

		$str = preg_replace("#^$c+#", '', $str);
		$str = preg_replace("#$v+$#", '', $str);

		preg_match_all("#($v+$c+)#", $str, $matches);

		return count($matches[1]);
	}
        
        /**
	* Checks for ending CVC sequence where second C is not W, X or Y
	*
	* @param  string $str String to check
	* @return bool        Result
	*/

	function cvc($str){

		$regex_consonant = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';
		$regex_vowel = '(?:[aeiou]|(?<![aeiou])y)';

		$c = $regex_consonant;
		$v = $regex_vowel;

		return     preg_match("#($c$v$c)$#", $str, $matches)
			   AND strlen($matches[1]) == 3
			   AND $matches[1]{2} != 'w'
			   AND $matches[1]{2} != 'x'
			   AND $matches[1]{2} != 'y';
	}
        
        /**
	* Returns true/false as to whether the given string contains two
	* of the same consonant next to each other at the end of the string.
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	function doubleConsonant($str){
		$c = $this->regex_consonant;

		return preg_match("#$c{2}$#", $str, $matches) AND $matches[0]{0} == $matches[0]{1};
	}
        
        function getFullPageHtml($link_info) {
            $link_id = $link_info['link_id']; 
            $full_html = file_get_contents('Repo/PageHtmlRepo/'.$link_id.'_page_html.html');
            return $full_html;
        }
        
    /* Extract links from html */
    function get_links($link_info) {
        
        $url = $link_info['link'];
        
        $file = $this->getFullPageHtml($link_info); 
        
            $links = array ();
            $chunklist = array ();
            $regs = Array ();
            $checked_urls = Array();
            
            preg_match_all("/href\s*=\s*[\'\"]?([+:%\/\?~=&;\\\(\),._a-zA-Z0-9-]*)(#[.a-zA-Z0-9-]*)?[\'\" ]?(\s*rel\s*=\s*[\'\"]?(nofollow)[\'\"]?)?/i", $file, $regs);          
            
            if(!empty($regs[1])){               
                foreach ($regs[1] as $val) { 
                    $a = $this->url_purify($val, $url);
                    if (!empty($a)) {
                        $links[] = $a;
                    }                    
                }                  
            } 
            
            
            preg_match_all("/(frame[^>]*src[[:blank:]]*)=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
            if(!empty($regs[1])){               
                foreach ($regs[1] as $val) { 
                    $a = $this->url_purify($val, $url);
                    if (!empty($a)) {
                        $links[] = $a;
                    }                    
                }                  
            }
            
            preg_match_all("/(window[.]location)[[:blank:]]*=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
            if(!empty($regs[1])){               
                foreach ($regs[1] as $val) { 
                    $a = $this->url_purify($val, $url);
                    if (!empty($a)) {
                        $links[] = $a;
                    }                    
                }                  
            }
            
            preg_match_all("/(http-equiv=['\"]refresh['\"] *content=['\"][0-9]+;url)[[:blank:]]*=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
            if(!empty($regs[1])){               
                foreach ($regs[1] as $val) { 
                    $a = $this->url_purify($val, $url);
                    if (!empty($a)) {
                        $links[] = $a;
                    }                    
                }                  
            }

            preg_match_all("/(window[.]open[[:blank:]]*[(])[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
            if(!empty($regs[1])){               
                foreach ($regs[1] as $val) { 
                    $a = $this->url_purify($val, $url);
                    if (!empty($a)) {
                        $links[] = $a;
                    }                    
                }                  
            }

            return $links;
    }   
    
    public function get_ext_word(){
        $file = file_get_contents(ENGINEROOT.'DataProcessor/TextFiles/CommonWords.txt');
        $split = explode("\n", $file);
        return $split;
    }
	
    
    
        /* Checks if url is legal, relative to the main url.*/
        function url_purify($url, $parent_url) {
            
            $first_char = substr($url,1);
            if($first_char != '/'){
                $url = '/'.$url;
            }
            
            global  $mainurl, $apache_indexes, $strip_sessids;
            $mainurl = $parent_url;

            $ext = $this->get_ext_word();

            $urlparts = parse_url($url);
            //de($urlparts);

            $main_url_parts = parse_url($mainurl);
            

            reset($ext);
            while (list ($id, $excl) = each($ext))
                    if (preg_match("/\.$excl$/i", $url))                           
                            return '';

            if (substr($url, -1) == '\\') {
                    
                    return '';
            }



            if (isset($urlparts['query'])) {
                    if ($apache_indexes[$urlparts['query']]) {
                       
                            return '';
                    }
            }
           

            if (preg_match("/[\/]?mailto:|[\/]?javascript:|[\/]?news:/i", $url)) {
                    
                    return '';
            }
            if (isset($urlparts['scheme'])) {
                    $scheme = $urlparts['scheme'];
            } else {
                    $scheme ="";
            }



            //only http and https links are followed
            if (!($scheme == 'http' || $scheme == '' || $scheme == 'https')) {
                    
                    return '';
            }

            //parent url might be used to build an url from relative path
            $parent_url = $this->remove_file_from_url($parent_url);
            $parent_url_parts = parse_url($parent_url);


            if (substr($url, 0, 1) == '/') {
                    $url = $parent_url_parts['scheme']."://".$parent_url_parts['host'].$url;
            } else
                    if (!isset($urlparts['scheme'])) {
                            $url = $parent_url.$url;
                    }

            $url_parts = parse_url($url);

            $urlpath = $url_parts['path'];

            $regs = Array ();

            while (preg_match("/[^\/]*\/[.]{2}\//", $urlpath, $regs)) {
                    $urlpath = str_replace($regs[0], "", $urlpath);
            }

            //remove relative path instructions like ../ etc 
            $urlpath = preg_replace("/\/+/", "/", $urlpath);
            $urlpath = preg_replace("/[^\/]*\/[.]{2}/", "",  $urlpath);
            $urlpath = str_replace("./", "", $urlpath);
            $query = "";
            if (isset($url_parts['query'])) {
                    $query = "?".$url_parts['query'];
            }
            if ($main_url_parts['port'] == 80 || $url_parts['port'] == "") {
                    $portq = "";
            } else {
                    $portq = ":".$main_url_parts['port'];
            }
            $url = $url_parts['scheme']."://".$url_parts['host'].$portq.$urlpath.$query;

            //if we index sub-domains
            if ($can_leave_domain == 1) {
                    return $url;
            }

            $mainurl = $this->remove_file_from_url($mainurl);

            if ($strip_sessids == 1) {
                    $url = remove_sessid($url);
            }
            //only urls in staying in the starting domain/directory are followed	
            $url = $this->convert_url($url);
            
            
            if (strstr($url, $mainurl) == false) {
                
                    return '';
            } else {
                return $url;
            }
                    
        }
        
        /* Remove the file part from an url (to build an url from an url and given relative path) */
        function remove_file_from_url($url) {
            $url_parts = parse_url($url);
            $path = $url_parts['path'];

            $regs = Array ();
            if (preg_match('/([^\/]+)$/i', $path, $regs)) {
                    $file = $regs[1];
                    $check = $file.'$';
                    $path = preg_replace("/$check"."/i", "", $path);
            }

            if ($url_parts['port'] == 80 || $url_parts['port'] == "") {
                    $portq = "";
            } else {
                    $portq = ":".$url_parts['port'];
            }

            $url = $url_parts['scheme']."://".$url_parts['host'].$portq.$path;
            return $url;
        }

    function convert_url($url) {
	$url = str_replace("&amp;", "&", $url);
	$url = str_replace(" ", "%20", $url);
	return $url;
    }
    
    /* Removes duplicate elements from an array */
    function distinct_array($arr) {
            rsort($arr);
            reset($arr);
            $newarr = array();
            $i = 0;
            $element = current($arr);

            for ($n = 0; $n < sizeof($arr); $n++) {
                    if (next($arr) != $element) {
                            $newarr[$i] = $element;
                            $element = current($arr);
                            $i++;
                    }
            }

            return $newarr;
    }
    
    function calc_weights($wordarray, $title, $host, $path, $keywords) {
	global $index_host, $index_meta_keywords;
	$hostarray = $this->unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($host))));
	$patharray = $this->unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($path))));
	$titlearray = $this->unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($title))));
	$keywordsarray = $this->unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($keywords))));
	$path_depth = $this->countSubstrs($path, "/");

	while (list ($wid, $word) = each($wordarray)) {
		$word_in_path = 0;
		$word_in_domain = 0;
		$word_in_title = 0;
		$meta_keyword = 0;
		if ($index_host == 1) {
			while (list ($id, $path) = each($patharray)) {
				if ($path[1] == $word[1]) {
					$word_in_path = 1;
					break;
				}
			}
			reset($patharray);

			while (list ($id, $host) = each($hostarray)) {
				if ($host[1] == $word[1]) {
					$word_in_domain = 1;
					break;
				}
			}
			reset($hostarray);
		}

		if ($index_meta_keywords == 1) {
			while (list ($id, $keyword) = each($keywordsarray)) {
				if ($keyword[1] == $word[1]) {
					$meta_keyword = 1;
					break;
				}
			}
			reset($keywordsarray);
		}
		while (list ($id, $tit) = each($titlearray)) {
			if ($tit[1] == $word[1]) {
				$word_in_title = 1;
				break;
			}
		}
		reset($titlearray);

		$wordarray[$wid][2] = (int) ($this->calc_weight($wordarray[$wid][2], $word_in_title, $word_in_domain, $word_in_path, $path_depth, $meta_keyword));
	}
	reset($wordarray);
	return $wordarray;
    }
    
    function countSubstrs($haystack, $needle) {
	$count = 0;
	while(strpos($haystack,$needle) !== false) {
	   $haystack = substr($haystack, (strpos($haystack,$needle) + 1));
	   $count++;
	}
	return $count;
    }
    
    //function to calculate the weight of pages
    function calc_weight ($words_in_page, $word_in_title, $word_in_domain, $word_in_path, $path_depth, $meta_keyword) {
        global $title_weight, $domain_weight, $path_weight,$meta_weight;
        $weight = ($words_in_page + $word_in_title * $title_weight +
                          $word_in_domain * $domain_weight +
                          $word_in_path * $path_weight + $meta_keyword * $meta_weight) *10 / (0.8 +0.2*$path_depth);

        return $weight;
    }
    
    function save_keywords($wordarray, $link_id) {
	global $mysql_table_prefix, $all_keywords;
	reset($wordarray);
        
	while ($thisword = each($wordarray)) {
		$word = $thisword[1][1];
		$wordmd5 = substr(md5($word), 0, 1);
		$weight = $thisword[1][2];             
                
		if (strlen($word)<= 30) {
                    
                    $keyword_id = $all_keywords[$word];
                    
                    
			if ($keyword_id  == "") {
                            
                            $data = array(              
                               'keyword' => $word              
                            );
                            $this->insert_keyword($data);
                        
                            /*
                            if (mysql_errno() == 1062) { 
                                $result = mysql_query("select keyword_ID from ".$mysql_table_prefix."keywords where keyword='$word'");
                                echo mysql_error();
                                $row = mysql_fetch_row($result);
                                $keyword_id = $row[0];
                            } else{
                                $keyword_id = mysql_insert_id();
                                $all_keywords[$word] = $keyword_id;
                                echo mysql_error();
                            }  */
			} 
                        
                    //$inserts[$wordmd5] .= ",($link_id, $keyword_id, $weight, $domain)"; 
                    
		}
	}

	for ($i=0;$i<=15; $i++) {
            $char = dechex($i);
            $values= substr($inserts[$char], 1);
            if ($values!="") {
                $query = "insert into ".$mysql_table_prefix."link_keyword$char (link_id, keyword_id, weight, domain) values $values";
                mysql_query($query);
                echo mysql_error();
            }
	}
    }
    
    function insert_keyword($data){          
        $this->insert($data,'keywords');
        return 1;       
    }
	
}
