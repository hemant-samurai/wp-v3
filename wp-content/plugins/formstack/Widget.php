<?php

/*
Plugin Name: Formstack Widget
Plugin URI: http://wordpress.org/extend/plugins/formstack
Description: Easily embed Formstack forms into your sidebar.
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

require_once dirname(__FILE__) . '/API.php';

class Formstack_Widget extends WP_Widget {

    private $fields = array('formkey', 'formstack_api_key');

    function  __construct() {

        $desc = "Easily embed Formstack forms into your sidebar.";
        parent::__construct('fs_wp_widget', 'Formstack', array('description' => $desc), array('width' => 200));
    }

    function widget($args, $instance) {

        if (empty($instance['formkey'])) {
            return;
        }

        list($form, ) = explode('-', $instance['formkey']);
        $wp = wp_remote_fopen("http://www.formstack.com/forms/wp-ad.php?form={$form}");

        print <<< EOF
        <div class="fs_wp_sidebar">
        <link href="https://www.formstack.com/forms/css/2/wordpress-widget.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="https://www.formstack.com/forms/js.php?{$instance['formkey']}">
        </script><noscript><a href="https://www.formstack.com/forms/?{$instance['formkey']}" title="Online Form">Online Form</a></noscript>
        {$wp}
        </div>
EOF;

    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        foreach ($this->fields as $i => $field) {
            $instance[$field] = strip_tags($new_instance[$field]);
        }
        return $instance;
    }

    function form($instance) {

        $api_key = $instance['formstack_api_key'];

        if (empty($api_key)) {
            $api_key = get_option('formstack_api_key');
        }

        $keyFieldId = $this->get_field_id('formstack_api_key');
        $keyFieldName = $this->get_field_name('formstack_api_key');

        if (empty($api_key)) {
            include 'tmpl/widget_empty_api_key.php';
            return;
        }

        $res = Formstack_API::request($api_key, 'forms');

        if ($res->status == "error") {
            include 'tmpl/widget_empty_api_key.php';
            return;
        } elseif ($res->status != "ok") {
            include 'tmpl/api_error.php';
            return;
        }

        $res = $res->response;

        $fields = array();
        foreach ($this->fields as $i => $field) {
            $fields[$field] = array(
                 'id' => $this->get_field_id($field),
                 'name'  => $this->get_field_name($field),
                 'value' => esc_attr(@$instance[$field])
             );
        }

        print "<p>";
        print "<label for='{$fields['formkey']['id']}'>Choose a form to embed:";
        print "<select class='widefat' name='{$fields['formkey']['name']}' id='{$fields['formkey']['id']}'>";

        if ($fields['formkey']['value'] == '') {
            print "<option value=''></option>";
        }

        foreach ($res->forms as $form) {
            $sel = esc_attr($fields['formkey']['value']) == "{$form->id}-{$form->viewkey}"
                ? "selected='selected'" : '';
            print "<option {$sel} value='{$form->id}-{$form->viewkey}'>" . htmlspecialchars($form->name) . "</option>";
        }
        print "</select>";
        print "</label>";
        print "<input type='hidden' name='{$keyFieldName}' id='{$keyFieldId}' value='{$api_key}' />";
        print "</p>";

    }

}

add_action( 'widgets_init', 'formstack_widget_init' );
function formstack_widget_init() { register_widget('Formstack_Widget'); }

?>
