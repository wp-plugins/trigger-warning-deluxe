<input type="text" id="<?php esc_attr_e( $id ) ?>" name="<?php esc_attr_e( $name ) ?>" value="<?php esc_attr_e( $value ) ?>"/>

<?php if( isset( $label ) ) : ?>
<label for="<?php esc_attr_e( $id ) ?>"><?php esc_html_e( $label ) ?></label>
<?php endif ?>