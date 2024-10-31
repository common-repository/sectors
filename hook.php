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

/**
 * Manage hooks for select sectors
 */
class Sector_Hook {

	/**
	 * Registered sector hooks
	 * @var array
	 */
	protected static $sectors;

	/**
	 * Hook list
	 * @var array
	 */
	protected $hooks;

	/**
	 * Sector name
	 * @var string
	 */
	protected $sector;

	/**
	 * Is sector hook the current sector
	 * @var boolean
	 */
	protected $is_current;

	public function __construct($sector) {
		$this->sector = $sector;
		$this->hooks = array();
		$this->is_current = false;
	}

	/**
	 * Get a sector hook by name
	 *
	 * @since  1.0
	 * @param  string  $sector
	 * @return Sector_Hook
	 */
	public static function on($sector) {
		if(!isset(self::$sectors[$sector])) {
			self::$sectors[$sector] = new self($sector);
		}
		return self::$sectors[$sector];
	}

	/**
	 * Set sector hook as current sector
	 *
	 * @since 1.0
	 */
	public function set_current() {
		$this->is_current = true;
		$this->dispatch();
	}

	/**
	 * Run all sector hooks in list
	 *
	 * @since  1.0
	 * @return void
	 */
	public function dispatch() {
		foreach ($this->hooks as $hook) {
			add_action($hook[0],$hook[1],$hook[2],$hook[3]);
		}
		$this->hooks = array();
	}

	/**
	 * Add sector hook
	 *
	 * @since 1.0
	 * @param string  $hook
	 * @param string  $callback
	 * @param int     $priority
	 * @param int     $args
	 */
	public function add($hook,$callback,$priority,$args) {
		if($this->is_current) {
			add_action($hook,$callback,$priority,$args);
		} else {
			$this->hooks[] = array($hook,$callback,$priority,$args);
		}
		
	}

	/**
	 * Remove sector hook
	 *
	 * @since 1.0
	 * @param string  $hook
	 * @param string  $callback
	 * @param int     $priority
	 * @param int     $args
	 */
	public function remove($hook,$callback,$priority,$args) {
		if($this->is_current) {
			return remove_action($hook,$callback,$priority,$args);
		} else {
			$find = array($hook,$callback,$priority,$args);
			$found = array_search($find, $this->hooks);
			if($found !== false) {
				unset($this->hooks[$found]);
			}
			return $found;
		}
	}

}

//eol