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
 * Is sector the current one
 *
 * @since  1.0
 * @param  string  $sector_name
 * @return boolean
 */
function is_sector($sector_name = '') {
	return $sector_name ? isset(SCT_App::instance()->sectors[$sector_name]) : count(SCT_App::instance()->sectors);
}

/**
 * Get all sectors for current context
 *
 * @since  1.1
 * @return array
 */
function get_current_sectors() {
	return SCT_App::instance()->sectors;
}

/**
 * Add an action to a select sector
 *
 * @since 1.0
 * @param string  $sector
 * @param string  $hook
 * @param string  $callback
 * @param int     $priority
 * @param int     $args
 */
function add_sector_action($sector,$hook,$callback,$priority = 10,$args = 1) {
	Sector_Hook::on($sector)->add($hook,$callback,$priority,$args);
}

/**
 * Add a filter to a select sector
 *
 * @since 1.0
 * @param string  $sector
 * @param string  $hook
 * @param string  $callback
 * @param int     $priority
 * @param int     $args
 */
function add_sector_filter($sector,$hook,$callback,$priority = 10,$args = 1) {
	Sector_Hook::on($sector)->add($hook,$callback,$priority,$args);
}

/**
 * Remove action from sector
 *
 * @since 1.0
 * @param string  $sector
 * @param string  $hook
 * @param string  $callback
 * @param int     $priority
 * @param int     $args
 */
function remove_sector_action($sector,$hook,$callback,$priority = 10,$args = 1) {
	Sector_Hook::on($sector)->remove($hook,$callback,$priority,$args);
}

/**
 * Remove filter from sector
 *
 * @since 1.0
 * @param string  $sector
 * @param string  $hook
 * @param string  $callback
 * @param int     $priority
 * @param int     $args
 */
function remove_sector_filter($sector,$hook,$callback,$priority = 10,$args = 1) {
	Sector_Hook::on($sector)->remove($hook,$callback,$priority,$args);
}

//eol