<?php
class UserController extends Controller {
	public function __CONSTRUCT() {

    if(isset($_GET['action'])) {
			$action=$_GET['action'];
		}
		else {
			if(User::isLoggedIn()) {
				$action='profile';
			}
			else {
				$action='register';
			}
		}
		if($action == 'register' && (!isset($_POST['name']) && !isset($_GET['id']))) {
			if(User::isLoggedIn()) {
				Header('Location: /user/profile');
				exit;
			}
			$this->addTitle("Get Started at TalesCollective:_");
			$this->loadView('userRegister',true);
		}
		elseif($action == 'register' && isset($_GET['id'])) {
			$check = User::checkInfo($_POST['user'], $_POST['email']);
			if($check === ERROR_INVALID_CHARACTERS) {
				echo json_encode(array("status"=>"ERROR","message"=>"Oops your username contains invalid characters.","field"=>"name"));
			}
			elseif($check === ERROR_NAME_RESERVED) {
				echo json_encode(array("status"=>"ERROR","message"=>"Oops your username is reserved","field"=>"name"));
			}
			elseif($check === ERROR_USER_EXISTS) {
				echo json_encode(array("status"=>"ERROR","message"=>"Oops your username is already in use","field"=>"name"));
			}
			elseif($check === ERROR_EMAIL_EXISTS) {
				echo json_encode(array("status"=>"ERROR","message"=>"Oops your email is already in use","field"=>"email"));
			}
			else {
				echo json_encode(array("status"=>"SUCCESS"));
				require_once('libs/mailChimp.class.php');
				$MailChimp = new MailChimp('17b0f3f2ce0fb254213e147298933b2c-us8');
				$response = $MailChimp->call('/lists/member-info', array(
					'id'                => 'df75066f5b',
					'email'             => array('email'=>$_POST['email'])
				));
				if(!isset($response->data->id)) {
					$response = $MailChimp->call('lists/subscribe', array(
						'id'                => 'df75066f5b',
						'email'             => array('email'=>$_POST['email']),
						'merge_vars' 		=> array('FNAME'=>$_POST['name']),
						'double_optin'      => true,
						'update_existing'   => true,
						'replace_interests' => false,
						'send_welcome'      => false,
					));
				}
			}
			exit;
		}
		//add the user
		elseif($action == 'register' && isset($_POST['token'])) {
			$error = array();
			$name = $_POST['name'];
			if(!System::checkToken($_POST['token'])) {
				//token fail
				$_SESSION['error-message'] = "Oops! Your token didn't seem to match (<a href='/help/token'>?</a>)";
				Header('Location: /');
				exit;
			}
			$this->addTitle("Get Started at TalesCollective:_");
			if($_POST['pass1'] != $_POST['pass2']) {
				$this->addVariable('error-message','passwords don\'t match');
				$this->loadView('userRegister');
				exit;
			}
			$password = $_POST['pass1'];
			$email = filter_var($_POST['email'],FILTER_VALIDATE_EMAIL);
			if($email === false) {
				$error[] = array("field"=>"email","message"=>"Email address provided was not valid");
			}
			$dob = $_POST['dob'];
			if(strtotime($dob) === false) {
				$error[] = array("field"=>"dob","message"=>"Not a valid birthday");
			}
			if(trim($name) =="") {
				$error[] = array("field"=>"name","message"=>"user name can't be empty");
			}
			$gender = $_POST['gender'];
			if($gender < 1 || $gender > 3) {
				$gender = 3;
			}
			$rating = PG;
			switch (strtolower($_POST['rating'])) {
				case 'general':
					$rating = GENERAL;
					break;
				case 'pg':
					$rating = PG;
					break;
				case 'adult':
					$rating = ADULT;
					break;
				case 'restricted':
					$rating = RESTRICTED;
					break;
				default:
					$rating = PG;
					break;
			}
			if(!empty($error)) {
				$this->addVariable('error-message',"oops! There seems to be a few errors.");
				$this->addVariable('errors',json_encode($error));
				$this->loadView('userRegister');
				exit;
			}
			$user = User::addUser($name, $password, $email, $dob, $gender, $rating);
			if($user < 1) {
				//something went wrong, use the code to find out why.
				if($user == ERROR_USER_EXISTS) {
					$this->addVariable('error-message',"Sorry that name is already in use!");
				}
				if($user == ERROR_EMAIL_EXISTS) {
					$this->addVariable('error-message',"Sorry that email is already in use!");
				}
				if($user == ERROR_NAME_RESERVED) {
					$this->addVariable('error-message',"Sorry that username is reserved!");
				}
				if($user == ERROR_INVALID_CHARACTERS) {
					$this->addVariable("error-message","Your username can only contain letters numbers, and spaces.");
				}
				if($user == ERROR_USER_UNDER_AGE) {
					$_SESSION['error-message'] = "You must be at least 14 to register!";
					Header('location: /');
					exit;
				}
				$this->loadView('userRegister');
				exit;
			}
			else {
				$_SESSION['info-message'] = "Check you email for further instructions!";
				$user = new User($user);
				//send out an email
				$user->activate();
				header('location: /');
			}
		}
		elseif($action == "login" && User::isLoggedIn()) {
			Header('Location: /');
		}
		//show the login form
		elseif($action == 'login' && !isset($_POST['name'])) {
			if(isset($_GET['id'])) {
				$_SESSION['login-redirect'] = str_replace('-','/',$_GET['id']);
			}
			$this->addTitle("Login");
			$this->loadView('userLogin',true);
		}
		elseif($action == 'login' && isset($_POST['name'])) {
			if(!System::checkToken($_POST['token'])) {
				//token fail
				$_SESSION['error-message'] = "Oops! Your token didn't seem to match (<a href='/help/token'>?</a>)";
				Header('Location: /');
				exit;
			}
			if(User::authenticate($_POST['name'],$_POST['pass'],isset($_POST['stay']))) {
				$_SESSION['info-message'] = "Welcome back!";
				if(isset($_SESSION['login-redirect'])) {
					header('Location: ' . $_SESSION['login-redirect']);
					unset($_SESSION['login-redirect']);
				}
				else {
					header('Location: /me');
				}
				exit;
			}
			else {
				$this->addVariable('error-message',"Your username or password was not correct.");
				$this->addTitle("Login");
				$this->loadView('userLogin');
			}
		}
  }
}
