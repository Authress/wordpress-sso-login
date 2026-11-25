<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

function wp_stub_reset( $capabilities = [ 'manage_options' ] ) {
	$GLOBALS['wp_stub_capabilities'] = $capabilities;
	$GLOBALS['wp_stub_options']      = [];
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

function get_option( $name, $default = false ) {
	return $GLOBALS['wp_stub_options'][ $name ] ?? $default;
}

function update_option( $name, $value ) {
	$GLOBALS['wp_stub_options'][ $name ] = $value;
	return true;
}

function home_url() {
	return 'https://site.example';
}

function authress_debug_log( $message ) {
}

function wp_json_encode( $value ) {
	return json_encode( $value );
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}

function __( $text ) {
	return $text;
}

require_once __DIR__ . '/ErrorLogStub.php';
require_once __DIR__ . '/JwksStubLoginManager.php';
