<?php

/**
 * Implements application integration & global functionality (settings)
 */
class TriggerWarningDeluxe {
	const version = '0.9.9';
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

		if( $key !== null )
			return @$this->config[ $key ];
		else
			return $this->config;
	}

	/**
	 * Call to load plugin
	 */
	function load() {
		$this->config = $this->apiBridge->loadConfig();
	}

	/**
	 * Call to install this plugin.
	 *
	 * @internal
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
		$title = isset( $atts[ 'title' ]  ) ? $atts[ 'title' ] : 'Content flagged as a potential trauma-trigger';
		$content = $this->apiBridge->filter( 'twd-inline-warning', array( $content, $atts ) );
		$warning = $this->config( 'default-warning' );

		return $this->renderTemplate( 'views/common/inlinewarning', array( 'title' => $title, 'content' => $content, 'warning' => $warning ) );
	}

	/**
	 * Decorates the post title with a trigger warning.
	 *
	 * @param Integer $postid The post for which the title is to be decorated.
	 * @param String $title Optional title of the post. Title will be queried if left blank.
	 * @return String Title decorated with trigger warning.
	 */
	function getTitleTriggerWarning( $postid, $title ) {
		$trigger = $this->getPostTriggerData( $postid );

		if( $trigger->has_trigger ) {
			$warninglabel = $trigger->warning_label or $warninglabel = $this->config( 'default-warning-label' );
			$warninglabel = $this->apiBridge->filter( 'twd-post-title-warning', array( $warninglabel, $postid ) );

			return $this->renderTemplate( 'views/common/posttitlewarning', array( 'title' => $title, 'warninglabel' => $warninglabel ) );
		}
		else {
			return $title;
		}
	}

	/**
	 * Decorates the post content with a trigger warning.
	 *
	 * @param Integer $postid The post for which the trigger warning is to be generated.
	 * @return String The trigger warning.
	 */
	function getPostTriggerWarning( $postid, $content ) {
		$trigger = $this->getPostTriggerData( $postid );

		if( $trigger->has_trigger) {
			$warninglabel = $trigger->warning_label or $warninglabel = $this->config( 'default-warning-label' );
			$warninglabel = $this->apiBridge->filter( 'twd-post-title-warning', array( $warninglabel, $postid ) );
			$warning = $trigger->warning or $warning = $this->config( 'default-warning' );
			$warning = $this->apiBridge->filter( 'twd-post-warning', array( $warning, $postid ) );

			return $this->renderTemplate( 'views/common/postwarning', array( 'content' => $content, 'warninglabel' => $warninglabel, 'warning' => $warning ) );
		}
		else {
			return $content;
		}
	}

	/**
	 * Gets the trigger data for a post
	 *
	 * @param integer $postid The post for which the trigger data is to be fetched.
	 * @return TWD_TriggerMeta Trigger data.
	 */
	function getPostTriggerData( $postid ) {
		return new TWD_TriggerMeta( $this->apiBridge->fetchTriggerDataForPost( $postid ) );
	}

	function savePostTriggerData( $postid, TWD_TriggerMeta $trigger ) {
		if( $trigger->has_trigger )
			return $this->apiBridge->persistTriggerDataForPost( $postid, $trigger->asMeta() );
		else 
			return $this->apiBridge->removeTriggerDataFromPost( $postid );
	}

	/**
	 * Renders a template with passed parameters.
	 *
	 * @param String $template The path to the template.
	 * @param Array $parameters The parameters that will fill the template.
	 * @return String The rendered template.
	 */
	private function renderTemplate( $template, array $parameters ) {
		extract( $parameters );
		ob_start();
		include "{$template}.php";
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

	function fetchTriggerDataForPost( $postid );
	function persistTriggerDataForPost( $postid, $trigger );
	function removeTriggerDataFromPost( $postid );

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

?>