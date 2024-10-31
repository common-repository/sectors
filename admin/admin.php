<?php
/**
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */

if (!defined('ABSPATH')) {
	exit;
}

abstract class SCT_Admin {

	/**
	 * Screen identifier
	 * @var string
	 */
	protected $_screen;

	public function __construct() {
		if(is_admin()) {
			add_action('admin_menu',
				array($this,'add_menu'),99);
			$this->admin_hooks();
		} else {
			$this->frontend_hooks();
		}
	}

	/**
	 * Set up screen and menu if necessary
	 *
	 * @since 1.0
	 */
	public function add_menu() {
		$this->_screen = $this->get_screen();
		add_action('load-'.$this->_screen,
			array($this,'load_screen'));
	}

	/**
	 * Add filters and actions for admin dashboard
	 * e.g. AJAX calls
	 *
	 * @since  1.0
	 * @return void
	 */
	abstract public function admin_hooks();

	/**
	 * Add filters and actions for frontend
	 *
	 * @since  1.0
	 * @return void
	 */
	abstract public function frontend_hooks();

	/**
	 * Get current screen
	 *
	 * @since  1.0
	 * @return string
	 */
	abstract public function get_screen();

	/**
	 * Prepare screen load
	 *
	 * @since  1.0
	 * @return void
	 */
	abstract public function prepare_screen();

	/**
	 * Authorize user for screen
	 *
	 * @since  1.0
	 * @return boolean
	 */
	abstract public function authorize_user();

	/**
	 * Register and enqueue scripts styles
	 * for screen
	 *
	 * @since 1.0
	 */
	abstract public function add_scripts_styles();

	/**
	 * Load screen
	 *
	 * @since  1.0
	 * @return void
	 */
	public function load_screen() {
		if(!$this->authorize_user()) {
			wp_die(
				'<p>' . __( 'You do not have access to this screen.', 'sectors' ) . '</p>',
				403
			);
		}
		$this->prepare_screen();
		add_action('admin_enqueue_scripts',
			array($this,'add_scripts_styles'),11);
	}

}

//