<?php

namespace com\cminds\registration\controller;

use com\cminds\registration\model\InvitationCode;

use com\cminds\registration\model\Settings;

use com\cminds\registration\lib\Email;

use com\cminds\registration\model\Labels;

use com\cminds\registration\model\User;

use com\cminds\registration\App;

class EmailVerificationController extends Controller {
	
	const PARAM_VERIFICATION_CODE = 'cmreg_verification_code';
	const CRON_ACTION = 'cmreg_delete_inactive_users';
	
	static $actions = array(
		'init',
		'register_new_user' => array('args' => 1, 'priority' => 100),
		self::CRON_ACTION,
	);
	static $filters = array(
		'authenticate' => array('args' => 3, 'priority' => 100),
		'cmreg_options_config' => array('priority' => 50),
		'cmreg_user_can_login' => array('args' => 2),
		'cmreg_registration_ajax_response' => array('args' => 2, 'priority' => 500),
	);
	
	
	static function init() {

		// CRON job to delete inactive users
		if (wp_get_schedule(self::CRON_ACTION) === false) {
			wp_clear_scheduled_hook(self::CRON_ACTION);
			wp_schedule_event(time(), 'daily', self::CRON_ACTION);
		}
		
		if (App::isLicenseOk() AND !empty($_GET[self::PARAM_VERIFICATION_CODE])) {
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueAccountVerificationScript'));
			if (User::verifyEmail($_GET[self::PARAM_VERIFICATION_CODE])) {
				$msg = Labels::getLocalized('register_account_verification_success');
			} else {
				$msg = Labels::getLocalized('register_account_verification_error');
			}
			wp_localize_script('cmreg_account_verification', 'cmreg_account_verification', compact('msg'));
		}
		
	}
	
	
	static function enqueueAccountVerificationScript() {
		wp_enqueue_script('cmreg_account_verification');
	}
	
	static function getVerificationUrl($code) {
		return add_query_arg(self::PARAM_VERIFICATION_CODE, $code, RegistrationController::getWelcomeUrl());
	}
	
	

	static function sendEmailVerification($userId, $verificationCode) {
		$user = get_userdata($userId);
		if (empty($user)) return false;
		Email::send(
			$receiver = $user->user_email,
			$subject = Settings::getOption(Settings::OPTION_REGISTER_ACTIVATION_EMAIL_SUBJECT),
			$body = Settings::getOption(Settings::OPTION_REGISTER_ACTIVATION_EMAIL_BODY),
			$vars = self::getEmailVerificationVars($userId, $verificationCode)
		);
	}
	
	
	static function getEmailVerificationVars($userId, $verificationCode) {
		$vars = Email::getBlogVars() + Email::getUserVars($userId);
		$vars['[linkurl]'] = static::getVerificationUrl($verificationCode);
		return $vars;
	}
	
	
	/**
	 * After successful registration
	 *
	 * @param unknown $userId
	 */
	static function register_new_user($userId) {
		if (!App::isLicenseOk()) return;
		
		if ($code = InvitationCode::getByUser($userId)) {
			$verify = $code->getEmailVerificationStatusOrGlobal();
		} else {
			$verify = Settings::getOption(Settings::OPTION_REGISTER_EMAIL_VERIFICATION_ENABLE);
		}
		
		if ($verify) {
			$verificationCode = User::generateEmailVerificationCode($userId);
			self::sendEmailVerification($userId, $verificationCode);
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
		
		if ($userData = get_user_by('login', $username) AND !static::userCanLogin($userData->ID)) {
			$addError('email_not_verified', Labels::getLocalized('login_error_email_not_verified'));
		}
		
		return $user;
		
	}
	
	
	static function cmreg_user_can_login($result, $userId) {
		if ($result) {
			$result = static::userCanLogin($userId);
		}
		return $result;
	}
	
	
	static function userCanLogin($userId) {
		return (User::getEmailVerificationStatus($userId) != User::EMAIL_VERIFICATION_STATUS_PENDING);
	}
	
	
	
	static function cmreg_delete_inactive_users() {
		User::deleteInactiveUsers();
	}
	
	
	static function cmreg_options_config($config) {
		return array_merge($config, array(
			Settings::OPTION_REGISTER_EMAIL_VERIFICATION_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'register',
				'subcategory' => 'verification',
				'title' => 'Require email verification',
				'desc' => 'If enabled, user have to confirm his email address by clicking the activation link which will be send to his email. '
					. 'Until this his account won\'t be active and user will be unable to login.',
			),
			Settings::OPTION_REGISTER_DAYS_FOR_VERIFICATION => array(
				'type' => Settings::TYPE_INT,
				'category' => 'register',
				'subcategory' => 'verification',
				'default' => 30,
				'title' => 'Days for verification',
				'desc' => 'Give new user x days to verify his account. After that time the registered account will be deleted.',
			),
			Settings::OPTION_REGISTER_WELCOME_PAGE => array(
				'type' => Settings::TYPE_SELECT,
				'default' => 0,
				'options' => Settings::getPagesOptions(),
				'category' => 'register',
				'subcategory' => 'verification',
				'title' => 'Page loaded after successful email verification',
			),
			Settings::OPTION_REGISTER_ACTIVATION_EMAIL_SUBJECT => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'email',
				'subcategory' => 'activation',
				'default' => 'Confirm your registration on [blogname]',
				'title' => 'Activation email subject',
				'desc' => 'You can use the following shortcodes:' . str_replace(' ', '<br />', ' [blogname] [siteurl] [userdisplayname] [userlogin] [useremail] [linkurl]'),
			),
			Settings::OPTION_REGISTER_ACTIVATION_EMAIL_BODY => array(
				'type' => Settings::TYPE_TEXTAREA,
				'category' => 'email',
				'subcategory' => 'activation',
				'default' => 'Hi'. PHP_EOL .'In order to confirm your registration on [blogname] ([siteurl]) please visit the URL below:'
				. PHP_EOL . '[linkurl]',
				'title' => 'Activation email body template',
				'desc' => 'You can use the following shortcodes:' . str_replace(' ', '<br />', ' [blogname] [siteurl] [userdisplayname] [userlogin] [useremail] [linkurl]'),
			),
		));
	}
	
	
	static function cmreg_registration_ajax_response($response, $userId) {
		if ($userId AND !empty($response['success'])) {
			$status = User::getEmailVerificationStatus($userId);
			if (User::EMAIL_VERIFICATION_STATUS_PENDING == $status) {
				$response['msg'] = Labels::getLocalized('register_verification_msg');
			}
		}
		return $response;
	}
	
}
