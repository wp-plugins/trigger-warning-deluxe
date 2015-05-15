<?php if( $trigger->has_trigger ) : ?>
<div class="trigger-warning-deluxe">
	<strong><?php esc_html_e( ! empty( $trigger->warning_label ) ? $trigger->warning_label : $defaultwarninglabel ) ?></strong>
	<p><?php esc_html_e( ! empty( $trigger->warning ) ? $trigger->warning : $defaultwarning ) ?></p>
</div>
<?php endif ?>