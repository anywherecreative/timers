<?php
/**
* A custom caching script that automatically creates a cached version of the page
* that is loaded and expires after a set period of time.
* @copyright Jeff Manning
* @Date Feburary 2015
**/
class QuickCache {
	private $page;

	function __CONSTRUCT($item) {
		$this->page = CACHE_LOCATION . "/". MD5($item);
	}
	/**
	fetch the page from the cache
	@returns the page, or false on failure
	**/
	public function fetch() {
		if($this->isCached()) {
			return file_get_contents ($this->page);
		}
		else {
			return false;
		}
	}
	/**
	removes the page from the cache
	**/
	public function remove() {
		if(file_exists($this->page)) {
			unlink($this->page);
		}
	}
	/**
	replaces the currently cached page
	String $contents the contents of to be cached
	**/
	public function update($contents) {
		if(!file_exists($this->page)) {
			touch($this->page);
		}
		$fp = fopen($this->page, 'wb');
		fwrite($fp,$contents);
		fclose($fp);
	}
	/**
	checks to see if the page is cached
	@returns boolean true if cached, false otherwise
	**/
	public function isCached() {
		if(file_exists($this->page) && (time() - filemtime ($this->page)) < CACHE_TIME) {
			return true;
		}
	}
	/**
	this function is a cron function to auto delete old cache files
	**/
	public static function autoRemove() {
		if ($handle = opendir(CACHE_LOCATION)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && !is_dir(CACHE_LOCATION.'/'.$entry) && (time() - filemtime ($this->page)) >= CACHE_TIME) {
					unlink(CACHE_LOCATION.'/'.$entry);
				}
			}
			closedir($handle);
		}
	}
	/**
		This function removes all cached files
	**/
	public static function forceRemove() {
		if ($handle = opendir(CACHE_LOCATION)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && !is_dir(CACHE_LOCATION.'/'.$entry)) {
					unlink(CACHE_LOCATION.'/'.$entry);
				}
			}
			closedir($handle);
		}
	}
}
?>