<?php

/**
 * This file is part of Formstack's WordPress Plugin.
 *
 * Formstack's WordPress Plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * Formstack's WordPress Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

$path  = '';

if (!defined('WP_LOAD_PATH') ) {

    $classic_root = dirname(dirname(dirname(dirname(__FILE__)))) . '/' ;
    if(file_exists($classic_root . 'wp-load.php'))
        define( 'WP_LOAD_PATH', $classic_root);

    else if (file_exists($path . 'wp-load.php'))
        define( 'WP_LOAD_PATH', $path);

    else
        exit("Could not find wp-load.php");
}

require_once dirname(__FILE__) . '/API.php';
require_once WP_LOAD_PATH . 'wp-load.php';

?>