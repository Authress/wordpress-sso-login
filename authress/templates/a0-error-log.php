<?php
/**
 * Displays the error log settings page.
 *
 * @package WP-Authress
 *
 * @see Authress_Sso_Login_ErrorLog::render_settings_page()
 */

	$authress_errors_found = (new Authress_Sso_Login_ErrorLog())->get();
?>
<div class="a0-wrap settings wrap">

		<h1><?php esc_attr_e( 'Error Log', 'wp-authress' ); ?></h1>
		<?php if ( ! empty( $authress_errors_found ) ) : ?>
		<div class="a0-buttons">
			<form action="<?php echo esc_attr(admin_url( 'options.php' )); ?>" method="post" class="js-a0-confirm-submit"
						data-confirm-msg="<?php esc_attr_e( 'This will delete all error log entries. Proceed?', 'wp-authress' ); ?>">
			<?php wp_nonce_field( Authress_Sso_Login_ErrorLog::CLEAR_LOG_NONCE ); ?>
				<input type="hidden" name="action" value="authress_sso_login_clear_error_log">
				<input type="submit" name="submit" class="button button-primary" value="Clear Log">
			</form>
		</div>
		<?php endif; ?>

	<table class="widefat top-margin">
		<thead>
		<tr>
			<th><?php esc_attr_e( 'Date', 'wp-authress' ); ?></th>
			<th><?php esc_attr_e( 'Section', 'wp-authress' ); ?></th>
			<th><?php esc_attr_e( 'Error code', 'wp-authress' ); ?></th>
			<th><?php esc_attr_e( 'Message', 'wp-authress' ); ?></th>
			<th><?php esc_attr_e( 'Count', 'wp-authress' ); ?></th>
		</tr>
		</thead>

		<tbody>
		<?php if ( empty( $authress_errors_found ) ) : ?>
			<tr>
				<td class="message" colspan="5"><?php esc_attr_e( 'No errors', 'wp-authress' ); ?></td>
			</tr>
		<?php else : ?>
			<?php
			foreach ( $authress_errors_found as $item ) :
				?>
				<tr>
					<td><?php echo esc_attr(gmdate( 'm/d/Y H:i:s', $item['date'] )); ?></td>
					<td><?php echo esc_attr( $item['section'] ); ?></td>
					<td><?php echo esc_attr( $item['code'] ); ?></td>
					<td><?php echo esc_attr( $item['message'] ); ?></td>
					<td><?php echo isset( $item['count'] ) ? esc_attr(intval( $item['count'] )) : 1; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
