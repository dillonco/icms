<?php
namespace Nixhatter\ICMS;

/**
 * Database Connection Class
 * Grabs the credentials from the configuration.php file and starts a connection
 * Credentials are encoded, so we need to decode them first. Encoding is still a work in progress
 *
 * User: Dillon
 * Date: 3/23/2015
 */

defined('_ICMS') or die;

class Database {
    private $db;

    public function __construct($settings) {
        $error = "Error connecting to the database";

        $config = array(
            'host'    => $settings->production->database->host,
            'username'    => $settings->production->database->user,
            'password'    => $settings->production->database->password,
            'dbname'    => $settings->production->database->name,
            'port'    => $settings->production->database->port
        );

        try {
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            //$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

            //Encryption is just a POC right now, still in development
            $secret_key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");

            $ciphertext_dec = base64_decode($config['password']);

            # retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
            $iv_dec = substr($ciphertext_dec, 0, $iv_size);

            # retrieves the cipher text (everything except the $iv_size in the front)
            $ciphertext_dec = substr($ciphertext_dec, $iv_size);

            # may remove 00h valued characters from end of plain text
            $decrypted_password = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secret_key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
            $this->db = new \PDO('mysql:host=' . $config['host'] . ';port='. $config['port'] .'; dbname=' . $config['dbname'], $config['username'], $decrypted_password);
            $this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            if($settings->production->debug === "true") {
                $error = "Database Error: " . $e->getMessage();
            }
            exit($error);
        } catch (Exception $e) {
            if($settings->production->debug  === "true") {
                $error = "Caught Exception: " . $e->getMessage();
            }
            exit($error);
        }
    }
    public function load() {
        return $this->db;
    }
}
