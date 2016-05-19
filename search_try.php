<?php 
  	include 'spellcorrector.php';

	header('Content-Type: text/html; charset=utf-8'); 	 
	$limit =10;  
	$query =isset($_REQUEST['d']) ? $_REQUEST['d'] : false; 
	
	if($query!=null) 
        {
		$queryterm=explode(" ",$query);
		$n=sizeof($queryterm);

		foreach($queryterm as $word)
                {
			$wordNew =spellcorrector::correct($word);
			$newtry =$newtry.$wordNew." ";
		}
	}
	
	$result =false; 
 
	if ($newtry) 
	{  
	  require_once('solr-php-client-master/Apache/Solr/Service.php'); 
	 
	  // new solr service instance O host, port, and corename 
	  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/'); 
	 
	  // stripslashes to be enabled
        
	  if (get_magic_quotes_gpc() == 1) 
	  { 
	    $newtry= stripslashes($newtry); 
	  } 

	  //try catch block
        
	  try 
	  { 
	  	if(isset($_REQUEST['file']))
		  {
		  	$query1 =$newtry;
		  	$add=array('sort'=>'pageRankFile desc');
			$result =$solr->search($query1,0, $limit,$add);
			$suggestResults =$solr->suggest($query1,0, $limit,$add);
		  	 
		  } 
          else 
		  {
		  	$result =$solr->search($newtry, 0, $limit); 
		  	$suggestResults =$solr->suggest($newtry, 0, $limit);
		  }
	   
	  } 
	  catch (Exception $e) 
	  {       
	    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");    
	  } 
	} 
	
	?> 


	<html> 
	  <head> 
	    <title>Solr-Search</title> 
	    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" />
       <script src="//code.jquery.com/jquery-1.10.2.js"></script>
       <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	  </head> 
        
	  <body> 
	    <form  accept-charset="utf-8" method="get" autocomplete="on"> 
	      <label for="d">Search:</label>        
	      <input id="d" name="d" type="text"  onkeypress="suggestion()"value="<?php echo htmlspecialchars($newQuery, ENT_QUOTES, 'utf-8'); ?>"/>        
	      <br><br>
            
	      Page Rank Applied<input type="radio"name="file" value="file"><br><br>
	      <input type="submit"name="submit"/> <br>
	    </form> 

	  <script type="text/javascript" >
        function suggestion() {
               $("#d").autocomplete({
                   source:function(request,response) 
                   {
                       $.ajax({
                           crossDomain:true,
                           url:"suggest.php",
                           data:{
                               d:$("#d").val()
                           },
                           success:function(data)
                           { 
                               var cry =data.split(",");
                               cry =JSON.parse(cry);
                               
                               response($.map(cry,function (item){
                                   return {
                                       label:item,
                                       value:item
                                   }
                               }));
console.log("Success function query: " + $("#q").val());
                              
                           },
                           error: function () {
                               console.log("Wrong output!!");
                           }

                       });
                   },
                   minLength: 1
               });
			//Suggestion function starts from here
           };

        </script>
	<?php 

	if($newtry!=$query) {
        echo "did you mean?<font color='blue'>$newtry</font></br>";
		echo "Showing results for <font color='blue'> $newtry </font> <br>";
	}
	// showing results 
	if ($result) 
	{ 
	  $total=(int)$result->response->numFound; 
	  $start =min(1,$total); 
	  $end =min($limit,$total); 
	?>    
          
	<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>      
	<ol> 
        
	<?php 
	  // result iterations 
	  foreach($result->response->docs as $doc) 
	  { 
	?> 
	      <li> 
	<?php 
	    // iterate document fields / values 
		$id ="n/a";
		$title ="n/a";
		$size ="n/a";
		$author ="n/a";
		$date ="n/a";
	    foreach ($doc as $field=>$value) 
	    { 
	    	if($field =="id")
            {
	    		$id =$value; 
	    		$id=substr($id,36);	
	    	}
            elseif($field=="dc_title")
            {
	    		$title =$value;		
	    	} 
            elseif($field=="stream_size") 
            {
	    		$size =$value;
	    		$size/=1024;
	    		$size=round($size,3);
	    	} 
            elseif($field=="author")
            {
	    		$author =$value;
	    	}
	
	    }
		

		$id =str_replace("%3A",":",$id);		
		$id =str_replace("%2F","/",strstr($id,".html",true));		

	     echo "<a href='$id'>Document</a>".$title."<br>";
	     echo "Size: " .$size."KB; Author: ".$author. "; Date created: ".$date."<br><br>";
	?> 
	      </li> 
	<?php 
	  } 
	?> 
	    </ol> 
	<?php 
	} 
	?> 
	 
        </body> 
	</html> 
