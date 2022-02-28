<?php
$form_title  = isset( $instance['form_title'] ) ? $instance['form_title'] : '';
$gravatar    = isset( $instance['gravatar'] ) ? $instance['gravatar'] : '';
$icon_url    = isset( $instance['icon_url'] ) ? $instance['icon_url'] : '';
$dict        = isset( $instance['dict'] ) ? $instance['dict'] : '';
$extra_conf  = isset( $instance['extra_conf'] ) ? $instance['extra_conf'] : '';
$redirect_to = isset( $instance['redirect_to'] ) ? $instance['redirect_to'] : '';
?>

<p>
	<strong><?php _e( 'Note', 'wp-authress' ); ?></strong>
		<?php _e( 'The login form will not display for logged-in users.', 'wp-authress' ); ?>
</p>

<?php
if ( $this->showAsModal() ) :
	$modal_trigger_name = isset( $instance['modal_trigger_name'] ) ? $instance['modal_trigger_name'] : '';
	?>
	<p>
		<label for="<?php echo $this->get_field_id( 'modal_trigger_name' ); ?>"><?php _e( 'Button text', 'wp-authress' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'modal_trigger_name' ); ?>"
			   name="<?php echo $this->get_field_name( 'modal_trigger_name' ); ?>"
			   type="text" value="<?php echo esc_attr( $modal_trigger_name ); ?>" />
	</p>
<?php endif; ?>
<p>
	<label for="<?php echo $this->get_field_id( 'form_title' ); ?>"><?php _e( 'Form title:', 'wp-authress' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'form_title' ); ?>"
		   name="<?php echo $this->get_field_name( 'form_title' ); ?>"
		   type="text" value="<?php echo esc_attr( $form_title ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'redirect_to' ); ?>"><?php _e( 'Redirect after login:', 'wp-authress' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'redirect_to' ); ?>"
		   name="<?php echo $this->get_field_name( 'redirect_to' ); ?>"
		   type="text" value="<?php echo esc_attr( $redirect_to ); ?>" />
</p>
<p>
	<label><?php _e( 'Enable Gravatar Integration', 'wp-authress' ); ?></label>
	<br>
	<div class="radio-wrapper">
		<input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_yes"
			   name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
			   type="radio" value="1" <?php echo $gravatar == 1 ? 'checked="true"' : ''; ?> />
		<label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_yes"><?php _e( 'Yes', 'wp-authress' ); ?></label>
		&nbsp;
		<input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"
			   name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
			   type="radio" value="0" <?php echo $gravatar == 0 ? 'checked="true"' : ''; ?> />
		<label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_no"><?php _e( 'No', 'wp-authress' ); ?></label>
		&nbsp;
		<input id="<?php echo $this->get_field_id( 'gravatar' ); ?>_inherit"
			   name="<?php echo $this->get_field_name( 'gravatar' ); ?>"
			   type="radio" value="" <?php echo $gravatar === '' ? 'checked="true"' : ''; ?> />
		<label for="<?php echo $this->get_field_id( 'gravatar' ); ?>_inherit"><?php _e( 'Default Setting', 'wp-authress' ); ?></label>
	</div>

</p>
<p>
	<label for="<?php echo $this->get_field_id( 'icon_url' ); ?>"><?php _e( 'Icon URL:', 'wp-authress' ); ?></label>
	<input type="text" id="<?php echo $this->get_field_id( 'icon_url' ); ?>"
		   name="<?php echo $this->get_field_name( 'icon_url' ); ?>"
		   value="<?php echo esc_attr( $icon_url ); ?>"/>
	<a href="javascript:void(0);" id="wp_authress_choose_icon"
	   related="<?php echo $this->get_field_id( 'icon_url' ); ?>"
	   class="button button-secondary"><?php _e( 'Choose Icon', 'wp-authress' ); ?></a>
	<br><span class="description"><?php _e( 'This image works best as a PNG with a transparent background less than 120px tall', 'wp-authress' ); ?>.</span>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'dict' ); ?>"><?php _e( 'Translation:', 'wp-authress' ); ?></label>
	<textarea class="widefat" id="<?php echo $this->get_field_id( 'dict' ); ?>"
			  name="<?php echo $this->get_field_name( 'dict' ); ?>"><?php echo sanitize_text_field( $dict ); ?></textarea>
	<br><span class="description">
			<?php _e( 'The languageDictionary parameter for the Authress login form. ', 'wp-authress' ); ?>
	</span>
	<br><span class="description">
			<?php
			printf(
				'<a href="https://github.com/authress/lock/blob/master/src/i18n/en.js" target="_blank">%s</a>',
				__( 'List of all modifiable options', 'wp-authress' )
			);
			?>
		</span>
	<br><span class="description">
			<?php
			_e( 'NOTE: This field is deprecated and will be removed in the next major release. ', 'wp-authress' );
			_e( 'Use a languageDictionary property the Extra Settings field below to change text.', 'wp-authress' );
			?>
	</span>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'extra_conf' ); ?>"><?php _e( 'Extra Settings', 'wp-authress' ); ?></label>
	<textarea class="widefat" id="<?php echo $this->get_field_id( 'extra_conf' ); ?>"
			  name="<?php echo $this->get_field_name( 'extra_conf' ); ?>"><?php echo sanitize_text_field( $extra_conf ); ?></textarea>
	<br><span class="description">
			<?php _e( 'Valid JSON for Lock options configuration; will override all options set elsewhere.', 'wp-authress' ); ?>
		<a target="_blank" href="https://authress.io/knowledge-base"><?php _e( 'See options and examples', 'wp-authress' ); ?></a>
		</span>
</p>
