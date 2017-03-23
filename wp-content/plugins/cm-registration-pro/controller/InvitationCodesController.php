<?php

namespace com\cminds\registration\controller;

use com\cminds\registration\model\Labels;

use com\cminds\registration\model\User;

use com\cminds\registration\model\Settings;

use com\cminds\registration\metabox\InvitationCodeBox;

use com\cminds\registration\model\InvitationCode;

use com\cminds\registration\model\S2MembersLevels;

use com\cminds\registration\App;

class InvitationCodesController extends Controller {
	
	const PAGE_NAME = 'Invitation Codes';
	const PARAM_USERS_INVIT_CODE = 'cmreg_invitation_code_id';
	const PARAM_INVITATION_CODE = 'cmreg_code';
	
	const FIELD_INVITATION_CODE = 'cmreg_invit_code';
	
	static $filters = array(
		'post_row_actions' => array('args' => 2),
		'cmreg_options_config' => array('priority' => 50),
		'cmreg_create_invitation_code' => array('args' => 2),
	);
	static $actions = array(
		'pre_get_users' => array('args' => 1),
		'register_form' => array('args' => 1),
		'cmreg_register_form' => array('args' => 1, 'method' => 'register_form', 'priority' => 50),
		'register_post' => array('args' => 3),
		'register_new_user' => array('args' => 1, 'priority' => 20),
	);
	
	
	static function bootstrap() {
		parent::bootstrap();
		add_filter('manage_edit-' . InvitationCode::POST_TYPE .'_columns', array(__CLASS__, 'adminColumnsHeader'));
		add_action('manage_' . InvitationCode::POST_TYPE . '_posts_custom_column', array(__CLASS__, 'adminColumns'), 10, 2);
	}
	
	
	static function adminColumnsHeader($cols) {
// 		$lastValue = end($cols);
// 		$lastKey = key($cols);
// 		array_pop($cols);
		$cols[InvitationCode::META_EXPIRATION] = 'Expiration';
		$cols[InvitationCode::META_USERS_LIMIT] = 'Users limit';
		if (Settings::getOption(Settings::OPTION_S2MEMBERS_ENABLE)) {
			$cols[InvitationCode::META_S2MEMBERS_LEVEL] = 'S2Members Level';
		}
		$cols[InvitationCode::META_USER_ROLE] = 'User role';
		$cols[InvitationCode::META_CODE_STRING] = 'Invitation code';
// 		$cols[$lastKey] = $lastValue;
		return $cols;
	}
	
	
	static function adminColumns($columnName, $id) {
		if ($code = InvitationCode::getInstance($id)) {
			switch ($columnName) {
				case InvitationCode::META_CODE_STRING:
					printf('<input type="text" readonly value="%s" />', esc_attr($code->getCodeString()));
					break;
				case InvitationCode::META_S2MEMBERS_LEVEL:
					echo S2MembersLevels::getLevelName($code->getS2MembersLevel());
					break;
				case InvitationCode::META_EXPIRATION:
					if ($date = $code->getExpirationDate()) {
						echo Date('Y-m-d', $date) . ' 00:00:00';
					} else {
						echo 'never';
					}
					break;
				case InvitationCode::META_USERS_LIMIT:
					if ($limit = $code->getUsersLimit()) {
						echo $code->getUsersCount() .'/'. $limit;
					} else {
						echo 'unlimited';
					}
					break;
				case InvitationCode::META_USER_ROLE:
					echo $code->getUserRole();
					break;
			}
		}
	}
	
	
	/**
	 * Filter users by invitation code
	 * @param \WP_User_Query $query
	 */
	static function pre_get_users(\WP_User_Query $query) {
		global $pagenow;
		if (is_admin() AND $pagenow == 'users.php' AND !empty($_GET[self::PARAM_USERS_INVIT_CODE])) {
			$query->set('meta_key', User::META_INVITATION_CODE);
			$query->set('meta_value', $_GET[self::PARAM_USERS_INVIT_CODE]);
		}
	}
	
	
	static function post_row_actions($actions, $post) {
		if ( $post->post_type === InvitationCode::POST_TYPE AND $code = InvitationCode::getInstance($post) ) {
			$url = add_query_arg(self::PARAM_USERS_INVIT_CODE, $code->getId(), admin_url('users.php'));
			$actions['cmreg_invited_users'] = sprintf('<a href="%s">%s</a>', esc_attr($url), 'Registered users');
		}
		return $actions;
	}
	
	

