<?php

class Authress_Sso_Login_ErrorLog {
	public static $errors = [];

	public static function insert_error( $section, $error ) {
		self::$errors[] = $error;
	}
}
