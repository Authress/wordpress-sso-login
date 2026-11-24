<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

function wp_stub_reset( $capabilities = [ 'manage_options' ] ) {
	$GLOBALS['wp_stub_capabilities'] = $capabilities;
}

function current_user_can( $capability ) {
	return in_array( $capability, $GLOBALS['wp_stub_capabilities'], true );
}

function sanitize_text_field( $value ) {
	return trim( preg_replace( '/<[^>]*>/', '', $value ) );
}

function wp_unslash( $value ) {
	return $value;
}
