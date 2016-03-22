<?php
class User {

	private $id;
	private $firstName;
	private $email;
	private $phone;
	private $created;
	public function __CONSTRUCT($id) {
		if(!is_numeric ($id)) {
			$id = User::idFromName($id);
		}
		$mysql = System::getDatabaseObject();
		$stmt = $mysql->prepare("SELECT USER_ID FROM users WHERE USER_ID = ?");
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows < 1) { //user does not exist
			$stmt->close();
			throw new Exception(USER_NOT_FOUND);
		}
		$this->id = $id;
		$info = $this->getInfo();

		$this->firstName = $info['first_name'];
		$this->email = $info['email'];
		$this->phone = $info['phone'];
		$this->created = $info['created'];
	}
	/**
	 * get the name of the user from the id provided
	 * @param $id int the id of the user
	 * @return int the user id
	**/
	public static function idFromEmail($email) {
		$mysql = System::getDatabaseObject();
		$stmt = $mysql->prepare("SELECT `USER_ID` FROM `users` WHERE `USER_EMAIL` = ? LIMIT 1");
		$stmt->bind_param('s',$email);
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows < 1) {
			return false;
		}
		else {
			$stmt->bind_result($id);
			$stmt->fetch();
			return $id;
		}
	}

  /**
	 * function to authenticate the user
	 * @param $name String the username
	 * @param $pass String the password
	 * @return boolean wheather the user successfully authenticated
	**/
	public static function authenticate($email,$pass) {
    $mysql = System::getDatabaseObject();
		$stmt = $mysql->prepare("SELECT USER_PASSWORD, USER_ID, USER_ACTIVE FROM users WHERE USER_EMAIL = ?");
		$stmt->bind_param("s",$name);
		$stmt->execute();
		$stmt->store_result();
		if($stmt->num_rows < 1) { //user does not exist
			$stmt->close();
			return false;
		}
		$stmt->bind_result($upass, $id, $active);
		$stmt->fetch();
		$stmt->close();
		if(!password_verify ($pass,$upass)) {
				return false;
		}
		if(!$active) {
			$_SESSION['error-message'] = "User not active, please check your email to continue.";
			header('location: /');
			exit;
		}
		$_SESSION['user'] = $id;
		return true;
	}

  /**
	 * add a new user to the system
 	 * @param $firstName String the First name of the user
	 * @param $password String the password the user selected
	 * @param $email String a valid email
	 * @return int the users id
	 * @throws ERROR_EMAIL_EXISTS if the email exists
	 * @throws ERROR_EMAIL_EMPTY if the email exists
	**/
	public static function addUser($firstName, $password, $email, $dob=null, $gender=3, $rating=PG) {
		$mysql = System::getDatabaseObject();
	  if($email != "") {
			$stmt = $mysql->prepare("SELECT * FROM users WHERE `USER_EMAIL` = ?");
			$stmt->bind_param("s",$email);
			$stmt->execute();
			$stmt->store_result();
			if($stmt->num_rows != 0) { //user already in use
				$stmt->close();
				return ERROR_EMAIL_EXISTS;
			}
			$stmt->close();
			$email = System::clean_string($email);
		}
    else {
      return ERROR_EMAIL_EMPTY;
    }
		$password = password_hash ($password,PASSWORD_DEFAULT);
		$stmt = $mysql->prepare("
					INSERT INTO users (USER_FIRST_NAME, USER_PASSWORD, USER_EMAIL, USER_CREATED)
					VALUES (?,?,?,NOW())
					");
		$firstName = System::clean_string($firstName);
		$stmt->bind_param("sss",$firstName,$password,$email);
		$stmt->execute();
		$id = $stmt->insert_id;
		$stmt->close();
		return $id;
	}

  /**
	 * Function to reset a users password
	 * @param $key String if set checks the key against the current user activation otherwise sends a new key
	 * @return if the key is set boolean wheather the key matches
	**/
	public function resetPassword($key=null) {
		if(!isset($key)) {
			$mysql = System::getDatabaseObject();
			//delete any existing keys from the table
			$stmt = $mysql->prepare("
					DELETE FROM user_activation WHERE UA_ID = ?
				");
			$stmt->bind_param("i",$this->id);
			$stmt->execute();

			//generate a new key
			$key = $this->randomString(75);

			//add to the database
			$stmt = $mysql->prepare("
					INSERT INTO user_activation (UA_ID,UA_KEY,UA_GENERATED) VALUES (?, ?, CURRENT_TIMESTAMP);
				");
			$stmt->bind_param("is",$this->id,$key);
			$stmt->execute();

			//send the email
			$user = $this->getInfo();
			$content = "
				Hi {$user['first_name']},

				You recently sent a request to reset your password.  You can do so by copying and pasting the link below into your
				browser. You will then be prompted to update your password.  If you did not send this password reset request, don't
				worry your account is still safe, and this request will expire in 24 hours.

				http://" . SITE_DOMAIN . "/user/reset/" . urlencode($user['email']) . "/$key

				Thanks,

				Pyrodesign
				";
			$this->sendEmail("Password Reset Requested",$content);
		}
		else {
			$mysql = System::getDatabaseObject();
			$stmt = $mysql->prepare("SELECT UA_ID FROM user_activation WHERE UA_KEY = ? LIMIT 1");
			$stmt->bind_param("s",$key);
			$stmt->execute();
			$stmt->bind_result($user);
			$stmt->store_result();
			if($stmt->num_rows < 1) {
				return false;
			}
			$stmt->fetch();
			if($user != $this->id) {
				return false;
			}
			else {
				return true;
			}
		}
	}
	/**
	 * activate the current user when key is specified, or send an activation email with a new key if not
	 * @param $key String the key, if null a new one is generated and sent
	 * @return if a key is specified boolean if the user is activated null otherwise.
	**/
	public function activate($key=null) {
		if(!isset($key)) {
			$mysql = System::getDatabaseObject();
			//delete any existing keys from the table
			$stmt = $mysql->prepare("
					DELETE FROM user_activation WHERE UA_ID = ?
				");
			$stmt->bind_param("i",$this->id);
			$stmt->execute();

			//generate a new key
			$key = $this->randomString(75);

			//add to the database
			$stmt = $mysql->prepare("
					INSERT INTO user_activation (UA_ID,UA_KEY,UA_GENERATED) VALUES (?, ?, CURRENT_TIMESTAMP);
				");
			$stmt->bind_param("is",$this->id,$key);
			$stmt->execute();

			//send the email
			$user = $this->getInfo();
			$content = "
				Hi {$user['first_name']},

				Welcome to TalesCollective:_ please click on the link below to get started!

				http://" . SITE_DOMAIN . "/user/activate/" . urlencode($user['email']) . "/$key

				Thanks,

				Pyrodesign
				";
			$this->sendEmail("Activate your account",$content);
		}
		else {
			$mysql = System::getDatabaseObject();
			$stmt = $mysql->prepare("SELECT UA_ID FROM user_activation WHERE UA_KEY = ? LIMIT 1");
			$stmt->bind_param("s",$key);
			$stmt->execute();
			$stmt->bind_result($user);
			$stmt->store_result();
			if($stmt->num_rows < 1) {
				return false;
			}
			$stmt->fetch();
			if($user == $this->id) {
				$stmt = $mysql->prepare("UPDATE users SET USER_ACTIVE = 1 WHERE USER_ID = ? LIMIT 1");
				$stmt->bind_param("i",$this->id);
				$stmt->execute();
				return true;
			}
			else {
				return false;
			}
		}
	}
	/**
	 * update the users password, remove any persistant logins the user might have
	 * @param $password String a new password to use
	**/
	public function updatePassword($password) {
		$mysql = System::getDatabaseObject();
		$password = password_hash ($password,PASSWORD_DEFAULT);
		$stmt = $mysql->prepare("
				UPDATE users SET USER_PASSWORD = ?, USER_SALT = null WHERE USER_ID = ?
			");
		$stmt->bind_param("si",$password,$this->id);
		$stmt->execute();
		$stmt->close();
		$stmt = $mysql->prepare("
				DELETE FROM user_activation WHERE UA_ID = ?
			");
		$stmt->bind_param("i",$this->id);
		$stmt->execute();
		$stmt->close();
	}

	/**
	* determines if a user is logged in
	* @param int $user id of user to check, null if current user
	* @return boolean wether the user is logged in
	**/
	public static function isLoggedIn($user=null) {
		$mysql = System::getDatabaseObject();
		if($user == null) {
			if(isset($_SESSION['user'])) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			$stmt = $mysql->prepare("SELECT * FROM users WHERE USER_ID = 1 AND (NOW() - TIMESTAMPDIFF(MINUTE ,NOW(),USER_LAST_MOVE)) < 900");
			$stmt->bind_param("s",$name);
			$stmt->execute();
			$stmt->store_result();
			if($stmt->num_rows < 1) { //user does not exist
				$stmt->close();
				return false;
			}
			else {
				return true;
			}
		}
	}
	/**
	 * logout the current user
	**/
	public function logout() {
		unset($_SESSION['user']);
	}
	public static function currentUser() {
		if(isset($_SESSION['user'])) {
			return $_SESSION['user'];
		}
		else {
			return false;
		}
	}

  /**
	* gets the current users info as an array
	* @return mixed[] the users info
	**/
	public function getInfo() {
		global $mysql;
		$stmt = $mysql->prepare("SELECT USER_FIRST_NAME,USER_EMAIL,USER_PHONE,USER_CREATED FROM users WHERE USER_ID = ? LIMIT 1");
		$stmt->bind_param("i",$this->id);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($firstName,$email,$phone,$created);
		$stmt->fetch();
		$stmt->close();
		return array(
			'name'=>$name,
			'email'=>$email,
			'phone'=>$phone,
			'created'=>$created,
		);
	}

  /**
	 * sends email to the current user
	 * @param $subject String the email subject
	 * @param $content String the content of the message
	 * @param $by int optional defaults to the system user. the id of the sending user
	**/
	public function sendEmail($subject,$content,$by=SYSTEM_USER) {
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/plain; charset=iso-8859-1";
		if($by == SYSTEM_USER) {
			$headers[] = "From: " . SYSTEM_USER . " <".SYSTEM_EMAIL.">";

		}
		elseif(!User::isUser($by)) {
			$headers[] = "From: " . $by;
		}
		else {
			$sender = new User($by);
			$by = $sender->getInfo();
			$headers[] = "From: {$by['name']} <{$by['email']}>";
		}
		$headers[] = "Subject: {$subject}";
		$to = $this->getinfo();
		mail($to['email'], $subject, $content, implode("\r\n", $headers));
	}

  /**
	 * Get the users phone number
	 * @return String the phone number
	 **/
	public function getPhoneNumber() {
		return $this->phone;
	}
	/**
	 * gets the current users id
	 * @return int the users id
	**/
	public function getId() {
		return $this->id;
	}
	/**
	 * get the current users email address
	 * @return String the users email
	**/
	public function getEmail() {
		return $this->email;
	}
  /**
   * get the current users first name
   * @return String the users first name
  **/
  public function getFirstName() {
    return $this->firstName;
  }


  /**
	 * set the users phone number
	 * @param number String the phone number
	 **/
	public function setPhoneNumber($phone) {
    $mysql = System::getDatabaseObject();
    $this->phone = $phone;
    $stmt = $mysql->prepare("UPDATE `users` SET `USER_PHONE` = ? WHERE `USER_ID` = ? LIMIT 1");
    $stmt->bind_param("si",$phone, $this->id);
	}

	/**
	 * set the users email address
	 * @param $email String the users email
	**/
	public function setEmail($email) {
    $mysql = System::getDatabaseObject();
    $stmt = $mysql->prepare("SELECT * FROM users WHERE `USER_EMAIL` = ?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows != 0) { //user already in use
      $stmt->close();
      throw new Exception(ERROR_EMAIL_EXISTS)
    }
    $stmt->close();
    $this->email = $email;
    $stmt = $mysql->prepare("UPDATE `users` SET `USER_EMAIL` = ? WHERE `USER_ID` = ? LIMIT 1");
    $stmt->bind_param("si",$email, $this->id);
	}
  /**
   * get the current users first name
   * @return $firstName String the users first name
  **/
  public function setFirstName($firstName) {
    $mysql = System::getDatabaseObject();
    $this->firstName = $firstName;
    $stmt = $mysql->prepare("UPDATE `users` SET `USER_FIRST_NAME` = ? WHERE `USER_ID` = ? LIMIT 1");
    $stmt->bind_param("si",$firstName, $this->id);
  }
}
