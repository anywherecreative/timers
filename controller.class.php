<?php
class Controller {
	private $model;
	protected $variables;
	protected $headVars;
	protected $title;
	public function __construct() {
		$this->variables = array();
		$this->headVars = array();
		$this->title = "Time Tracking ";
		$action = (isset($_GET['action'])) ? $_GET['action']:null;
		//capture message variables and unset the session variables for them
		if(isset($_SESSION['info-message'])) {
			$this->addVariable('info-message',$_SESSION['info-message']);
			unset($_SESSION['info-message']);
		}
		if(isset($_SESSION['error-message'])) {
			$this->addVariable('error-message',$_SESSION['error-message']);
			unset($_SESSION['error-message']);
		}
		if(isset($_SESSION['success-message'])) {
			$this->addVariable('success-message',$_SESSION['success-message']);
			unset($_SESSION['success-message']);
		}
		if(!isset($_GET['option'])) {
			$this->addTitle("Home - Time Tracking");
			$this->loadView('track');
		}
		elseif($_GET['option'] == 'user') {
			require_once('controllers/user.controller.php');
			$uc = New UserController();
		}
		elseif($_GET['option'] == 'system' && SHOW_SYSTEM_INFO) {
			//system information
			phpinfo();
			exit;
		}
		elseif($_GET['option'] == 'whoami') {
			$this->loadView('whoami');
		}
		elseif($_GET['option'] == 'e') {
			$this->addVariable('url',$_SERVER['REQUEST_URI']);
			if($_GET['action'] == 404) {
				header("HTTP/1.1 404 Page Not Found");
				$this->addVariable('type',404);
			}
			else {
				header('Location: /');
				exit;
			}
			$this->addTitle("That Tale has Not Yet Been Told");
			$this->loadView('error');
		}
		else {
			//this will sanatize the option name, and check for a controller for it
			//if one exists it will load that controller, and it's model if it exists
			//if not then show a 404.
			$option = preg_replace("/!([a-zA-Z])/","",$_GET['option']);
			if(file_exists(SITE_DIR.'/controllers/'.$option.'.controller.php')) {
				require_once(SITE_DIR.'/controllers/'.$option.'.controller.php');
				if(file_exists(SITE_DIR.'/models/'.$option.'.class.php')) {
					require_once(SITE_DIR.'/models/'.$option.'.class.php');
				}
				$reflection = new ReflectionClass($option.'Controller');
				$instance = $reflection->newInstanceArgs();
			}
			else {
				$this->show404();
			}
		}
	}
	/**
	 * this function adds a variable to be used by the view
	 * @param $key String the key to access the variable by
	 * @param $value mixed the value to store for that key
	**/
	protected function addVariable($key,$value) {
		$this->variables[$key] = $value;
	}
	/**
	 * load a variable that is set by the controller
	 * @param $key String the variable to load
	 * @return mixed the value stored, or false if it doesn't exist.
	 * TODO throw an error instead of a return on failure to avoid ambiguity
	 *      (boolean key, with a false return for a fail)
	**/
	protected function loadVariable($key) {
		if(isset($this->variables[$key])) {
			return $this->variables[$key];
		}
		else {
			return false;
		}
	}
	/**
	 * load the content to be included in the head of the view.
	 * @return String the HTML to include in the head
	**/
	private function loadHead() {
		$head = "<title>" . $this->title . "</title>\n";
		if(is_array($this->headVars)) {
			foreach($this->headVars as $key=>$value) {
				$head .= "<meta name='$key' content='$value' />";
			}
		}
		return $head;
	}
	/**
	 * add the title to be shown in the browser tab/bookmark bar
	 * @param $title String the title to use
	 **/
	protected function addTitle($title) {
		$this->title = $title;
	}
	/**
	 * alias of addTitle
	 **/
	protected function setTitle($title) {
		self::addTitle($title);
	}
	/**
	 * add meta data to be included in the head of the view
	 * @param $key String the meta name attribute
	 * @param $value String teh meta value attribute
	 **/
	protected function addMeta($key, $value) {
		$headVars[$key] = $value;
	}
	/**
	 * loads the view to be shown the browser.  This function echos, as oppose to
	 * returns the data.  In order to capture the output use output buffering.
	 * @param $view String the view to load (without php extension)
	 * @param $wrap boolean use the header and footer default is true
	 * @param $cacheable wheather or not to store page in cache default is false
	 **/
	protected function loadView($view,$wrap=true,$cacheable=false) {
		global $tokenGuard;
		if($cacheable) {
			$cache = new QuickCache($view);
			if($cache->isCached()) {
				echo $cache->fetch();
				exit;
			}
			ob_start();
		}
		if($wrap) {
			require_once('views/header.php');
			require_once('views/'.$view.'.php');
			require_once('views/footer.php');
		}
		else {
			require_once('views/'.$view.'.php');
		}
		if($cacheable) {
			$contents = ob_get_contents ();
			$cache->update($contents);
			ob_flush ();
		}
	}
	/**
	 * returns weather or not a variable is set for use in the view
	 * @param $key String the variable to check
	 * @return boolean true if it exists, false otherwise.
	 **/
	protected function isVariable($key) {
		return isset($this->variables[$key]);
	}

	protected function loadModule($module) {
		if(is_dir ('./modules/'.$module)) {

		}
	}
	/**
		* this function takes a string for a single js file or an array
		* if given an array, it will couple all js files together and serve them as one
		* reducing requests.  The resulting file is cached so subsequent requests can use it

		* the array is sorted alphabetically in order to catch out of order files.
		* @param $js String[] an array of Js files to use, without js extension
		* @return String the js file to include as HTML
	**/
	protected function addJs($js=null) {
		if($js === null) {
			return "";
		}
		if(!is_array($js)) {
			return $js;
		}
		//$js is an array, sort alphabetically7
		$name = "";
		$content = "";
		asort($js);
		$used = array();
		$js = str_replace(array("/","\\",">","<",".js"),"",$js);
		if(file_exists(md5(implode($js)).".js")) {
			return md5(implode($js)).".js";
		}
		foreach($js as $file) {
			//name is equal to the js file name minus .js or the full length if that's not present
			if(file_exists("assets/js/".$file.".js") && !in_array($file,$used)) {
				//$content .= preg_replace( "/\r|\n|\t/", "",file_get_contents ("assets/js/$file.js")) . ";";
				$content .= file_get_contents ("assets/js/$file.js");
				$used[] = $file;
				$name .= $file;
			}
			else {
				continue;
			}

		}
		file_put_contents("assets/js/cache/".md5($name).".js",$content);
		return "/assets/js/cache/".md5($name).".js";
	}
	/**
	 * loads the 404 view with data
	 **/
	protected function show404() {
		header("HTTP/1.1 404 Page Not Found");
		$this->addVariable('url',$_SERVER['REQUEST_URI']);
		$this->addVariable('type',404);
		$this->addTitle("That Tale has Not Yet Been Told");
		$this->loadView('error');
	}
}
