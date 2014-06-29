<?php if( $trigger->has_trigger ) : ?>
<div class="trigger-warning-deluxe">
	<strong><?php esc_html_e( ! empty( $trigger->warning_label ) ? $trigger->warning_label : $defaultWarningLabel ) ?></strong>
	<p><?php esc_html_e( ! empty( $trigger->warning ) ? $trigger->warning : $defaultWarning ) ?></p>
</div>
<?php endif ?>