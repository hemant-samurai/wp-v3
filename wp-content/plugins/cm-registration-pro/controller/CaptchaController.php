<?php

namespace com\cminds\registration\controller;

use com\cminds\registration\model\Labels;

use com\cminds\registration\App;

use com\cminds\registration\model\Settings;

use com\cminds\registration\helper\Recaptcha;

class CaptchaController extends Controller {
	
	static $actions = array(
		'login_form' => array('args' => 1),
		'register_form' => array('args' => 1),
		'cmreg_register_form' => array('args' => 1, 'method' => 'register_form', 'priority' => 10),
		'register_post' => array('args' => 3),
	);
	static $filters = array(
		'authenticate' => array('args' => 3, 'priority' => 100),
		'cmreg_options_config' => array('priority' => 50),
	);
	
	
	static function login_form($place = null) {
		if (App::isLicenseOk() AND Recaptcha::isConfigured() AND Settings::getOption(Settings::OPTION_LOGIN_RECAPTCHA_ENABLE)) {
			if ($place == 'cmreg_overlay') {
				echo '<div class="cmreg-recaptcha cmreg-recaptcha-login" id="cmreg-recaptcha-'. rand() .'" data-sitekey="'
						. esc_attr(Recaptcha::getSiteKey()) .'"></div>';
			} else {
				wp_enqueue_script('cmreg-recaptcha');
				echo '<div style="margin: 1em 0;" class="g-recaptcha" data-sitekey="'. esc_attr(Recaptcha::getSiteKey()) .'"></div>';
			}
		}
	}
	
	
	static function authenticate($user, $username, $password) {
	
		if (!App::isLicenseOk()) return $user;
		
		$addError = function($errorCode, $msg) use (&$user) {
			if (is_wp_error($user)) {
				$user->add($errorCode, $msg);
			} else {
				$user = new \WP_Error($errorCode, $msg);
			}
		};
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST'
				AND Recaptcha::isConfigured() AND Settings::getOption(Settings::OPTION_LOGIN_RECAPTCHA_ENABLE)
				AND !RegistrationController::isRegistrationAction() // disable for signing-in after registration
				AND !Recaptcha::verify()) {
			$addError('invalid_captcha', Labels::getLocalized('login_error_invalid_captcha'));
		}
	
		return $user;
	}
	
	

	/**
	 * Display extra field on the registration form.
	 *
	 * @param string $place
	 */
	static function register_form($place = null) {
		if (App::isLicenseOk() AND Recaptcha::isConfigured() AND Settings::getOption(Settings::OPTION_REGISTER_RECAPTCHA_ENABLE)) {
			if ($place == 'cmreg_overlay') {
				echo '<div class="cmreg-recaptcha cmreg-recaptcha-registration" id="cmreg-recaptcha-'. rand() .'" data-sitekey="'. esc_attr(Recaptcha::getSiteKey()) .'"></div>';
			} else {
				wp_enqueue_script('cmreg-recaptcha');
				echo '<div style="margin: 1em 0;" class="g-recaptcha" data-sitekey="'. esc_attr(Recaptcha::getSiteKey()) .'"></div>';
			}
		}
	}
	
	


	/**
	 * Validate the registration
	 *
	 * @param string $sanitized_user_login
	 * @param string $user_email
	 * @param \WP_Error $errors
	 */
	static function register_post($sanitized_user_login, $user_email, \WP_Error $errors) {
// 		var_dump(__METHOD__);
		if (App::isLicenseOk() AND Recaptcha::isConfigured() AND Settings::getOption(Settings::OPTION_REGISTER_RECAPTCHA_ENABLE) AND !Recaptcha::verify()) {
			// Validate captcha
			$errors->add('invalid_captcha', Labels::getLocalized('register_invalid_captcha_error'));
		}
	}
	
	
	static function cmreg_options_config($config) {
		return array_merge($config, array(
			Settings::OPTION_RECAPTCHA_API_SITE_KEY => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'api',
				'title' => 'Google reCAPTCHA API site key',
				'desc' => '<a href="https://www.google.com/recaptcha/admin#list" target="_blank" class="button">Register new reCAPTCHA key</a>'
			),
			Settings::OPTION_RECAPTCHA_API_SECRET_KEY => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'api',
				'title' => 'Google reCAPTCHA API secret key',
			),
			Settings::OPTION_LOGIN_RECAPTCHA_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'login',
				'subcategory' => 'login',
				'title' => 'Enable reCAPTCHA on the login form',
			),
			Settings::OPTION_REGISTER_RECAPTCHA_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'register',
				'subcategory' => 'register',
				'title' => 'Enable reCAPTCHA on the registration form',
			),
		));
	}
	
	
	
}