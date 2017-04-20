<?php
//Author: Daniel Raquel (draquel@webjynx.com)
	function ageReadable($sec){	if($sec > 60){ $age = $sec/60; if($age > 24){ $age = round($age/24,2) . " D"; }else{$age = round($age,2)." H";}}else{ $age = round($sec,2) . " M";}	return $age; }
	function isMobile() { return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]); }
	function parseImgs($src){ $imgs = array(); $doc = new DOMDocument(); $doc->loadHTMLFile($src); $doc->preserveWhiteSpace = false; $images = $doc->getElementsByTagName('img'); if($images->length >= 1){ foreach($images as $i){ $imgs[] = $i->getAttribute('src'); } } return $imgs; }
	function url($page = false){$ha = apache_request_headers();	if(isset($ha['X-Forwarded-Proto'])){ $protocol = $ha['X-Forwarded-Proto']; }else{$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https' : 'http'; } $protocol .= "://"; $out = $protocol . $_SERVER['HTTP_HOST']; if($page){ $out .= $_SERVER['REQUEST_URI']; } return $out; }
	function free_result($con) { while (mysqli_more_results($con) && mysqli_next_result($con)){ $dummyResult = mysqli_use_result($con); if ($dummyResult instanceof mysqli_result) { mysqli_free_result($con); } } }
	function str_sanitize($s){
		//Replace Symbols With HTML Codes
		str_replace(" ","&#32;",$s);
		str_replace("!","&#33;",$s);
		str_replace("\"","&#39;",$s);
		str_replace("#","&#35;",$s);
		str_replace("%","&#37;",$s);
		str_replace("&","&#38;",$s);
		str_replace("'","&#39;",$s);
		str_replace("(","&#40;",$s);
		str_replace(")","&#41;",$s);
		str_replace("*","&#42;",$s);
		str_replace("+","&#43;",$s);
		str_replace(",","&#44;",$s);
		str_replace("-","&#45;",$s);
		str_replace(".","&#46;",$s);
		str_replace("/","&#47;",$s);
		str_replace(":","&#58;",$s);
		str_replace(";","&#59;",$s);
		str_replace("<","&#60;",$s);
		str_replace(">","&#62;",$s);
		str_replace("?","&#63;",$s);
		str_replace("@","&#64;",$s);
		str_replace("_","&#95;",$s);
		str_replace("`","&#96;",$s);
		str_replace("{","&#123;",$s);
		str_replace("|","&#124;",$s);
		str_replace("}","&#125;",$s);
		str_replace("~","&#126;",$s);
		return $s;
	}
	function str_unsanitize($s){
		//Replace HTML Codes With Symbols
		str_replace("&#32;"," ",$s);
		str_replace("&#33;","!",$s);
		str_replace("&#39;","\"",$s);
		str_replace("#","&#35;",$s);
		str_replace("&#37;","%",$s);
		str_replace("&#38;","&",$s);
		str_replace("&#39;","'",$s);
		str_replace("&#40;","(",$s);
		str_replace("&#41;",")",$s);
		str_replace("&#42;","*",$s);
		str_replace("&#43;","+",$s);
		str_replace("&#44;",",",$s);
		str_replace("&#45;","-",$s);
		str_replace("&#46;",".",$s);
		str_replace("&#47;","/",$s);
		str_replace("&#58;",":",$s);
		str_replace("&#59;",";",$s);
		str_replace("&#60;","<",$s);
		str_replace("&#62;",">",$s);
		str_replace("&#63;","?",$s);
		str_replace("&#64;","@",$s);
		str_replace("&#95;","_",$s);
		str_replace("&#96;","`",$s);
		str_replace("&#123;","{",$s);
		str_replace("&#124;","|",$s);
		str_replace("&#125;","}",$s);
		str_replace("&#126;","~",$s);
		return $s;
	}
	function str_filename($s){
		str_replace(" ","_",$s);
		str_replace("&#32;","_",$s);
		str_replace("!","",$s);
		str_replace("\"","",$s);
		str_replace("#","",$s);
		str_replace("$","",$s);
		str_replace("%","",$s);
		str_replace("&","",$s);
		str_replace("'","",$s);
		str_replace("(","",$s);
		str_replace(")","",$s);
		str_replace("*","",$s);
		str_replace("+","",$s);
		str_replace(",","",$s);
		str_replace("-","",$s);
		str_replace("/","",$s);
		str_replace(":","",$s);
		str_replace(";","",$s);
		str_replace("<","",$s);
		str_replace(">","",$s);
		str_replace("?","",$s);
		str_replace("@","",$s);
		str_replace("`","",$s);
		str_replace("{","",$s);
		str_replace("|","",$s);
		str_replace("}","",$s);
		str_replace("~","",$s);
		return $s;
	}

	function random_date_in_range($start_date, $end_date){
		$new_date_timestamp = mt_rand($start_date->getTimestamp(), $end_date->getTimestamp());
		$random_date = new DateTime();
		$random_date->setTimestamp($new_date_timestamp);
		return $random_date;
	}

	function randString($length)
	{
		$charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$str = '';
		$count = strlen($charset);
		while ($length--) {
			$str .= $charset[mt_rand(0, $count-1)];
		}
		return $str;
	}
	
	/*function validateClient($browser){
		$support = FALSE;
		$red = FALSE;
		$green = TRUE;
		switch($browser->getBrowser()){
			case Browser::BROWSER_FIREFOX:
				switch(floor($browser->getVersion())){
					case 2:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 3:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 4:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 5:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 6:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 7:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 8:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 9:
						$wb = "Firefox ".$browser->getVersion(); $support = $red;
					break;
					case 10:
						$wb = "Firefox ".$browser->getVersion(); $support = $green;
					break;
					case 11:
						$wb = "Firefox ".$browser->getVersion(); $support = $green;
					break;
					case 12:
						$wb = "Firefox ".$browser->getVersion(); $support = $green;
					break;
					case 13:
						$wb = "Firefox ".$browser->getVersion(); $support = $green;
					break;
					case 14:
						$wb = "Firefox ".$browser->getVersion(); $support = $green;
					break;
				}
			break;
			case Browser::BROWSER_SAFARI:
				switch(floor($browser->getVersion())){
					case 2:
						$wb = "Safari ".$browser->getVersion(); $support = $red;
					break;
					case 3:
						$wb = "Safari ".$browser->getVersion(); $support = $red;
					break;
					case 4:
						$wb = "Safari ".$browser->getVersion(); $support = $green;
					break;
					case 5:
						$wb = "Safari ".$browser->getVersion(); $support = $green;
					break;
				}
			break;
			case Browser::BROWSER_IE:
				switch(floor($browser->getVersion())){
					case 2:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 3:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 4:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 5:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 6:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 7:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 8:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 9:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $red;
					break;
					case 10:
						$wb = "Internet Explorer ".$browser->getVersion(); $support = $green;
					break;
				}
			break;
			case Browser::BROWSER_CHROME:
				$wb = "Google Chrome"; $support = $green;
			break;
			case Browser::BROWSER_IPHONE:
				$wb = "iOS Safari"; $support = $red;
			break;
			case Browser::BROWSER_IPOD:
				$wb = "iOS Safari"; $support = $red;
			break;
			case Browser::BROWSER_IPAD:
				$wb = "iOS Safari"; $support = $red;
			break;
			case Browser::BROWSER_ANDROID:
				$wb = "Android Browser"; $support = $red;
			break;
			case Browser::BROWSER_BLACKBERRY:
				$wb = "Blackberry Browser"; $support = $red;
			break;
			case Browser::BROWSER_POCKET_IE:
				$wb = "Pocket Internet Explorer"; $support = $red;
			break;
			default:
				$wb .= "Unrecognized Browser";
			break;
		}
		switch($browser->getPlatform()){
			case  Browser::PLATFORM_WINDOWS:
				$os = "Microsoft Windows";
			break;
			case  Browser::PLATFORM_APPLE:
				$os = "Apple Mac";
			break;
			case  Browser::PLATFORM_IPHONE:
				$os = "Apple iOS (iPhone)";
			break;
			case  Browser::PLATFORM_IPOD:
				$os = "Apple iOS (iPod)";
			break;
			case  Browser::PLATFORM_IPAD:
				$os = "Apple iOS (iPad)";
			break;
			case  Browser::PLATFORM_ANDROID:
				$os = "Google Android";
			break;
			default:
				$os .= "Unrecognized OS";
			break;
		}
		return array($wb,$os,$support);
	}*/
	
	function validEmail($email){
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex){$isValid = false;}
	   else{
		  $domain = substr($email, $atIndex+1);
		  $local = substr($email, 0, $atIndex);
		  $localLen = strlen($local);
		  $domainLen = strlen($domain);
		  if ($localLen < 1 || $localLen > 64){$isValid = false;}// local part length exceeded
		  else if ($domainLen < 1 || $domainLen > 255){ $isValid = false;}// domain part length exceeded
		  else if ($local[0] == '.' || $local[$localLen-1] == '.'){$isValid = false;}// local part starts or ends with '.'
		  else if (preg_match('/\\.\\./', $local)){$isValid = false;}// local part has two consecutive dots
		  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){$isValid = false;}// character not valid in domain part
		  else if (preg_match('/\\.\\./', $domain)){$isValid = false;}// domain part has two consecutive dots
		  else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local))){
			  // character not valid in local part unless 
			 // local part is quoted
			 if (!preg_match('/^"(\\\\"|[^"])+"$/',
				 str_replace("\\\\","",$local)))
			 {
				$isValid = false;
			 }
		  }
		  if ($isValid && !(checkdnsrr($domain,"MX"))){
			 // domain not found in DNS
			 $isValid = false;
		  }
	   }
	   return $isValid;
	}
	
	/*function genSysEmail($title,$body){
		$s = "<!DOCTYPE HTML>
		<html>
			<head>
				<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
				<link href=\"http://src.webjynx.com/_css/mail.css\" rel=\"stylesheet\" type=\"text/css\">
			</head>
			<body>
				<a href=\"http://www.webjynx.com\"><img src=\"http://www.webjynx.com/banner.png\" width=\"300\" /></a>
				<div><h2>".$title."</h2></div>
				<div id=\"body\">&nbsp;&nbsp;&nbsp;
				".$body." 
				</div>
				<div id=\"sig\">
					<b>WebJynx Administrator</b><br />
					webmaster@webjynx.com <br />
					<a href=\"http://www.webjynx.com\">www.webjynx.com</a>
				</div>
				<div style=\"text-align:center\"><hr></div>
				<div style=\"margin-top:25px;text-align:center\"\><img src=\"http://cont.webjynx.com/logo/head/32/000.png\" width=\"24\" /></div>
			</body>
		</html>";	
		return $s;
	}*/
?>
