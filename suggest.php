<?php 
	header('Content-Type: text/html; charset=utf-8'); 	
	header("Access-Control-Allow-Origin: *"); 
	include 'spellcorrector.php';
	include 'porterstemmer.php';

	$limit=10;  
	if(isset($_GET['d'])) 
    {
		$query = $_GET['d'];
		if($query) 
        {
			$queryterm=explode(" ",$query);
			for ($i = 0; $i<(sizeof($queryterm)-1);$i++)
                        {
				$queryterm[$i]=spellcorrector::correct($queryterm[$i]);
				$prefix =$prefix .$queryterm[$i]. " ";
			}
			$query= $queryterm[sizeof($queryterm)- 1];
			require_once('solr-php-client-master/Apache/Solr/Service.php');
			$solr =new Apache_Solr_Service('localhost', 8983, '/solr/myexample/'); 
			if (get_magic_quotes_gpc()== 1) 
			{ 
				$query=stripslashes($query); 
			} 

			try 
	  		{ 
				$suggestresult= $solr->suggest($query, 0, $limit);

			} catch (Exception $e) 
			{       
			   
                die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");    
			}


			if($suggestresult) {
				$new =$suggestresult->suggest->suggest->$query->suggestions; 
				$result=array(); 
				for($b = 0;$b <15 ;$b++)
				{	
					$test=array($new[$b]->term=>$new[$b]->weight);
					$result= array_merge($result,$test);
				}
				// Sorting of weights
                
				arsort($result);
				$wordstem =array();
				$out=array();
				foreach ($result as $key =>$val) 
                                {
					    if($key!=null) 
                                            {
					    	$key =explode(".", $key)[0]; 
					    	$stem=porterstemmer::Stem($key);
					    	$one =array($key=>$stem);
					    	$wordstem=array_merge($wordstem,$one);
					    }
				}
                //exit that foreach loop
                
				$wordstem =array_unique($wordstem);
				$count=0;
                
				foreach ($wordstem as $key=>$val) 
             {
					if(++$count>5)
                    {
						break;
					}
					if($key !=null) {
						$out[] = $prefix .$key;
					}
				}
                //exit 2nd foreach loop
                
				echo json_encode($out);
			}
		}
	} 

?>
