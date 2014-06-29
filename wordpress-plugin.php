<?php

class TWD_WordPressBridge implements TWD_APIBridge {
	function install() {
		return !! add_option( TriggerWarningDeluxe::slug, array(
			'version' => TriggerWarningDeluxe::version,
			'default-warning-label' => 'Trigger Warning',
			'default-warning' => 'Be aware that the following content may contain troubling reminders of a traumatic event.'
		) );
	}

	function uninstall() {
		return !! delete_option( TriggerWarningDeluxe::slug );
	}

	function loadConfig() {
		return get_option( TriggerWarningDeluxe::slug, array() );
	}

	function fetchTriggerDataForPost( $postid ) {
		$trigger = get_post_meta( $postid, TriggerWarningDeluxe::slug, true ) or $trigger = array();
		return $trigger;
	}

	function persistTriggerDataForPost( $postid, $trigger ) {
		return !! update_post_meta( $postid, TriggerWarningDeluxe::slug, $trigger );
	}

	function removeTriggerDataFromPost( $postid ) {
		return !! delete_post_meta( $postid, TriggerWarningDeluxe::slug );
	}

	function filter( $tag, $parameters ) {
		return apply_filters_ref_array( $tag, $parameters );
	}
}

class TWD_WordPressIntegration {
	private static $instance;
	private $plugin;

	static function instance() {
		if( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

	function __construct() {
		$api = new TWD_WordPressBridge;
		$this->plugin = new TriggerWarningDeluxe( $api );
	}

	/**
	 * Called after WP loads plugins.
	 *
	 * @internal
	 */
	function plugins_loaded() {
		load_plugin_textdomain( TriggerWarningDeluxe::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Called when plugins are initialized.
	 * 
	 * @internal
	 */
	function init() {
		$this->plugin->load();

		add_shortcode( 'triggerwarning', array( $this, 'shortcodeStub' ) );
	}

	function shortcodeStub( $atts, $content ) {
		return $this->plugin->getInlineTriggerWarning( $content, $atts );
	}

	function titleStub( $title, $postid ) {
		return ! is_admin() ? $this->plugin->getTitleTriggerWarning( $postid, $title ) : $title;
	}

	function contentStub( $content ) {
		return ! is_admin() && is_single() ? $this->plugin->getPostTriggerWarning( get_the_ID(), $content ) : $content;
	}

	function excerptStub( $excerpt ) {
		return $excerpt;
	}

	function post_class( $classes ) {
		if( $this->plugin->getPostTriggerData( get_the_ID() )->has_trigger )
			$classes[] = 'has-trigger-warning';

        return $classes;
	}

	/**
	 * Called when WordPress is loading assets.
	 *
	 * @internal
	 */
	function wp_enqueue_scripts() {
		wp_enqueue_style( 'trigger-warning-deluxe-styles', plugins_url( '/assets/css/styles.css', __FILE__ ) );
		wp_enqueue_script( 'trigger-warning-deluxe-scripts', plugins_url( '/assets/js/scripts.js', __FILE__ ), array( 'jquery', 'jquery-ui-dialog' ) );
	}

	/**
	 * Called when WordPress activates this plugin.
	 *
	 * @internal
	 */
	function activate() {
		$this->plugin->install();
	}

	/**
	 * Called when WordPress deactivates this plugin.
	 *
	 * @internal
	 */
	function deactivate() {}

	/**
	 * Called when WordPress uninstalls this plugin.
	 */
	function uninstall() {
		$this->plugin->uninstall();
	}
}

add_action( 'plugins_loaded', array( TWD_WordPressIntegration::instance(), 'plugins_loaded' ) );
add_action( 'init', array( TWD_WordPressIntegration::instance(), 'init' ) );
add_action( 'wp_enqueue_scripts', array( TWD_WordPressIntegration::instance(), 'wp_enqueue_scripts' ) );

add_filter( 'the_title', array( TWD_WordPressIntegration::instance(), 'titleStub' ), 10, 2 );
add_filter( 'the_content', array( TWD_WordPressIntegration::instance(), 'contentStub' ) );
add_filter( 'the_excerpt', array( TWD_WordPressIntegration::instance(), 'excerptStub' ) );

add_filter( 'post_class', array( TWD_WordPressIntegration::instance(), 'post_class' ) );

?>