<?php

class WP_Authress_Embed_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			$this->getWidgetId(),
			$this->getWidgetName(),
			[ 'description' => $this->getWidgetDescription() ]
		);
	}

	protected function getWidgetId() {
		return 'wp_authress_widget';
	}

	protected function getWidgetName() {
		return __( 'Authress Login', 'wp-authress' );
	}

	protected function getWidgetDescription() {
		return __( 'Shows Authress login form in your sidebar', 'wp-authress' );
	}

	protected function showAsModal() {
		return false;
	}

	public function form( $instance ) {
		wp_enqueue_media();
		wp_enqueue_script( 'wp_authress_admin' );
		wp_enqueue_style( 'media' );
		require WP_AUTHRESS_PLUGIN_DIR . 'templates/a0-widget-setup-form.php';
		return 'form';
	}

	public function widget( $args, $instance ) {

		if ( wp_authress_is_ready() ) {

			$instance['show_as_modal']      = $this->showAsModal();
			$instance['modal_trigger_name'] = isset( $instance['modal_trigger_name'] )
				? $instance['modal_trigger_name']
				: __( 'Login', 'wp-authress' );

			if ( ! isset( $instance['redirect_to'] ) || empty( $instance['redirect_to'] ) ) {
				// Null coalescing validates the input variable.
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				$instance['redirect_to'] = home_url( $_SERVER['REQUEST_URI'] ?? '' );
			}

			echo $args['before_widget'];
			\WP_Authress_Lock::render( false, $instance );
			echo $args['after_widget'];

		} else {
			_e( 'Please check your Authress configuration', 'wp-authress' );
		}
	}

	public function update( $new_instance, $old_instance ) {
		$new_instance['dict'] = trim( $new_instance['dict'] );
		if ( $new_instance['dict'] && json_decode( $new_instance['dict'] ) === null ) {
			$new_instance['dict'] = $old_instance['dict'];
		}

		$new_instance['extra_conf'] = trim( $new_instance['extra_conf'] );
		if ( $new_instance['extra_conf'] && json_decode( $new_instance['extra_conf'] ) === null ) {
			$new_instance['extra_conf'] = $old_instance['extra_conf'];
		}

		if ( ! empty( $new_instance['redirect_to'] ) ) {
			$admin_advanced = new WP_Authress_Admin_Advanced(
				WP_Authress_Options::Instance(),
				new WP_Authress_Routes( WP_Authress_Options::Instance() )
			);

			$new_instance['redirect_to'] = $admin_advanced->validate_login_redirect(
				$new_instance['redirect_to'],
				$old_instance['redirect_to']
			);
		}

		return $new_instance;
	}
}
