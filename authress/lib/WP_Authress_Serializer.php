<?php
class WP_Authress_Serializer {

	public static function serialize( $o ) {
		return json_encode( $o );
	}

	public static function unserialize( $s ) {
		if ( ! is_string( $s ) || trim( $s ) === '' ) {
			return null;
		}

		if ( $s[0] === '{' ) {
			return json_decode( $s );
		}

		return @unserialize( $s );
	}

}
