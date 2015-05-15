<?php

/**
 * Implements application integration & global functionality (settings)
 */
class TriggerWarningDeluxe {
	const version = '1.0.2';
	const slug = 'trigger-warning-deluxe';

	private $apiBridge;
	private $config;

	function __construct( TWD_APIBridge $api ) {
		$this->apiBridge = $api;

		$this->config = $api->loadConfig();
	}

	/**
	 * Retrieves setting from registry based on key, or all if no key is specified.
	 *
	 * @throws Exception Thrown if config is not loaded.
	 * @param mixed $key Key value for searching the registry.
	 */
	function config( $key = null ) {
		if( ! is_array( $this->config ) )
			throw new Exception( 'Plugin config not loaded' );

		return isset( $key ) ? $this->config[ $key ] : $this->config;
	}

	/**
	 * Call to load plugin
	 */
	function load() {
		$defaults = array(
			'version' => self::version,
			'default-warning-label' => 'Trigger Warning',
			'default-warning' => 'Be aware that the following content may contain troubling reminders of a traumatic event.',
			'default-inline-title' => 'Content flagged as a potential trauma-trigger'
		);

		$this->config = array_merge( $defaults, $this->apiBridge->loadConfig() );
	}

	/**
	 * Call to install this plugin.
	 */
	function install() {
		$this->apiBridge->install();
	}

	/**
	 * Call to uninstall this plugin.
	 */
	function uninstall() {
		$this->apiBridge->uninstall();
	}

	/**
	 * Wraps content in a trigger warning.
	 *
	 * @param String $content The content to be wrapped by a trigger warning
	 * @param Array $atts Additional parameters.
	 *
	 * @return String Content with trigger warning.
	 */
	function getInlineTriggerWarning( $content, $atts = array() ) {
		$title = isset( $atts[ 'title' ]  ) ? $atts[ 'title' ] : $this->config( 'default-inline-title' );
		$title = $this->apiBridge->filter( 'twd-post-inline-title-warning', array( $title, $postid ) );
		$warning = isset( $atts[ 'warning' ]  ) ? $atts[ 'warning' ] : $this->config( 'default-warning' );
		$warning = $this->apiBridge->filter( 'twd-post-warning', array( $warning, $postid ) );

		return $this->presentView( 'common/inlinewarning', array( 'content' => $content, 'title' => $title, 'warning' => $warning ) );
	}

	/**
	 * Decorates the post title with a trigger warning.
	 *
	 * @param Integer $postid The post for which the title is to be decorated.
	 * @param String $title Optional title of the post. Title will be queried if left blank.
	 * @return String Title decorated with trigger warning.
	 */
	function getTitleTriggerWarning( $postid, $title ) {
		$trigger = $this->getTriggerWarningDataForPost( $postid );

		if( ! $trigger->has_trigger )
			return $title;

		$warninglabel = $trigger->warning_label or $warninglabel = $this->config( 'default-warning-label' );
		$warninglabel = $this->apiBridge->filter( 'twd-post-warning-label', array( $warninglabel, $postid ) );

		return $this->presentView( 'common/posttitlewarning', array( 'title' => $title, 'warninglabel' => $warninglabel ) );
	}

	/**
	 * Decorates the post content with a trigger warning.
	 *
	 * @param Integer $postid The post for which the trigger warning is to be generated.
	 * @return String The trigger warning.
	 */
	function getPostTriggerWarning( $postid, $content ) {
		$trigger = $this->getTriggerWarningDataForPost( $postid );

		if( ! $trigger->has_trigger )
			return $content;

		$warninglabel = $trigger->warning_label or $warninglabel = $this->config( 'default-warning-label' );
		$warninglabel = $this->apiBridge->filter( 'twd-post-warning-label', array( $warninglabel, $postid ) );
		$warning = $trigger->warning or $warning = $this->config( 'default-warning' );
		$warning = $this->apiBridge->filter( 'twd-post-warning', array( $warning, $postid ) );

		return $this->presentView( 'common/postwarning', array( 'content' => $content, 'warninglabel' => $warninglabel, 'warning' => $warning ) );
	}

	/**
	 * Gets the trigger data for a post
	 *
	 * @param integer $postid The post for which the trigger data is to be fetched.
	 * @return TWD_TriggerMeta Trigger data.
	 */
	function getTriggerWarningDataForPost( $postid ) {
		return new TWD_TriggerMeta( $this->apiBridge->getTriggerWarningDataForPost( $postid ) );
	}

	function persistTriggerWarningDataForPost( $postid, TWD_TriggerMeta $trigger ) {
		if( $trigger->has_trigger )
			return $this->apiBridge->persistTriggerWarningDataForPost( $postid, $trigger->asMeta() );
		else
			return $this->apiBridge->removeTriggerWarningDataFromPost( $postid );
	}

	/**
	 * Renders a template with passed parameters.
	 *
	 * @param String $template The path to the template.
	 * @param Array $parameters The parameters that will fill the template.
	 * @return String The rendered template.
	 */
	private function presentView( $template, array $parameters ) {
		extract( $parameters );
		ob_start();
		include "views/{$template}.php";
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}

/**
 * API abstraction intended for plugin to utilize underlying framework.
 */
interface TWD_APIBridge {
	function install();
	function uninstall();
	function loadConfig();

	function getTriggerWarningDataForPost( $postid );
	function persistTriggerWarningDataForPost( $postid, $trigger );
	function removeTriggerWarningDataFromPost( $postid );

	function filter( $filter, $parameters );
}

/**
 * Utility class for formalizing trigger data and providing an array adapter.
 */
class TWD_TriggerMeta {
	private $meta;

	function __construct( array $meta ) {
		static $defaults = array(
			'has_trigger' => false,
			'warning_label' => null,
			'warning' => null
		);

		$this->meta = array_intersect_key(
			array_merge( $defaults, $meta ),
			$defaults
		);
	}

	/**
	 * Returns trigger data as an array
	 *
	 * @return array Trigger data
	 */
	function asMeta() {
		return $this->meta;
	}

	/**
	 * Magic accessor method for dynamically accessing data.
	 *
	 * @param mixed $key Object attribute
	 * @return mixed Object attribute value
	 */
	function __get( $key ) {
		return $this->meta[ $key ];
	}
}