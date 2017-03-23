<?php

namespace com\cminds\registration\controller;

use com\cminds\registration\model\Settings;

use com\cminds\registration\App;
use com\cminds\registration\model\User;

class UserController extends Controller {
	
	const ACTION_EDIT = 'cmreg_profile_edit';
	
	static $ajax = array('cmreg_user_profile_edit');
	
	
	
	static function cmreg_user_profile_edit() {
		
		$response = array('success' => 0, 'msg' => 'An error occurred. Please try again.');
		
		$nonce = filter_input(INPUT_POST, 'nonce');
		if ($nonce AND wp_verify_nonce($nonce, static::ACTION_EDIT)) {
			
			$userdata = User::getUserData();
			
			$userdata->display_name = filter_input(INPUT_POST, 'display_name');
			$userdata->user_email = filter_input(INPUT_POST, 'email');
			$userdata->description = filter_input(INPUT_POST, 'description');
			
			try {
				
				User::updateUserData($userdata);
				update_user_meta($userdata->ID, 'website', filter_input(INPUT_POST, 'website'));
				static::processSaveExtraFields($userdata);
				
				$response['success'] = 1;
				$response['msg'] = 'Profile has been saved.';
				
			} catch (\Exception $e) {
				$response['msg'] = $e->getMessage();
			}
			
		}
		
		header('content-type: application/json');
		echo json_encode($response);
		exit;
		
	}
	
	
	protected static function processSaveExtraFields($userdata) {
		$extraFieldsValues = (isset($_POST['extra_field']) ? $_POST['extra_field'] : array());
		$extraFields = Settings::getOption(Settings::OPTION_REGISTER_EXTRA_FIELDS);
		if (is_array($extraFields)) {
			array_shift($extraFields);
			foreach ($extraFields as $i => &$field) {
				$name = $field['meta_name'];
				$value = (isset($extraFieldsValues[$name]) ? $extraFieldsValues[$name] : '');
				User::setExtraField($userdata->ID, $name, $value);
			}
		}
	}
		
}
