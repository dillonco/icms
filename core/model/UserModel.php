<?php
/**
 * ICMS - Intelligent Content Management System
 *
 * @package ICMS
 * @author Dillon Aykac
 */

/*
|--------------------------------------------------------------------------
| User Model
|--------------------------------------------------------------------------
|
| Actions relating to users and permissions
|
*/
class UserModel extends Model{
    public $posts;
    public $user;
    public $user_id;
    public $container;
    private $settings;

    public function __construct(\Pimple\Container $container) {
        $this->container = $container;
        $this->db       = $container['db'];
        $blog           = new BlogModel($container);
        $this->settings = $container['settings'];
        $this->posts    = $blog->get_posts();
    }

    public function update_user($username, $full_name, $gender, $bio, $image_location, $id)
    {
        $query = $this->db->prepare("UPDATE `users` SET
								`username`	= ?,
								`full_name`		= ?,
								`gender`		= ?,
								`bio`			= ?,
								`image_location`= ?

								WHERE `id` 		= ?
								");

        $query->bindValue(1, $username);
        $query->bindValue(2, $full_name);
        $query->bindValue(3, $gender);
        $query->bindValue(4, $bio);
        $query->bindValue(5, $image_location);
        $query->bindValue(6, $id);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function change_password($user_id, $password)
    {

        $password_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

        $query = $this->db->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");

        $query->bindValue(1, $password_hash);
        $query->bindValue(2, $user_id);

        try {
            $query->execute();
            return true;
        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }

    public function start_recover($email)
    {
        $site_url = $this->settings->production->site->url;
        $site_name = $this->settings->production->site->name;

        $username = $this->fetch_info('username', 'email', $email);// We want the 'id' WHERE 'email' = user's email ($email)

        $unique = uniqid('',true);
        $random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),0, 10);

        $generated_string = $unique . $random; // a random and unique string

        $query = $this->db->prepare("UPDATE `users` SET `generated_string` = ? WHERE `email` = ?");

        $query->bindValue(1, $generated_string);
        $query->bindValue(2, $email);

        try {

            $query->execute();

            $subject =  'Recover Password';
            $body =  "Hello " . $username. ",
            Please click the link below:
            http://". $site_url."/user/recover/endRecover?email=" . $email . "&recover_code=" . $generated_string . "
            We will generate a new password for you and send it back to your email.
            Thank you!";
            $this->mail($email, $username, $subject, $body);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function endRecover($email, $recoverCode)
    {
        $query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `email` = ? AND `generated_string` = ?");
        $query->bindValue(1, $email);
        $query->bindValue(2, $recoverCode);

        try {
            $query->execute();
            $rows = $query->fetchColumn();

            if ($rows == 1) {

                $username = $this->fetch_info('username', 'email', $email);
                $user_id  = $this->fetch_info('id', 'email', $email);

                $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $generated_password = substr(str_shuffle($charset),0, 10);

                $this->change_password($user_id, $generated_password);

                $query = $this->db->prepare("UPDATE `users` SET `generated_string` = 0 WHERE `id` = ?");

                $query->bindValue(1, $user_id);

                $query->execute();

                $subject = 'Recover Password';
                $body = "Hello " . $username . ",
                Your your new password is: " . $generated_password . "
                Please change your password once you have logged in.
                Thank you!";
                $this->mail($email, $username, $subject, $body);
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }

    public function fetch_info($what, $field, $value)
    {
        $allowed = array('id', 'username', 'full_name', 'gender', 'bio', 'email');
        if (!in_array($what, $allowed, true) || !in_array($field, $allowed, true)) {
            throw new InvalidArgumentException();
        } else {

            $query = $this->db->prepare("SELECT $what FROM `users` WHERE $field = ?");

            $query->bindValue(1, $value);

            try {
                $query->execute();
            } catch (PDOException $e) {
                die($e->getMessage());
            }
            return $query->fetchColumn();
        }
    }


    public function user_exists($username)
    {
        $query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `username`= ?");
        $query->bindValue(1, $username);

        try {

            $query->execute();
            $rows = $query->fetchColumn();

            if ($rows == 1) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }

    public function email_exists($email)
    {
        $query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `email`= ?");
        $query->bindValue(1, $email);

        try {

            $query->execute();
            $rows = $query->fetchColumn();

            if ($rows == 1) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }

    public function register($username, $password, $email)
    {
        $time = time();
        $ip = $_SERVER['REMOTE_ADDR'];
        $email_code = $email_code = uniqid('code_',true); // Creating a unique string.

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

        $query    = $this->db->prepare("INSERT INTO `users` (`username`, `password`, `email`, `ip`, `time`, `email_code`) VALUES (?, ?, ?, ?, ?, ?) ");

        $query->bindValue(1, $username);
        $query->bindValue(2, $hashedPassword);
        $query->bindValue(3, $email);
        $query->bindValue(4, $ip);
        $query->bindValue(5, $time);
        $query->bindValue(6, $email_code);

        try {
            $query->execute();
            $this->register_mail($email, $username);
            return true;

        } catch (PDOException $e) {
            return false;
            die($e->getMessage());
        }
    }

    private function mail($registeredEmail, $registeredUsername, $subject, $body) {
        $email_auth = $this->settings->production->email->auth;
        if($email_auth == "XOAUTH2") {
            $this->oauthMail($registeredEmail, $registeredUsername, $subject, $body);
        } else {
            $this->basicMail($registeredEmail, $registeredUsername, $subject, $body);
        }
    }

    private function oauthMail($registeredEmail, $registeredUsername, $subject, $body) {
        $site_name = $this->settings->production->site->name;
        $site_email = $this->settings->production->site->email;
        $email_host = $this->settings->production->email->host;
        $email_port = $this->settings->production->email->port;
        $email_user = $this->settings->production->email->user;
        $email_clientid = $this->settings->production->email->clientid;
        $email_clientsecret = $this->settings->production->email->clientsecret;
        $email_refreshtoken = $this->settings->production->email->refreshtoken;

        $mail = new PHPMailerOAuth;
        $mail->SMTPDebug = 0;
        $mail->isSMTP();                                    // Set mailer to use SMTP
        $mail->Host = $email_host;                          // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;
        $mail->AuthType = "XOAUTH2";
        //User Email to use for SMTP authentication - Use the same Email used in Google Developer Console
        $mail->oauthUserEmail = $email_user;
        //Obtained From Google Developer Console
        $mail->oauthClientId = $email_clientid;
        //Obtained From Google Developer Console
        $mail->oauthClientSecret = $email_clientsecret;
        //Obtained By running get_oauth_token.php after setting up APP in Google Developer Console.
        //Set Redirect URI in Developer Console as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
        // eg: http://localhost/phpmail/get_oauth_token.php
        $mail->oauthRefreshToken = $email_refreshtoken;
        $mail->Username = $email_user;                      // SMTP username
        //$mail->Password = $email_pass;                      // SMTP password
        $mail->SMTPSecure = 'tls';                          // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $email_port;                          // TCP port to connect to

        $mail->setFrom($site_email, $site_name);
        $mail->addAddress($registeredEmail, $registeredUsername);               // Add a recipient
        $mail->addReplyTo($site_email, $site_name);

        //$mail->isHTML(true);                                // Set email format to HTML

        $mail->Subject = $subject;
        $mail->Body    = $body;

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    private function basicMail($registeredEmail, $registeredUsername, $subject, $body) {
        $site_name = $this->settings->production->site->name;
        $site_email = $this->settings->production->site->email;
        $email_host = $this->settings->production->email->host;
        $email_port = $this->settings->production->email->port;
        $email_user = $this->settings->production->email->user;
        $email_pass = $this->settings->production->email->pass;
        $mail = new PHPMailer;

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $email_host;  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $email_user;                 // SMTP username
        $mail->Password = $email_pass;                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $email_port;                                    // TCP port to connect to

        $mail->addAddress($registeredEmail, $registeredUsername);               // Add a recipient
        $mail->addReplyTo($site_email, $site_name);

        //$mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $subject;
        $mail->Body    = $body;

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
    public function register_mail($registeredEmail, $registeredUsername) {
        $site_url = $this->settings->production->site->url;
        $site_name = $this->settings->production->site->name;


        $email_code = uniqid('code_',true); // Creating a unique string.
        $query    = $this->db->prepare("UPDATE `users` SET `email_code` = ? WHERE `email` = ?");
        $query->bindValue(1, $email_code);
        $query->bindValue(2, $registeredEmail);
        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        $subject = $site_name . ' - Please Activate your Account';
        $body    = "Hey " . $registeredUsername. ",
        Please visit the link below so we can activate your account:
        http://".$site_url."/user/register/activate?email=".$registeredEmail."&code=" . $email_code . "
        -- ".$site_name;

        $this->mail($registeredEmail, $registeredUsername, $subject, $body);

    }

    public function activate($email, $email_code)
    {
        $query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `email` = ? AND `email_code` = ? AND `confirmed` = ?");

        $query->bindValue(1, $email);
        $query->bindValue(2, $email_code);
        $query->bindValue(3, 0);

        try {
            $query->execute();
            $rows = $query->fetchColumn();
            if ($rows == 1) {
                $query_2 = $this->db->prepare("UPDATE `users` SET `confirmed` = ? WHERE `email` = ?");
                $query_2->bindValue(1, 1);
                $query_2->bindValue(2, $email);
                $query_2->execute();
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function email_confirmed($username)
    {
        $query = $this->db->prepare("SELECT COUNT(`id`) FROM `users` WHERE `username`= ? AND `confirmed` = ?");
        $query->bindValue(1, $username);
        $query->bindValue(2, 1);

        try {

            $query->execute();
            $rows = $query->fetchColumn();

            if ($rows == 1) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }

    public function login($username, $password)
    {

        $query = $this->db->prepare("SELECT `password`, `id` FROM `users` WHERE `username` = ?");
        $query->bindValue(1, $username);

        try {

            $query->execute();
            $data              = $query->fetch();
            $stored_password   = $data['password']; // stored hashed password
            $user_id           = $data['id']; // id of the user to be returned if the password is verified, below.

            if ($this->compare($password, $stored_password)) {
                if (password_needs_rehash($stored_password, PASSWORD_DEFAULT, ['cost' => 12])) {
                    $this->change_password($user_id, $password);
                }
                return $user_id;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }

    public function userdata($id)
    {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `id`= ?");
        $query->bindValue(1, $id);

        try {

            $query->execute();

            return $query->fetch();

        } catch (PDOException $e) {

            die($e->getMessage());
        }

    }

    public function get_users()
    {
        $query = $this->db->prepare("SELECT * FROM `users` ORDER BY `time` DESC");

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        return $query->fetchAll();

    }
    public function delete_user($ID)
    {
        $query = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $query->bindValue(1, $ID);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        return true;
    }
    public function get_user_permission($ID)
    {
        $query = $this->db->prepare("SELECT `permission` FROM `users` WHERE id = ?");
        $query->bindValue(1, $ID);

        try {
            $query->execute();

            return $query->fetch();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /* Gen Salt */
    private function genSalt()
    {
        $string = str_shuffle(mt_rand());
        $salt    = uniqid($string ,true);

        return $salt;
    }

    /* Gen Hash */
    public function genHash($password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        return $hash;
    }

    /* Compare passwords */
    public function compare($password, $passwordHash)
    {
        try {
            if (password_verify($password, $passwordHash)) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            die("Passwords do not match" . $e->getMessage());
        }
    }

    /* PERMISISSIONS */

    public function has_access($userID, $pageName, $usergroupID)
    {

        $query = $this->db->prepare("SELECT * FROM `permissions` WHERE `pageName` = ? AND (`userID`= ?  OR `usergroupID` = ? OR `usergroupID`= ? OR `usergroupID`= ? )");
        $query->bindValue(1, $pageName);
        $query->bindValue(2, $userID);
        $query->bindValue(3, $usergroupID);
        $query->bindValue(4, "guest");
        if (isset($userID)) $query->bindValue(5, "user"); else $query->bindValue(5, "");

        try {

            $query->execute();
            $rows = $query->fetch(PDO::FETCH_ASSOC);

            if ($rows) {
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }
    public function user_access($userID, $pageName)
    {
        if (!empty($userID)) {
            $query = $this->db->prepare("SELECT * FROM `permissions` WHERE `pageName` = ? AND `usergroupID` = 'user'");
            $query->bindValue(1, $pageName);

            try {

                $query->execute();
                $rows = $query->fetch(PDO::FETCH_ASSOC);

                if (!$rows) {
                    return false;
                } else {
                    return true;
                }

            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
    }

    public function add_permission($userID, $pageName)
    {
        $query    = $this->db->prepare("INSERT INTO `permissions` (`userID`, `pageName`) VALUES (?, ?) ");

        $query->bindValue(1, $userID);
        $query->bindValue(2, $pageName);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function delete_permission($userID, $pageName)
    {
        $query    = $this->db->prepare("DELETE FROM `permissions` WHERE `userID` = ? AND `pageName` = ?");

        $query->bindValue(1, $userID);
        $query->bindValue(2, $pageName);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function add_usergroup($usergroupID, $pageName)
    {
        $query    = $this->db->prepare("INSERT INTO `permissions` (`usergroupID`, `pageName`) VALUES (?, ?) ");

        $query->bindValue(1, $usergroupID);
        $query->bindValue(2, $pageName);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function delete_usergroup($usergroupID, $pageName)
    {
        $query    = $this->db->prepare("DELETE FROM `permissions` WHERE `usergroupID` = ? AND `pageName` = ?");

        $query->bindValue(1, $usergroupID);
        $query->bindValue(2, $pageName);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function get_permission($id)
    {
        $query = $this->db->prepare("SELECT * FROM `permissions` WHERE `userID`= ? or `usergroupID` = ?");
        $query->bindValue(1, $id);
        $query->bindValue(2, $id);

        try {

            $query->execute();

        } catch (PDOException $e) {

            die($e->getMessage());
        }

        return $query->fetch();
    }
    public function get_permissions()
    {
        $query = $this->db->prepare("SELECT * FROM `permissions` WHERE `userID` IS NOT NULL ORDER BY `userID` DESC");

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        return $query->fetchAll();
    }
    public function get_usergroups()
    {
        $query = $this->db->prepare("SELECT * FROM `permissions` WHERE `usergroupID` IS NOT NULL ORDER BY `usergroupID` ASC");

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        return $query->fetchAll();
    }
    public function delete_all_page_permissions($pageName)
    {
        $query    = $this->db->prepare("DELETE FROM `permissions` WHERE  `pageName` = ?");

        $query->bindValue(1, $pageName);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    public function delete_all_user_permissions($userID)
    {
        $query    = $this->db->prepare("DELETE FROM `permissions` WHERE  `userID` = ?");

        $query->bindValue(1, $userID);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    public function delete_all_usergroup_permissions($usergroupID)
    {
        $query    = $this->db->prepare("DELETE FROM `permissions` WHERE  `usergroupID` = ?");

        $query->bindValue(1, $usergroupID);

        try {
            $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}