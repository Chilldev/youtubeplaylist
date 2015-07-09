<html>
<head>
	<title>Youtube Playlist Duration Calculator</title>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<form action="index.php" method='post'>
	<h1>Youtube Playlist Duration Calculator</h1>
	<label for="url">Please Enter Playlist's URL: <?php if(!empty($urlerr)){echo$urlerr;}?></label>
	<input name='url' type="url">
	<input type="submit" value="GO!">
</form>


<?php
class iniDOM{
	public $curl_handler;
	public $url;
	public $DOM;
	public $Xpath;
	public $result;
	public $state;
	public $site_session;

	function __construct($state){
		$this->state=$state;
	}

	function initiate_cURL($url){
		$this->url=$url;
		$this->curl_handler=curl_init($url);
		iniDOM::set_options($this->state);
		return $this->site_session= curl_exec($this->curl_handler);
		curl_close($this->curl_handler);
	}

	function set_options(){
		curl_setopt($this->curl_handler, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl_handler, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->curl_handler,CURLOPT_RETURNTRANSFER, $this->state);
	}

	function load_xpath(){
		$this->DOM = new DOMDocument();
		@$this->DOM->loadHTML($this->site_session);
		$this->Xpath = new DOMXpath($this->DOM);
	}

	function xquery($path){
		iniDOM::initiate_cURL($this->url);
		iniDOM::load_xpath();
		return $this->result=$this->Xpath->query($path);
	}
}


if($_SERVER['REQUEST_METHOD'] == 'POST'){

	extract($_POST);

	if (!filter_var($url, FILTER_VALIDATE_URL) === false){

		$urlerr="";
		$res=strpos($url,'&list=');

		if($res){
			$url=explode('&list=', $url);
			$url='https://www.youtube.com/playlist?list='.$url['1'];
		}

		$res=strpos($url,'&index=');

		if($res){
			$url=explode('&index=', $url);
			$url=$url['0'];
		}

		ini_set('max_execution_time', 2000);

		$obj= new iniDOM(1);
		$obj->initiate_cURL($url);
		$results=$obj->xquery('//div[@class=\'more-menu-wrapper\']/div[@class=\'timestamp\']/span/text()');

		$totalseconds  = 0;
		$totalminutes  = 0;
		$totalhours    = 0;
		$seconds       = 0;
		$minutes       = 0;
		$hours         = 0;

		foreach ($results as $result){
			
			$time=explode(':', $result->nodeValue);

			if($count=count($time) == 3){

				$hours         += $time['0'];	
				$minutes       += $time['1'];
				$seconds       += $time['2'];

			}else{

				$minutes       += $time['0'];
				$seconds       += $time['1'];

			}
		}

		$extraminutes = number_format((float)$seconds/60,2,'.','');
		$extrahours   = number_format((float)$minutes/60,2,'.','');

		if($extraminutes >= 1){

			$totalminutes += (int)$extraminutes;
			$seconds      -= (int)$extraminutes*60; 

		}

		if($extrahours >= 1){

			$totalhours  += (int)$extrahours;
		    $minutes     -= (int)$extrahours*60; 

		}

	 	$totalseconds  += $seconds;
	 	$totalminutes  += $minutes;

	 	if($totalminutes >= 59){

	 		$extrahours     = (int)$totalminutes/60;
	 		$totalminutes  -= (int)$extrahours*60;

	 	}else{
	 		
	 		$extrahours = 0;
	 	}


	 	$totalhours += $hours + (int)$extrahours;

		echo'<div>'.
				'<table border="1px">'.
					' <tr><th>Hours</th><th>Minutes</th><th>Seconds</th></tr>'.
					'<tr><td>'.$totalhours.'</td><td>'.$totalminutes.'</td><td>'.$totalseconds.'</td></tr>'.
				'</table>'.
			'</div>';
		}

}else{
    $urlerr="this is not a valid URL";
}
?>
</body>
</html>
