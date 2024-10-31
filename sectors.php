<?php
/**
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */
/*
Plugin Name: Sectors - Conditional Templates & Hooks
Plugin URI: https://dev.institute/wordpress-sectors/
Description: Add templates, actions, and filters depending on the context.
Version: 1.2
Author: Joachim Jensen
Author URI: https://dev.institute
License: GPLv3

    Sectors for WordPress
    Copyright (C) 2018 Joachim Jensen - jv@intox.dk

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('ABSPATH')) {
    exit;
}

$sct_plugin_dir = plugin_dir_path(__FILE__);
require($sct_plugin_dir.'lib/wp-content-aware-engine/bootstrap.php');
require($sct_plugin_dir.'app.php');
require($sct_plugin_dir.'hook.php');
require($sct_plugin_dir.'functions.php');

if (is_admin()) {
    require($sct_plugin_dir.'lib/wp-db-updater/wp-db-updater.php');
    require($sct_plugin_dir.'admin/db-updates.php');
    require($sct_plugin_dir.'admin/admin.php');
    require($sct_plugin_dir.'admin/sector-list-table.php');
    require($sct_plugin_dir.'admin/sector-overview.php');
    require($sct_plugin_dir.'admin/sector-edit.php');
}

// Launch plugin
SCT_App::instance();

//eol
