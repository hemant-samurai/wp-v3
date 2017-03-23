<?php

namespace com\cminds\registration\shortcode;

use com\cminds\registration\model\Settings;

use com\cminds\registration\model\Labels;

use com\cminds\registration\controller\SocialLoginController;

class SocialLoginShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmreg-social-login';
	
	
	static function shortcode($atts, $text = '') {
		
		$atts = shortcode_atts(array(
			'text' => '',
		), $atts);
		
// 		if (!is_user_logged_in()) {
			echo SocialLoginController::getButtonsView($atts['text']);
// 		}
	}
	
	
}
