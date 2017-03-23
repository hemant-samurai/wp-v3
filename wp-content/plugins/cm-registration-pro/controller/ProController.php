<?php

namespace com\cminds\registration\controller;

use com\cminds\registration\model\User;

use com\cminds\registration\model\S2MembersLevels;

use com\cminds\registration\App;
use com\cminds\registration\model\Settings;
use com\cminds\registration\model\Labels;

class ProController extends Controller {

	
	static $filters = array(
		'cmreg_options_config' => array('priority' => 50),
		'cmreg_settings_pages' => array('priority' => 2000),
		'cmreg_email_headers',
	);
	protected static $actions = array(
		'plugins_loaded',
		array('name' => 'plugins_loaded', 'method' => 'session_keeper'),
		'wp_enqueue_scripts' => array('method' => 'enqueueLogoutScript'),
		'cmreg_labels_init',
	);
	
	
	
	static function plugins_loaded() {
		if (App::TESTING) {
			if (!defined(S2MembersLevels::MEMBERSHIP_LEVELS)) {
				define(S2MembersLevels::MEMBERSHIP_LEVELS, 4);
			}
			for ($n=1; $n<=constant(S2MembersLevels::MEMBERSHIP_LEVELS); $n++) {
				$const = sprintf(S2MembersLevels::MEMBER_LEVEL_LABEL, $n);
				if (!defined($const)) {
					define($const, 'Membership Level #'. $n);
				}
			}
		}
	}
	
	
	static function cmreg_labels_init() {
		Labels::loadLabelFile(App::path('asset/labels/pro.tsv'));
	}
	
	
	static function session_keeper() {
		if (is_user_logged_in() AND $timeout = (int)Settings::getOption(Settings::OPTION_LOGOUT_INACTIVITY_TIME) AND $timeout > 0) {
			if (!session_id()) session_start();
			$lastActivity = User::getLastActivity();
// 			var_dump($timeout);var_dump($lastActivity);var_dump(time() - $lastActivity);
			if ($lastActivity AND (time() - $lastActivity) > $timeout*60) {
				User::logout();
			}
			if (!defined('DOING_AJAX') OR !DOING_AJAX) {
				User::updateLastActivity();
			}
		}
		
	}
	
	
	static function enqueueLogoutScript() {
		if (is_user_logged_in() AND Settings::getOption(Settings::OPTION_RELOAD_AFTER_LOGOUT)) {
			wp_enqueue_script('cmreg-logout');
		}
	}
	
	
	
