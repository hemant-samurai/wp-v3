<?php

namespace com\cminds\registration\controller;

use com\cminds\downloadmanager\addon\clientdownloadzone\model\Label;

use com\cminds\registration\model\User;
use com\cminds\registration\model\Labels;
use com\cminds\registration\App;

use com\cminds\registration\model\Settings;

class SocialLoginController extends Controller {

	const URL_PART_SOCIAL_LOGIN = 'cminds-registration-social-login';
	const PARAM_SOCIAL_LOGIN_ERROR = 'cmreg_social_login_error';
	
	
	static $actions = array(
		'template_redirect' => array('priority' => PHP_INT_MAX),
		'plugins_loaded',
		'cmreg_login_form_bottom' => array('args' => 1),
		'cmreg_register_form_bottom' => array('args' => 1),
		'wp_footer',
	);
	
	
	
	static function template_redirect() {
		if ($error = filter_input(INPUT_GET, static::PARAM_SOCIAL_LOGIN_ERROR)) {
			
			$label = Labels::getLocalized($error);
			if (strpos($error, 'cmreg_social_login_error_') !== 0 OR $label == $error) {
				$label = Labels::getLocalized('cmreg_social_login_error_generic');
			}
			
			static::displayErrorPage($label);
			
		}
	}
	
	
	
