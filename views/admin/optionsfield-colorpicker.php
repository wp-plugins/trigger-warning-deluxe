<input type="color" class="twd-color-field" id="<?php esc_attr_e( $id ) ?>" name="<?php esc_attr_e( $name ) ?>" value="<?php esc_attr_e( $value ) ?>" data-default-color="<?php esc_attr_e( $default ) ?>"/>

<?php if( isset( $label ) ) : ?>
<label for="<?php esc_attr_e( $id ) ?>"><?php esc_html_e( $label ) ?></label>
<?php endif ?>