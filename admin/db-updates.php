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

$sct_db_updater = new WP_DB_Updater('sct_db_version',SCT_App::PLUGIN_VERSION, true);

//eol