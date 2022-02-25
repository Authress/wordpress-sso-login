<?php

class WP_Authress_Popup_Widget extends WP_Authress_Embed_Widget {

	protected function getWidgetId() {
		return 'wp_authress_popup_widget';
	}

	protected function getWidgetName() {
		return __( 'Authress Popup Login', 'wp-authress' );
	}

	protected function getWidgetDescription() {
		return __( 'Shows a button to pop up an Authress login form in your sidebar', 'wp-authress' );
	}

	protected function showAsModal() {
		return true;
	}

}
