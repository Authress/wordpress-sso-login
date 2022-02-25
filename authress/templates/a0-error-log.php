<?php
/**
 * Displays the error log settings page.
 *
 * @package WP-Authress
 *
 * @see WP_Authress_ErrorLog::render_settings_page()
 */

$error_log = new WP_Authress_ErrorLog();
$errors    = $error_log->get();
?>
<div class="a0-wrap settings wrap">

		<h1><?php _e( 'Error Log', 'wp-authress' ); ?></h1>
		<?php if ( ! empty( $errors ) ) : ?>
		<div class="a0-buttons">
			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post" class="js-a0-confirm-submit"
						data-confirm-msg="<?php _e( 'This will delete all error log entries. Proceed?', 'wp-authress' ); ?>">
			<?php wp_nonce_field( WP_Authress_ErrorLog::CLEAR_LOG_NONCE ); ?>
				<input type="hidden" name="action" value="wp_authress_clear_error_log">
				<input type="submit" name="submit" class="button button-primary" value="Clear Log">
			</form>
		</div>
		<?php endif; ?>

	<table class="widefat top-margin">
		<thead>
		<tr>
			<th><?php _e( 'Date', 'wp-authress' ); ?></th>
			<th><?php _e( 'Section', 'wp-authress' ); ?></th>
			<th><?php _e( 'Error code', 'wp-authress' ); ?></th>
			<th><?php _e( 'Message', 'wp-authress' ); ?></th>
			<th><?php _e( 'Count', 'wp-authress' ); ?></th>
		</tr>
		</thead>

		<tbody>
		<?php if ( empty( $errors ) ) : ?>
			<tr>
				<td class="message" colspan="5"><?php _e( 'No errors', 'wp-authress' ); ?></td>
			</tr>
		<?php else : ?>
			<?php
			foreach ( $errors as $item ) :
				?>
				<tr>
					<td><?php echo date( 'm/d/Y H:i:s', $item['date'] ); ?></td>
					<td><?php echo sanitize_text_field( $item['section'] ); ?></td>
					<td><?php echo sanitize_text_field( $item['code'] ); ?></td>
					<td><?php echo sanitize_text_field( $item['message'] ); ?></td>
					<td><?php echo isset( $item['count'] ) ? intval( $item['count'] ) : 1; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
