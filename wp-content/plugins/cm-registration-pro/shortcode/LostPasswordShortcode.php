<?php

namespace com\cminds\registration\shortcode;

use com\cminds\registration\controller\RegistrationController;

use com\cminds\registration\controller\LoginController;

use com\cminds\registration\model\Settings;

use com\cminds\registration\model\Labels;

use com\cminds\registration\controller\FrontendController;

class LostPasswordShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmreg-lost-password';
	
	
	static function shortcode($atts = array()) {
		if (!is_user_logged_in()) {
			return '<div class="cmreg-wrapper">' . LoginController::getLostPasswordView($atts) . '</div>';
		}
	}
	
	
}
