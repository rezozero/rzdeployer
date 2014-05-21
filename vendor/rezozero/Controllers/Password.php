<?php 
/**
 * Copyright REZO ZERO 2014
 * 
 * 
 * 
 *
 * @file Password.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Controllers;

class Password {

	public static function encrypt( $password )
	{
		$results = null;
		exec('openssl passwd -crypt "'.$password.'"', $results);

		return $results[0];
	}

	public static function generate( $length = 8 )
	{
		//cat /dev/urandom| tr -dc 'a-zA-Z0-9' | fold -w 12| head -n 1
		$results = array();
		$query = "openssl rand -base64 36 | tr -dc 'a-zA-Z0-9\-\_\\$\@' | fold -w ".(int)$length." | head -n 1";

		exec($query, $results);

		return $results[0];
	}
}