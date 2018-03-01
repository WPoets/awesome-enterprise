<?php
class iptocountry{
	static function get_short_name($ip) {    
		$numbers = preg_split( "/\./", $ip);    
		include("ip_files/".$numbers[0].".php");
		$code=($numbers[0] * 16777216) + ($numbers[1] * 65536) + ($numbers[2] * 256) + ($numbers[3]);    
		foreach($ranges as $key => $value){
			if($key<=$code){
				if($ranges[$key][0]>=$code){$country=$ranges[$key][1];break;}
				}
		}
		if ($country==""){$country="unkown";}
		return $country;
	}
	
	static function get_full_name($short_name) {    
		include("ip_files/countries.php");
		//$three_letter_country_code=$countries[ $two_letter_country_code][0];
		$country_name=$countries[$short_name][1];
		return $country_name;
	}
}