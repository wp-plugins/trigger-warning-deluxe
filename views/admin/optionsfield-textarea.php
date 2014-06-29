<textarea id="<?php esc_attr_e( $id ) ?>" name="<?php echo esc_attr_e( $name ) ?>" rows="5"><?php echo esc_textarea( $value ) ?></textarea>

<?php if( isset( $label ) ) : ?>
<label for="<?php esc_attr_e( $id ) ?>"><?php esc_html_e( $label ) ?></label>
<?php endif ?>