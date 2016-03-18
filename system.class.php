<?php
class System {

	/**
		*cleans a string of non allowed charecters
	**/
	public static function clean_string($string,$allowBreaks=false) {
		$string = htmlentities (strip_tags ($string));
		if($allowBreaks) {
		$string = trim($string);
		$string = "<p>".str_replace("\n","</p>\n<p>",$string)."</p>";
		}
		return $string;
	}
	public static function cleanString($string, $allowBreaks=false) {
		return self::clean_string($string, $allowBreaks=false);
	}

	public static function generateToken() {
		$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));
		return $_SESSION['token'];
	}

	public static function checkToken($token) {
		if(!isset($_SESSION['token']) || $_SESSION['token'] != $token) {
			return false;
		}
		else {
			return true;
		}
	}
	/**
	 * return the current mysql object
	 * TODO move the connection functions into the system class and remove global calls?
	**/
	public static function getDatabaseObject() {
		global $mysql;
		return $mysql;
	}

	public static function getSubDomain() {
		$start = 0;
		if(stripos($_SERVER['HTTP_HOST'],"www.") !== false) {
			//string contains WWW.
			$start = stripos($_SERVER['HTTP_HOST'],"www.");
		}
		if(strtolower($_SERVER['HTTP_HOST']) == SITE_DOMAIN) {
			//no subdomain
			return "";
		}
		return strtolower(substr($_SERVER['HTTP_HOST'],$start, stripos($_SERVER['HTTP_HOST'],SITE_DOMAIN)-1));
	}
	public static function getSubString($string,$start,$length,$detectWord=true) {
		if(strlen($string) < $length) {
			return $string;
		}
		if($detectWord) {
			return substr($string,$start,stripos($string,".",$length)+$start+1);
		}
		else {
			return substr($string,$start,$length);
		}
	}

	/**
	* Do Not Track for privacy.
	* When this feature is enabled, webpage should exclude all tracking tools,
	* like Google Analytic and advertising networks
	* @return boolean true if user has DNT on false otherwise
	**/
	public static function getDNT() {
	  return (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1);
	}

	public static function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
}
