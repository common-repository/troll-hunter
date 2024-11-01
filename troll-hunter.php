<?php
/*
   Plugin Name: Troll Hunter by Gavagai
   Plugin URI: http://wordpress.org/extend/plugins/troll-hunter-by-gavagai/
   Version: 0.1
   Author: Gavagai AB
   Description: A plugin to automatically moderate messages and block those that contain hateful, degrading, or slanderous language. Can be configured for six languages: Swedish. English, Danish, Finnish, Russian, and German. To activate the plugin, please go to its settings page and enter an API key for the Gavagai API.
   Text Domain: troll-hunter
   License: GPLv3
   Author URI: http://www.gavagai.se
  */

/*
    "Troll Hunter By Gavagai" Copyright (C) 2014 Gavagai AB  (email : info@gavagai.se)

    This following part of this file is part of Troll Hunter By Gavagai for WordPress.

    Troll Hunter By Gavagai is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Troll Hunter By Gavagai is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

$TrollHunter_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function TrollHunter_noticePhpVersionWrong() {
    global $TrollHunter_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Troll Hunter" requires a newer version of PHP to be running.',  'troll-hunter').
            '<br/>' . __('Minimal version of PHP required: ', 'troll-hunter') . '<strong>' . $TrollHunter_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'troll-hunter') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function TrollHunter_PhpVersionCheck() {
    global $TrollHunter_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $TrollHunter_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'TrollHunter_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 *
 * @return void
 */
function TrollHunter_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('troll-hunter', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
TrollHunter_i18n_init();


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'set_settings_link' );

function set_settings_link( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'plugins.php?page=TrollHunter_PluginSettings') .'">' . __("Settings") . '</a>';
   return $links;
}

if (TrollHunter_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('troll-hunter_init.php');
    TrollHunter_init(__FILE__);
}