	static function cmreg_options_config($config) {
		return array_merge($config, array(
			Settings::OPTION_REGISTER_DEFAULT_ROLE => array(
				'type' => Settings::TYPE_SELECT,
				'default' => 'subscriber',
				'options' => Settings::getRolesOptions(),
				'category' => 'register',
				'subcategory' => 'register',
				'title' => 'Default role',
				'desc' => 'User\'s role granted after the registration.',
			),
			Settings::OPTION_S2MEMBERS_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'register',
				'subcategory' => 's2member',
				'title' => 'Enable S2Members integration',
				'desc' => 'If enabled, the invitations code can be related with the S2Members Pro membership level and new users '
				. 'will be assigned to the chosen level.',
			),
			Settings::OPTION_REGISTER_S2MEMBER_DEFAULT_LEVEL => array(
				'type' => Settings::TYPE_SELECT,
				'options' => array(0 => '-- none --') + S2MembersLevels::getAll(),
				'default' => 0,
				'category' => 'register',
				'subcategory' => 's2member',
				'title' => 'S2Member Pro default level',
				'desc' => 'Assign user which is not using the invitation code to the chosen S2Members Pro membership level.',
			),
			Settings::OPTION_REGISTER_WELCOME_EMAIL_SUBJECT => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'email',
				'subcategory' => 'welcome',
				'default' => 'Welcome to [blogname]',
				'title' => 'Welcome email subject',
				'desc' => 'You can use the following shortcodes:' . str_replace(' ', '<br />',
					' [blogname] [siteurl] [userdisplayname] [userlogin] [useremail] [linkurl]'),
			),
			Settings::OPTION_REGISTER_WELCOME_EMAIL_BODY => array(
				'type' => Settings::TYPE_TEXTAREA,
				'category' => 'email',
				'subcategory' => 'welcome',
				'default' => 'Hi'. PHP_EOL .'You have been registered on the [blogname] ([siteurl]) and your account is already active.'
					. PHP_EOL .'Please visit the following website to read the additional information:'. PHP_EOL .'[linkurl]',
				'title' => 'Welcome email body template',
				'desc' => 'You can use the following shortcodes:' . str_replace(' ', '<br />',
					' [blogname] [siteurl] [userdisplayname] [userlogin] [useremail] [linkurl]'),
			),
			Settings::OPTION_OVERLAY_OPACITY => array(
				'type' => Settings::TYPE_PERCENT,
				'category' => 'general',
				'subcategory' => 'appearance',
				'default' => 80,
				'title' => 'Overlay background opacity',
				'desc' => 'Enter the opacity of the login dialog box overlay background.',
			),
			Settings::OPTION_LOGOUT_INACTIVITY_TIME => array(
				'type' => Settings::TYPE_INT,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'logout',
				'title' => 'Logout after inactivity time [min]',
				'desc' => 'User will be logged-out after this time of inactivity. Set 0 to disable.',
			),
			Settings::OPTION_RELOAD_AFTER_LOGOUT => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'logout',
				'title' => 'Reload browser after logout',
				'desc' => 'If enabled, the script will be checking in background if user is still logged-in and reload the browser if not.',
			),
			Settings::OPTION_TERMS_OF_SERVICE_CHECKBOX_TEXT => array(
				'type' => Settings::TYPE_RICH_TEXT,
				'category' => 'register',
				'subcategory' => 'fields',
				'title' => 'Terms of service acceptance text',
				'desc' => 'Enter text which will be displayed next to the checkbox that user has to check to accept the terms of service. '
							. 'Leave empty if you don\'t want to display the checkbox.',
			),
			Settings::OPTION_REGISTER_EXTRA_FIELDS => array(
				'type' => Settings::TYPE_EXTRA_FIELDS,
				'category' => 'register',
				'subcategory' => 'fields',
				'title' => 'Extra user-meta fields',
				'desc' => 'Add extra user-meta fields to the registration form.<br /><br />'
					. 'To download all users\' extra fields in the CSV format use the following button:<br />'
					. '<a href="'. esc_attr(ExtraFieldsController::getExportCSVUrl()). '" class="button">Download users CSV</a>',
			),
			Settings::OPTION_EMAIL_USE_HTML => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'email',
				'subcategory' => 'general',
				'title' => 'Use HTML content-type emails',
				'desc' => 'If enabled, the entire email content will be treated as HTML (eg. new lines won\'t work and you need to use <br> tags).',
			),
			
			// Social Login
			Settings::OPTION_SOCIAL_LOGIN_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'login',
				'subcategory' => 'social-login',
				'title' => 'Enable social login',
				'desc' => 'General option to enable the social login features. User will be able to login using his social service account and will be logged-in '
						. 'to a WP account with the same email address.',
			),
			Settings::OPTION_LOGIN_SHOW_SOCIAL_LOGIN_BUTTONS => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'login',
				'subcategory' => 'social-login',
				'title' => 'Add social login buttons to the login form',
				'desc' => 'If enabled the social login buttons will be added to the login form by default. '
						. 'If disabled you can still use the social login by using the [cmreg-social-login] shortcode.',
			),
			Settings::OPTION_SOCIAL_LOGIN_ENABLE_ALLOW_REGISTRATION => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'login',
				'subcategory' => 'social-login',
				'title' => 'Enable registration using social login',
				'desc' => 'If enabled a Wordpress account will be automatically created for a new user that used the social login button '
						. '(when plugin won\'t find any associated account). If disabled then user won\'t be logged if there\'s no WP account '
						. 'with the same email address.',
			),
			Settings::OPTION_REGISTER_SHOW_SOCIAL_LOGIN_BUTTONS => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'login',
				'subcategory' => 'social-login',
				'title' => 'Add social login buttons to the registration form',
				'desc' => 'If enabled the social login buttons will be added to the registration form by default. '
					. 'If disabled you can still use the social login by using the [cmreg-social-login] shortcode.',
			),
			Settings::OPTION_SOCIAL_LOGIN_FACEBOOK_APP_ID => array(
				'type' => Settings::TYPE_STRING,
				'default' => '',
				'category' => 'login',
				'subcategory' => 'social-login',
				'title' => 'Facebook App ID',
				'desc' => 'Create a <a href="http://developers.facebook.com" target="_blank">Facebook Login App</a> and enter the following URL '
						. 'into the "Valid OAuth redirect URIs":<br><kbd>' . SocialLoginController::getFacebookValidCallbackUrl() .'</kbd><br><br>'
						. 'Then go to App Review and make your App public.',
			),
			Settings::OPTION_SOCIAL_LOGIN_FACEBOOK_APP_SECRET => array(
				'type' => Settings::TYPE_STRING,
				'default' => '',
				'category' => 'login',
				'subcategory' => 'social-login',
				'title' => 'Facebook App Secret',
				'desc' => '',
			),
		));
	}
	
	
	static function cmreg_settings_pages($categories) {
		$categories['email'] = 'Email';
		$categories['labels'] = 'Labels';
		return $categories;
	}
	
	
	static function cmreg_email_headers($headers) {
		if (Settings::getOption(Settings::OPTION_EMAIL_USE_HTML)) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}
		return $headers;
	}
	

}