	static protected function displayErrorPage($content) {
		echo static::loadFrontendView('error-template', compact('content'));
		exit;
	}

	
	static function cmreg_login_form_bottom($atts) {
		if (!App::isLicenseOk()) return;
		if (!empty($atts['social-login'])) {
			echo self::getButtonsView(Labels::getLocalized('social_login_btn_prefix'));
		}
	}
	
	
	static function cmreg_register_form_bottom($atts) {
		if (!App::isLicenseOk()) return;
		if (!empty($atts['social-login'])) {
			echo self::getButtonsView(Labels::getLocalized('social_login_register_btn_prefix'));
		}
	}
	
	
	static function getButtonsView($text = '') {
		
		if (!Settings::getOption(Settings::OPTION_SOCIAL_LOGIN_ENABLE)) return;
		if (is_user_logged_in()) return;
		
		if (empty($text)) $text = Labels::getLocalized('social_login_btn_prefix');
		
		$out = '';
		if ($appId = Settings::getOption(Settings::OPTION_SOCIAL_LOGIN_FACEBOOK_APP_ID)) {
			$url = static::getFacebookAuthUrl();
			$out .= static::loadFrontendView('login-facebook', compact('appId', 'url', 'text'));
		}
		return $out;
		
	}
	
	
	static function getFacebookAuthUrl() {
		return site_url('/' . static::URL_PART_SOCIAL_LOGIN . '/facebook/');
	}
	
	
	static function getFacebookValidCallbackUrl() {
		return static::getFacebookAuthUrl() . 'int_callback';
	}
	
	
	static function plugins_loaded() {
		
		if (!App::isLicenseOk()) return;
		if (!Settings::getOption(Settings::OPTION_SOCIAL_LOGIN_ENABLE)) return;
		
		/*
		 * URLs calling order:
		 * 
		 * http://local.cminds.review/cminds-registration-social-login/facebook
		 * http://local.cminds.review/cminds-registration-social-login/facebook/int_callback
		 * http://local.cminds.review/cminds-registration-social-login/callback
		 * 
		 */
		
		if (strpos(filter_input(INPUT_SERVER, 'REQUEST_URI'), static::URL_PART_SOCIAL_LOGIN . '/callback') !== false) {
			
			// Process callback
			$Opauth = static::initOpauth($run = false);
			if ($response = static::getResponse($Opauth) AND isset($response['auth'])) {
				static::processSocialLoginData($response);
			}
			
		}
		else if (strpos(filter_input(INPUT_SERVER, 'REQUEST_URI'), static::URL_PART_SOCIAL_LOGIN) !== false) {
			
			// Initialize Opauth social login
			static::initOpauth($run = true);
			
		}
		
	}
	
	
	static protected function initOpauth($run) {
		$config = array(
			'path' => '/'. static::URL_PART_SOCIAL_LOGIN .'/',
			'callback_url' => '{path}callback',
			'security_salt' => 'nasdfajsdfjkhawer0o24i35rjkhnsfgvnskasdfjklkv',
			'Strategy' => array(
				'Facebook' => array(
					'app_id' => Settings::getOption(Settings::OPTION_SOCIAL_LOGIN_FACEBOOK_APP_ID),
					'app_secret' => Settings::getOption(Settings::OPTION_SOCIAL_LOGIN_FACEBOOK_APP_SECRET),
					'scope' => 'email',
				),
			),
		);
			
		require_once App::path('lib/Opauth/Opauth.php');
		return new \Opauth( $config, $run );
	}
	
	
	static protected function getResponse($Opauth) {
		
		/**
		 * Fetch auth response, based on transport configuration for callback
		 */
		$response = null;
		
		switch($Opauth->env['callback_transport']){
			case 'session':
				if (!session_id()) session_start();
				if (isset($_SESSION['opauth'])) {
					$response = $_SESSION['opauth'];
					unset($_SESSION['opauth']);
				}
				break;
			case 'post':
				if (isset($_POST['opauth'])) {
					$response = unserialize(base64_decode( $_POST['opauth'] ));
				}
				break;
			case 'get':
				if (isset($_GET['opauth'])) {
					$response = unserialize(base64_decode( $_GET['opauth'] ));
				}
				break;
			default:
				static::displayErrorPage('CM Registration Opauth - Unsupported callback_transport');
				break;
		}
		
		if (empty($response)) {
			static::displayErrorPage('CM Registration Opauth - Authentication error: Opauth returns empty auth response');
		}
		
		/**
		 * Check if it's an error callback
		 */
		else if (array_key_exists('error', $response)) {
			static::displayErrorPage('CM Registration Opauth - Authentication error: Opauth returns error auth response. ' . $response['error']);
		}
		
		/**
		 * Auth response validation
		 *
		 * To validate that the auth response received is unaltered, especially auth response that
		 * is sent through GET or POST.
		 */
		else{
			if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
				static::displayErrorPage('CM Registration Opauth - Invalid auth response: Missing key auth response components');
			}
			elseif (!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)){
				static::displayErrorPage('CM Registration Opauth - Invalid auth response: ' . $reason);
			}
			else{
		
				/**
				 * It's all good. Go ahead with your application-specific authentication logic
				 */
			}
		}
		
		return $response;
		
	}
	
	
	
	static function processSocialLoginData($data) {
		
		$provider = $data['auth']['provider'];
		$displayName = $data['auth']['info']['name'];
		$uid = $data['auth']['uid'];
		$email = (isset($data['auth']['info']['email']) ? $data['auth']['info']['email'] : null);
		
// 		var_dump(__METHOD__);
// 		var_dump($data);
// 		var_dump($email);exit;
		
		// Find user with the same uid
		$userId = User::getBySocialLoginUID($provider, $uid);
		if (empty($userId) AND !empty($email)) {
			$userId = User::getByEmail($email);
		}
		
		if (!empty($userId)) {
			
			static::login($userId);
			
		} else {
			
			if (!empty($email) AND Settings::getOption(Settings::OPTION_SOCIAL_LOGIN_ENABLE_ALLOW_REGISTRATION)) {
				
				$login = $email;
				$password = sha1(microtime() . $uid . $provider . mt_rand()) . 'Az.123';
				try {
					
					$userId = User::register($email, $password, $login, $displayName);
					User::setSocialLoginUID($userId, $provider, $uid);
					static::login($userId);
					
				} catch (\Exception $e) {
// 					var_dump($e);exit;
					static::displayErrorPage(Labels::getLocalized('cmreg_social_login_error_registration') .' ' . $e->getMessage());
// 					static::redirect(site_url('/?cmreg_social_login_msg=registration_error&msg=' . urlencode($e->getMessage())));
// 					exit;
				}
				
			} else {
				static::displayErrorPage(Labels::getLocalized('cmreg_social_login_error_unknown_user'));
// 				static::redirect(site_url('/?cmreg_social_login_msg=unknown_user'));
// 				exit;
			}
			
		}
		
		static::displayErrorPage(Labels::getLocalized('cmreg_social_login_error_generic') . '...');
// 		die('cmreg social login: this shouldn\'t happen');
// 		exit;
		
	}
	
	
	static protected function login($userId) {
// 		var_dump($userId);
		if ($canLogin = apply_filters('cmreg_user_can_login', true, $userId)) {
			
			$user = get_userdata($userId);
			User::loginById($userId);
			
			$redirect = LoginController::getLoginRedirectUrl($user);
			if (empty($redirect)) {
				$redirect = site_url('/');
			}
			
		} else {
			static::displayErrorPage(Labels::getLocalized('cmreg_social_login_error_account_inactive') . '...');
// 			$redirect = site_url('/?cmreg_social_login_msg=cannot_login');
		}
		
// 		var_dump(__METHOD__);
// 		var_dump($redirect);
// 		echo '<pre>';
// 		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
// 		exit;
		
		header('Location: '. $redirect);
		exit;
		
	}
	
	
	static protected function redirect($url) {
		// For some reasing wp_redirect() doesn't work in the callback method so using header:
		header('Location: '. $url);
		exit;
	}
	
	
	
	static function wp_footer() {
		
		// Remove #_=_ characters from the URL
		
		echo '<script>if (window.location.hash == "#_=_") {
			history.replaceState 
		        ? history.replaceState(null, null, window.location.href.split("#")[0])
		        : window.location.hash = "";
		}
		</script>';
	}
	
	
}