	/**
	 * Display extra field on the registration form.
	 *
	 * @param string $place
	 */
	static function register_form($place = null) {
		if (App::isLicenseOk()) {
			echo self::getInvitationCodeField();
		}
	}
	
	

	static function getInvitationCodeField() {
		$invitationCodeRequired = (Settings::getOption(Settings::OPTION_REGISTER_INVIT_CODE) == Settings::INVITATION_CODE_REQUIRED);
		$invitationCode = (empty($_GET[self::PARAM_INVITATION_CODE]) ? '' : $_GET[self::PARAM_INVITATION_CODE]);
		$showInvitationCode = (Settings::getOption(Settings::OPTION_REGISTER_INVIT_CODE) != Settings::INVITATION_CODE_DISABLED);
		if ($showInvitationCode) {
			return self::loadFrontendView('registration', compact('invitationCodeRequired', 'invitationCode'));
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
		if (App::isLicenseOk() AND Settings::getOption(Settings::OPTION_REGISTER_INVIT_CODE) == Settings::INVITATION_CODE_REQUIRED) {
			// Validate invitation code before registration
			$invitationCode = (empty($_POST[static::FIELD_INVITATION_CODE]) ? '' : $_POST[static::FIELD_INVITATION_CODE]);
			$code = InvitationCode::getByCode($invitationCode);
			if (empty($code) OR !$code->canUse()) {
				$errors->add('invalid_invitation_code', Labels::getLocalized('register_invit_code_invalid_error'));
			}
		}
	}
	
	
	/**
	 * After successful registration
	 *
	 * @param unknown $userId
	 */
	static function register_new_user($userId) {
		
		if (!App::isLicenseOk()) return;
		
		$invitationCode = (empty($_POST[static::FIELD_INVITATION_CODE]) ? '' : $_POST[static::FIELD_INVITATION_CODE]);
		$code = InvitationCode::getByCode($invitationCode);
		if ($code) {
			$code->registerInvitation($userId);
		}
		
	}
	
	
	static function cmreg_options_config($config) {
		return array_merge($config, array(
			Settings::OPTION_REGISTER_INVIT_CODE => array(
				'type' => Settings::TYPE_RADIO,
				'options' => array(
					Settings::INVITATION_CODE_DISABLED => 'disabled',
					Settings::INVITATION_CODE_OPTIONAL => 'optional',
					Settings::INVITATION_CODE_REQUIRED => 'required',
				),
				'default' => Settings::INVITATION_CODE_OPTIONAL,
				'category' => 'register',
				'subcategory' => 'register',
				'title' => 'Ask for invitation code',
			),
		));
	}
	
	
	static function cmreg_create_invitation_code($result, $args) {
		
		$result = array();
		
		$post = array(
			'ID' => null,
			'post_title' => $args['name'],
			'post_type' => InvitationCode::POST_TYPE,
			'post_status' => 'publish',
		);
		$obj = new InvitationCode((object)$post);
		if ($obj->save()) {
			$obj->setCodeString(isset($args['code']) ? $args['code'] : $obj->getOrGenerateCodeString());
			if (isset($args['role'])) {
				$obj->setUserRole($args['role']);
			}
			if (isset($args['usersLimit'])) {
				$obj->setUsersLimit($args['usersLimit']);
			}
			if (isset($args['expirationDate'])) {
				$obj->setExpirationDate($args['expirationDate']);
			}
			$result['ID'] = $obj->getId();
			$result['codeString'] = $obj->getCodeString();
			$result['instance'] = $obj;
		} else {
			$result = false;
		}
		
		return $result;
		
	}
	
	
}

