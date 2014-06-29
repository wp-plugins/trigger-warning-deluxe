<div id="trigger-warning-deluxe-edit-box" class="trigger-warning-deluxe">
	<p>
		<input id="twd-toggle-trigger" type="checkbox" name="trigger_warning_deluxe[has_trigger]" class="toggle-trigger" <?php checked( !! $trigger->has_trigger ) ?>/>
		<label for="twd-toggle-trigger"><?php _e( 'Veil this post with a trigger warning?', 'trigger-warning-deluxe' ) ?></label>
	</p>
	<div class="field-rows">
		<div class="field-row">
			<label>Title Label</label>
			<input type="text" name="trigger_warning_deluxe[warning_label]" placeholder="<?php esc_attr_e( $defaultWarningLabel ) ?>" value="<?php esc_attr_e( $trigger->warning_label ) ?>"/>
		</div>
		<div class="field-row">
			<label>Warning Message</label>
			<textarea name="trigger_warning_deluxe[warning]" rows="5" placeholder="<?php esc_attr_e( $defaultWarning ) ?>"><?php echo esc_textarea( $trigger->warning ) ?></textarea>
		</div>
	</div>
	<input type="hidden" name="trigger_warning_deluxe_editbox_nonce" id="trigger_warning_deluxe_editbox_nonce" value="<?php echo wp_create_nonce( 'trigger_warning_deluxe_editbox_nonce' ) ?>"/>
</div>