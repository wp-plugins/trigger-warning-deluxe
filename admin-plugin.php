<?php

/**
 * Loads administrative capabilities for configuring Trigger Warning Deluxe.
 */
class TWD_WordPressAdminIntegration {
	const admin_page = 'trigger-warning-deluxe';

	private static $instance;
	private $plugin;
	private $settingsManager;

	static function instance() {
		if( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

	function __construct() {
		$api = new TWD_WordPressBridge;
		$this->plugin = new TriggerWarningDeluxe( $api );
		$this->settingsManager = new TWD_WordPressAdminSettings( $this->plugin->config() );
	}

	// To be hooked into admin_init
	function admin_init() {
		register_setting( TriggerWarningDeluxe::slug, TriggerWarningDeluxe::slug, array( $this->settingsManager, 'validateFields' ) );
		$this->settingsManager->addSection( 'general-settings-section', __( 'General Settings', 'trigger-warning-deluxe' ) );

		// Add theme options here
		$this->settingsManager->addField(
			'textfield',
			'default-warning-label',
			__( 'Default warning label', 'trigger-warning-deluxe' ),
			false,
			__( 'The default warning label is displayed in the post title if not specified by the post', 'trigger-warning-deluxe' )
		);

		$this->settingsManager->addField(
			'textarea',
			'default-warning',
			__( 'Default warning', 'trigger-warning-deluxe' ),
			false,
			__( 'The default warning is displayed in the post if not specified by the post', 'trigger-warning-deluxe' )
		);
	}

	// To be hooked into wp_enqueue_scripts or another appropriate hook suitable for enqueuing scripts.
	function admin_enqueue_scripts( $context ) {
		wp_enqueue_style( 'trigger-warning-deluxe-admin-styles', plugins_url( '/assets/css/admin-styles.css', __FILE__ ) );
		wp_enqueue_script( 'trigger-warning-deluxe-admin-scripts', plugins_url( '/assets/js/admin-scripts.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ) );
	}

	// To be hooked into admin_menu.
	function admin_menu() {
		add_menu_page(
			__( 'Trigger Warning Deluxe', 'trigger-warning-deluxe' ),
			__( 'Trigger Warning Deluxe', 'trigger-warning-deluxe' ),
			'manage_options',
			self::admin_page,
			array( $this, 'trigger_warning_admin_page' ),
			'dashicons-info'
		);
	}

	// Callback for loading the admin view.
	function trigger_warning_admin_page() {
		include 'views/admin/twd-admin.php';
	}

	function add_meta_boxes() {
		add_meta_box(
			TriggerWarningDeluxe::slug . '-meta-box',
			__( 'Trigger Warning', 'trigger-warning-deluxe' ),
			array( $this, 'displayTriggerWarningMetabox' ),
			'post',
			'side',
			'default'
		);
	}

	function displayTriggerWarningMetabox( $post ) {
		$trigger = $this->plugin->getTriggerWarningDataForPost( $post->ID );
		$defaultWarningLabel = $this->plugin->config( 'default-warning-label' );
		$defaultWarning = $this->plugin->config( 'default-warning' );

		include 'views/admin/posttriggereditbox.php';
	}

	function save_post( $postid ) {
		$nonce = filter_input( INPUT_POST, 'trigger_warning_deluxe_editbox_nonce', FILTER_DEFAULT );
		$rawtrigger = filter_input( INPUT_POST, 'trigger_warning_deluxe', FILTER_DEFAULT, array( 'default' => array(), 'flags' => array( FILTER_REQUIRE_ARRAY ) ) );

		if( defined( 'DOING_AUTOSAVE' ) )
			return;

		if( ! current_user_can( 'edit_post', $postid ) )
			return;

		if( ! wp_verify_nonce( $nonce, 'trigger_warning_deluxe_editbox_nonce' ) )
			return;

		//save trigger customization
		$trigger = new TWD_TriggerMeta( $rawtrigger );
		$this->plugin->persistTriggerWarningDataForPost( $postid, $trigger );
	}

	function manage_posts_columns( $columns ) {
		$columns[ TriggerWarningDeluxe::slug ] = __( 'Trigger Warning', 'trigger-warning-deluxe' );
		return $columns;
	}

	function manage_posts_custom_column( $column ) {
		if( TriggerWarningDeluxe::slug == $column ) {
			$trigger = $this->plugin->getTriggerWarningDataForPost( get_the_id() );
			$defaultwarninglabel = $this->plugin->config( 'default-warning-label' );
			$defaultwarning = $this->plugin->config( 'default-warning' );

			include 'views/admin/posttriggercolumn.php';
		}
	}
}

class TWD_WordPressAdminSettings {
	private $registeredfields;
	private $config;

	function __construct( array $config ) {
		$this->registeredfields = array();
		$this->config = $config;
	}

	// Use this for adding sections to the admin page.
	function addSection( $id, $title ) {
		add_settings_section($id, $title, array( $this, 'sectionHeader'), TWD_WordPressAdminIntegration::admin_page );
	}

	// Use this for adding theme options to the admin page. See code below for available types (renderers).
	function addField( $type, $id, $title, $value = 1, $label = null, $args = array(), $section = 'general-settings-section' ) {
		$this->_addFieldFilter( $type, $id, $title, $value, $args );
		add_settings_field( $id, $title, array( $this, "{$type}Renderer" ), TWD_WordPressAdminIntegration::admin_page, $section, compact( 'type', 'id', 'value', 'label', 'args', 'section' ) );
	}

	// Callback for displaying a section header.
	function sectionHeader( $args ) {}

	// Renders a textfield.
	function textfieldRenderer( $args ) {
		$setting = $this->config[ $args[ 'id' ] ];

		$id = TriggerWarningDeluxe::slug . '_' . $args[ 'id' ];
		$name = TriggerWarningDeluxe::slug . "[{$args[ 'id' ]}]";
		$value = $setting;
		$label = $args[ 'label' ];

		$this->_fieldRenderer( 'textfield', compact( 'id', 'name', 'value', 'label' ) );
	}

	// Renders a textfield.
	function textareaRenderer( $args ) {
		$setting = $this->config[ $args[ 'id' ] ];

		$id = TriggerWarningDeluxe::slug . '_' . $args[ 'id' ];
		$name = TriggerWarningDeluxe::slug . "[{$args[ 'id' ]}]";
		$default = $args[ 'value' ];
		$value = $setting;
		$label = $args[ 'label' ];

		$this->_fieldRenderer( 'textarea', compact( 'id', 'name', 'value', 'label' ) );
	}

	// Renders a colourpicker field. Gracefully falls back to regular input box when not supported.
	function colorpickerRenderer( $args ) {
		$setting = $this->config[ $args[ 'id' ] ];

		$id = TriggerWarningDeluxe::slug . '_' . $args[ 'id' ];
		$name = TriggerWarningDeluxe::slug . "[{$args[ 'id' ]}]";
		$value = $setting or $value = $args[ 'value' ];
		$label = $args[ 'label' ];

		self::_fieldRenderer( 'colorpicker', compact( 'id', 'name', 'value', 'label' ) );
	}

	// Renders a checkbox. If multiple values are provided, an option group will be rendered.
	function checkboxRenderer( $args ) {
		$setting = (array) $this->config[ $args[ 'id' ] ];
		$multivalue = is_array( $args[ 'value' ] );

		$id = TriggerWarningDeluxe::slug . '_' . $args[ 'id' ];
		$name = TriggerWarningDeluxe::slug . "[{$args[ 'id' ]}]" . ( $multivalue ? '[]' : '' );
		$values = (array) $args[ 'value' ];
		$checked = array();
		$labels = (array) $args[ 'label' ];

		foreach( $setting as $idx => $s )
			$checked[ $idx ] = !! $s;

		$this->_fieldRenderer( 'checkbox', compact( 'id', 'name', 'values', 'checked', 'labels' ) );
	}

	// Renders a radiobox. More than one value should be provided as an option group.
	function radiobuttonRenderer( $args ) {
		$setting = $this->config[ $args[ 'id' ] ];
		$multivalue = false;

		$id = TriggerWarningDeluxe::slug . '_' . $args[ 'id' ];
		$name = TriggerWarningDeluxe::slug . "[{$args[ 'id' ]}]";
		$values = (array) $args[ 'value' ];
		$checked = array();
		$labels = (array) $args[ 'label' ];

		foreach( $values as $idx => $v )
			$checked[ $idx ] = $v == $setting;

		$this->_fieldRenderer( 'radiobutton', compact( 'id', 'name', 'values', 'checked', 'labels' ) );
	}

	// Validates raw input from option submission.
	function validateFields( $fields ) {
		$validated = array();

		foreach( $fields as $field => $value ) {
			if( ! $sanction = isset( $this->registeredfields[ $field ] ) ? $this->registeredfields[ $field ] : false )
				continue;

			$valid = true;
			if( ! empty( $sanction[ 'args' ][ 'raw' ] ) )
				;//allow raw
			elseif( is_scalar( $value ) )
				$value = sanitize_text_field( $value );
			elseif( is_array( $value ) )
				$value = array_map( 'sanitize_text_field', $value );

			switch( $sanction[ 'type' ] ) {
				case 'textfield' :
				case 'textarea' :

				if( $sanction[ 'value' ] && ! preg_match( "/{$sanction[ 'value' ]}/", $value ) ) {
					add_settings_error( TriggerWarningDeluxe::slug, 'invalid-value', "'{$sanction[ 'title' ]}' <strong>Invalid input</strong>" );
					$valid = false;
					break;
				}
				break;

				case 'colorpicker' :

				if( $value && ! preg_match( '/^#([[:xdigit:]]{3}|[[:xdigit:]]{6})$/', $value ) ) {
					$valid = false;
					add_settings_error( TriggerWarningDeluxe::slug, 'invalid-value', "'{$sanction[ 'title' ]}' <strong>Not a hex value</strong>" );
				}
				break;

				case 'checkbox' :
				case 'radiobox' :

				if( is_scalar( $sanction[ 'value' ] ) && $value != $sanction[ 'value' ] ) {
					$valid = false;
					add_settings_error( TriggerWarningDeluxe::slug, 'invalid-value', "'{$sanction[ 'title' ]}' <strong>Invalid input</strong>" );
				}
				elseif( is_array( $sanction[ 'value' ] ) && array_diff( (array) $value, $sanction[ 'value' ] ) ) {
					$valid = false;
					add_settings_error( TriggerWarningDeluxe::slug, 'invalid-value', "'{$sanction[ 'title' ]}' <strong>Invalid input</strong>" );
				}
				break;

				default :
			}

			if( $valid )
				$validated[ $field ] = $value;
		}


		return apply_filters( 'twd_validate_fields', $validated, $fields );
	}

	// Adds theme option to sanctioned list. Should be called when a field is added.
	private function _addFieldFilter( $type, $id, $title, $value, $args ) {
		$this->registeredfields[ $id ] = compact( 'type', 'title', 'value', 'args' );
	}

	// Delegates UI rendering to the template fragment loader.
	private function _fieldRenderer( $type, $params ) {
		extract( $params );
		include "views/admin/optionsfield-{$type}.php";
	}
}

add_action( 'admin_init', array( TWD_WordPressAdminIntegration::instance(), 'admin_init' ) );
add_action( 'admin_menu', array( TWD_WordPressAdminIntegration::instance(), 'admin_menu' ) );
add_action( 'admin_enqueue_scripts', array( TWD_WordPressAdminIntegration::instance(), 'admin_enqueue_scripts' ) );

add_action( 'add_meta_boxes', array( TWD_WordPressAdminIntegration::instance(), 'add_meta_boxes' ) );
add_action( 'save_post', array( TWD_WordPressAdminIntegration::instance(), 'save_post' ) );

add_filter( 'manage_posts_columns', array( TWD_WordPressAdminIntegration::instance(), 'manage_posts_columns' ) );
add_filter( 'manage_posts_custom_column', array( TWD_WordPressAdminIntegration::instance(), 'manage_posts_custom_column' ) );