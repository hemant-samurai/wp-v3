<?php

/*
Plugin Name: Formstack Plugin
Plugin URI: http://wordpress.org/extend/plugins/formstack
Description: Easily embed Formstack forms into your blog or WP pages.
Version: 1.0.10
Author: Formstack, LLC
Author URI: http://www.formstack.com
*/

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


class Formstack_Plugin {

    //
    // WordPress callbacks that this class will listen to.
    //
    private static $actions = array(
         'admin_init'
        ,'admin_menu'
    );

    //
    // shortcode map to its rendering function.
    //
    private static $shortcodes = array(
         'Formstack' => 'renderFormstackShortcode'
        ,'formstack' => 'renderFormstackShortcode'
        ,'fs'        => 'renderFormstackShortcode'
    );


    public function  __construct() {

        $this->addActions(self::$actions);
        $this->addShortcodes(self::$shortcodes);
    }

    /**
     * WordPress callback when the user access the admin area.
     *
     * @link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
     * @return <type>
     */
    public function admin_init() {

        if(!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;

        register_setting('formstack_plugin', 'formstack_api_key');

        if(get_user_option('rich_editing') == 'true')
            $this->addFilters(array(
               'mce_buttons'
              ,'mce_external_plugins'

            ));
    }


    public function admin_menu() {

        add_menu_page('Formstack Forms', 'Formstack', 'manage_options', 'Formstack', array($this, 'main_page'), '../wp-content/plugins/formstack/stack.gif');
        add_submenu_page('Formstack', 'Formstack Forms', 'View Forms', 'manage_options', 'Formstack', array($this, 'main_page'));
        add_submenu_page('Formstack', 'Create Formstack Form', 'Create Form', 'manage_options', 'FormstackNewForm', array($this, 'main_page'));
        add_submenu_page('Formstack', 'Formstack API Info', 'API Key', 'manage_options', 'FormstackAPI', array($this, 'main_page'));

        add_options_page('Formstack Options', 'Formstack', 'manage_options', 'FormstackOptions', array($this, 'plugin_options'));
    }

    /**
     * Renders the Formstack shortcodes: [Formstack], [formstack], and [fs].
     * This function expects `id` and `viewkey` to be present in the shortcode
     * attributes. The shotcode is replaced with Formstack's standard JavaScript
     * Embed and a WorPress optimized stylesheet.
     *
     * @link http://codex.wordpress.org/Shortcode_API
     *
     * @param <type> $atts The shortcode attributes
     * @param <type> $content The Shortcode inner content
     * @param <type> $code The shortcode name
     * @return <type>
     */
    public function renderFormstackShortcode($atts, $content = null, $code = '') {

        $wp = wp_remote_fopen("http://www.formstack.com/forms/wp-ad.php?form={$atts['id']}");

        if(empty($atts['id']) || empty($atts['viewkey'])) return '';

        return <<< EOF
        <link href="https://www.formstack.com/forms/css/2/wordpress-post.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="https://www.formstack.com/forms/js.php?{$atts['id']}-{$atts['viewkey']}">
        </script><noscript><a href="https://www.formstack.com/forms/?{$atts['id']}-{$atts['viewkey']}" title="Online Form">Online Form</a></noscript>
        {$wp}
EOF;


    }


    /**
     * Adds Formstack Embed buttons to TinyMCE
     * @param <type> $buttons
     * @return <type>
     */
    public function mce_buttons($buttons) {

        array_push($buttons, '|', 'formstack');
        return $buttons;
    }

    /**
     * Loads the external Formstack TinyMCE plugin
     * @param <type> $plugins
     * @return <type>
     */
    public function mce_external_plugins($plugins) {

        $plugins['formstack'] = plugin_dir_url( __FILE__ ) . 'tinymce/plugin.js';
        return $plugins;
    }

    /**
     * Render the Formstack settings page
     */
    public function plugin_options() {

        if(!current_user_can('manage_options'))
            wp_die( __('You do not have sufficient permissions to access this page.') );

        include 'tmpl/options.php';
    }



    /**
     * Render the Formstack page
     */
    public function main_page() {
        include 'tmpl/main.php';
    }

    private function addActions($actions) {

        foreach($actions as $action)
            add_action($action, array($this, $action));
    }

    private function addShortcodes($shortcodes) {

        foreach($shortcodes as $tag => $func)
            add_shortcode($tag, array($this, $func));
    }

    private function addFilters($filters) {

        foreach($filters as $filter)
            add_filter($filter, array($this, $filter));
    }

}

//
// Load the Plugin
//
new Formstack_Plugin;

?>
