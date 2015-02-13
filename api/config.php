<?php

//database connection info
define("_db_host","localhost");
define("_db_name","");
define("_db_username","");
define("_db_password","");

define("hash_algo","sha256"); // hashing algorithm for HMAC signatures (eg, md5 or sha1) 

define("hash_salt","s0diumChr0r1d3!"); // create a your own random string here

//encryption credentials
define("ENCRYPT_CYPHER", MCRYPT_RIJNDAEL_256);
define("ENCRYPT_MODE",   MCRYPT_MODE_CBC);
define("ENCRYPT_KEY",    md5("HowManyProgrammersDoesItTakeToChangeALightbulb?None:thatsAHardwareIssue") ); // CHANGE THIS
define('ENCRYPT_EOT','___EOT'); // an "end of transfer" delimiter to append to the data before encrypting (a fix for .docx and .xlsx files)

include_once 'idiorm.php';
ORM::configure('mysql:host='. _db_host .';dbname=' ._db_name);
ORM::configure('username', _db_username);
ORM::configure('password', _db_password);

/**
 * Encrypt a string
 *
 * ENCRYPT_CYPHER, ENCRYPT_MODE and ENCRYPT_KEY are defined in config.php
 * Besure to change the ENCRYPT_KEY from the default value.
 *
 */
function encrypt($plaintext)
{
	$plaintext .= ENCRYPT_EOT;
	$td = mcrypt_module_open(ENCRYPT_CYPHER, '', ENCRYPT_MODE, '');
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, ENCRYPT_KEY, $iv);
	$crypttext = mcrypt_generic($td, $plaintext);
	mcrypt_generic_deinit($td);
	return base64_encode($iv.$crypttext);
}

/** 
 * Decrypt a previously encrypted string
 *
 */
function decrypt($crypttext)
{
	$crypttext = base64_decode($crypttext);
    $plaintext = '';
    $td        = mcrypt_module_open(ENCRYPT_CYPHER, '', ENCRYPT_MODE, '');
    $ivsize    = mcrypt_enc_get_iv_size($td);
    $iv        = substr($crypttext, 0, $ivsize);
    $crypttext = substr($crypttext, $ivsize);
    if ($iv)
    {
        mcrypt_generic_init($td, ENCRYPT_KEY, $iv);
        $plaintext = mdecrypt_generic($td, $crypttext);
    }
    return substr($plaintext,0,strpos($plaintext, ENCRYPT_EOT));
} 