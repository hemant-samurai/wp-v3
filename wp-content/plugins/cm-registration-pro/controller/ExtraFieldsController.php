<?php

namespace com\cminds\registration\controller;

use com\cminds\registration\model\Labels;

use com\cminds\registration\model\User;

use com\cminds\registration\model\Settings;

use com\cminds\registration\App;

class ExtraFieldsController extends Controller {
	
	const NONCE_EXPORT_CSV = 'cmreg_extra_fields_export_csv';
	const ACTION_EXPORT_CSV = 'cmreg-extra-fields-csv';
	
	static $actions = array(
		'register_form' => array('args' => 1),
		'cmreg_register_form' => array('args' => 2, 'method' => 'register_form', 'priority' => 10),
		'register_post' => array('args' => 3),
		'register_new_user' => array('args' => 1),
		'edit_user_profile' => array('args' => 1, 'method' => 'show_user_profile'),
		'show_user_profile' => array('args' => 1),
	);
	static $filters = array(
		'user_row_actions' => array('args' => 2),
	);
	
	

	/**
	 * Display extra fields on the registration form.
	 *
	 * @param string $place
	 */
	static function register_form($place = null, $atts = array()) {
		if (!App::isLicenseOk()) return;
		
		// ToS
		$toc = Settings::getOption(Settings::OPTION_TERMS_OF_SERVICE_CHECKBOX_TEXT);
		if (strlen(strip_tags($toc)) > 0) {
			echo static::loadFrontendView('toc', compact('toc'));
		}
		
		// Extra fields
		$fields = Settings::getOption(Settings::OPTION_REGISTER_EXTRA_FIELDS);
		if (is_array($fields)) foreach ($fields as $i => $field) {
			if ($i == 0) continue; // Skip first template row
			if (!empty($field['role']) AND (empty($atts['role']) OR $field['role'] != $atts['role'])) continue;
			echo static::loadFrontendView('registration', compact('field', 'atts'));
		}
		
	}
	
	
	static function register_new_user($userId) {
		if (!App::isLicenseOk()) return;
		
		$fields = Settings::getOption(Settings::OPTION_REGISTER_EXTRA_FIELDS);
		if (is_array($fields)) foreach ($fields as $i => $field) {
			if ($i == 0) continue; // Skip first template row
			
			$metaName = $field['meta_name'];
			if (isset($_POST['cmreg_extra_field']) AND isset($_POST['cmreg_extra_field'][$metaName])) {
				$value = $_POST['cmreg_extra_field'][$metaName];
				if (!is_scalar($value)) $value = '';
				$maxlen = $field['maxlen'];
				if (!empty($maxlen)) {
					$value = substr($value, 0, $maxlen);
				}
				
				User::setExtraField($userId, $metaName, $value);
				
			}
			
		}
		
	}
	
	
	static function show_user_profile($user) {
		if (!App::isLicenseOk()) return;
		
		$fields = Settings::getOption(Settings::OPTION_REGISTER_EXTRA_FIELDS);
		if (is_array($fields)) foreach ($fields as $i => &$field) {
			if ($i == 0) continue;
			$field['value'] = User::getExtraField($user->ID, $field['meta_name']);
		}
		
		if (!empty($fields)) {
			echo static::loadFrontendView('user-profile', compact('fields'));
		}
		
	}
	
	
	/**
	 * Validate required fields.
	 * 
	 * @param unknown $sanitized_user_login
	 * @param unknown $user_email
	 * @param \WP_Error $errors
	 */
	static function register_post($sanitized_user_login, $user_email, \WP_Error $errors) {
		$fields = Settings::getOption(Settings::OPTION_REGISTER_EXTRA_FIELDS);
		$role = filter_input(INPUT_POST, 'role');
		if (is_array($fields)) foreach ($fields as $i => $field) {
			if ($i == 0) continue;
			if (!empty($field['role']) AND (empty($role) OR $field['role'] != $role)) continue;
			if (!empty($field['required'])) {
				if (empty($_POST['cmreg_extra_field']) OR !isset($_POST['cmreg_extra_field'][$field['meta_name']])
						OR strlen($_POST['cmreg_extra_field'][$field['meta_name']]) == 0) {
					$errors->add('empty_extra_field', Labels::getLocalized('register_empty_extra_field_error'));
				}
			}
		}
	}
	
	
	
	static function getExportCSVUrl($userId = null) {
		return add_query_arg(array(
			'action' => static::ACTION_EXPORT_CSV,
			'userId' => $userId,
			'nonce' => wp_create_nonce(static::NONCE_EXPORT_CSV),
		), admin_url('admin.php'));
	}
	
	
	
	static function user_row_actions($actions, $user) {
		$url = static::getExportCSVUrl($user->ID);
		$actions['cmreg_extra_fields_csv'] = sprintf('<a href="%s">%s</a>', esc_attr($url), 'Extra fields to CSV');
		return $actions;
	}
	
	
	static function processRequest() {
		if (is_admin()) {
			if (static::ACTION_EXPORT_CSV == filter_input(INPUT_GET, 'action') AND wp_verify_nonce(filter_input(INPUT_GET, 'nonce'), static::NONCE_EXPORT_CSV)) {
				
				$lines = array(static::getCSVHeader());
				
				$userId = filter_input(INPUT_GET, 'userId');
				if (!empty($userId)) {
					$usersIds = array($userId);
				} else {
					$usersIds = get_users(array('fields' => 'ID', 'orderby' => 'user_registered'));
				}
				
				foreach ($usersIds as $id) {
					$lines = array_merge($lines, array(static::getCSVForUser($id)));
				}
				static::downloadCSV($lines);
				
			}
		}
	}
	
	
	static function downloadCSV($lines) {
		header('content-type: text/csv');
		header('Content-Disposition: attachment; filename="extra-fields.csv"');
		$out = fopen('php://output', 'w');
		foreach ($lines as $line) {
			fputcsv($out, $line);
		}
		fclose($out);
		exit;
	}
	
	
	static function getCSVForUser($userId) {
		$user = get_userdata($userId);
		$result = array($userId, $user->user_login, $user->user_email, $user->display_name);
		$fields = array_values(User::getAllExtraFields($userId));
		$result = array_merge($result, $fields);
		return $result;
	}
	
	
	static function getCSVHeader() {
		$header = array('User ID', 'Login', 'Email', 'Display name');
		$fields = Settings::getOption(Settings::OPTION_REGISTER_EXTRA_FIELDS);
		if (is_array($fields)) foreach ($fields as $i => $field) {
			if ($i == 0) continue;
			$header[] = $field['label'];
		}
		return $header;
	}
	
	
	
}
