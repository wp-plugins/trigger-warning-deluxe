<?php foreach( $values as $index => $value ) : ?>
	<input type="radio" id="<?php esc_attr_e( "{$id}_$index" ) ?>" name="<?php esc_attr_e( $name ) ?>" value="<?php esc_attr_e( $value ) ?>" <?php checked( $checked[ $index ] ) ?>/>

	<?php if( isset( $labels[ $index ] ) ) : ?>
	<label for="<?php esc_attr_e( "{$id}_$index" ) ?>"><?php esc_html_e( $labels[ $index ] ) ?></label>
	<?php endif ?>
<?php endforeach ?>