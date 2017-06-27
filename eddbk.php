<?php

/*
 * @wordpress-plugin
 *
 * Plugin Name: Easy Digital Downloads - Bookings
 * Plugin URI: https://easydigitaldownloads.com/downloads/edd-bookings/
 * Description: Adds a customizable booking system to Easy Digital Downloads.
 * Version: 3.0.0
 * Author: RebelCode
 * Text Domain: eddbk
 * Domain Path: /languages/
 * License: GPLv3
 */

/*
 * Copyright (C) 2015-2016 RebelCode Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('EDDBK_SLUG', 'eddbk');

// Paths
define('EDDBK_DIR', __DIR__);
define('EDDBK_SRC_DIR', EDDBK_DIR . DIRECTORY_SEPARATOR . 'src');
define('EDDBK_VENDOR_DIR', EDDBK_DIR . DIRECTORY_SEPARATOR . 'vendor');
// I18n
define('EDDBK_TEXT_DOMAIN', 'eddbk');

// Module System
define('EDDBK_MODULE_ROOT_DIR', EDDBK_VENDOR_DIR);
define('EDDBK_MODULE_FILE_FINDER_MAX_DEPTH', 2);

// Load Composer Autoloader
require EDDBK_VENDOR_DIR . DIRECTORY_SEPARATOR . 'autoload.php';
// Load Functions
require EDDBK_SRC_DIR . DIRECTORY_SEPARATOR . 'functions.php';

// Run the plugin
eddbk()->load();
