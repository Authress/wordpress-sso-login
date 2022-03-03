<?php
class Authress_Sso_Login_Serializer {

	public static function serialize( $o ) {
		return wp_json_encode( $o );
	}

	public static function unserialize( $s ) {
		if ( ! is_string( $s ) || trim( $s ) === '' ) {
			return null;
		}

		try {
			return json_decode( $s );
		} catch ( Exception $e ) {
			return null;
		}
	}

}
