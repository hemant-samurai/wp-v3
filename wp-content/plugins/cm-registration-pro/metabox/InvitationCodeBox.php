<?php

namespace com\cminds\registration\metabox;

use com\cminds\registration\model\InvitationCode;

use com\cminds\registration\model\S2MembersLevels;

use com\cminds\registration\controller\InvitationCodesController;

class InvitationCodeBox extends MetaBox {

	const SLUG = 'cmreg-invitation-code';
	const NAME = 'Invitation Code';
	
	static protected $supportedPostTypes = array(InvitationCode::POST_TYPE);
	
	
	static function render($post) {
		
		wp_enqueue_style('cmreg-backend');
		wp_enqueue_script('cmreg-backend');
		
		static::renderNonceField($post);
		$code = InvitationCode::getInstance($post);
		$levels = S2MembersLevels::getAll();
		echo InvitationCodesController::loadBackendView('metabox', compact('levels', 'code'));
		
	}
	
	
	static function savePost($post_id) {
		if ($codeObj = InvitationCode::getInstance($post_id)) {
			if (isset($_POST[InvitationCode::META_CODE_STRING])) {
				$codeObj->setCodeString($_POST[InvitationCode::META_CODE_STRING]);
			}
			if (isset($_POST[InvitationCode::META_S2MEMBERS_LEVEL])) {
				$codeObj->setS2MembersLevel($_POST[InvitationCode::META_S2MEMBERS_LEVEL]);
			}
			if (isset($_POST[InvitationCode::META_EXPIRATION])) {
				$codeObj->setExpirationDate($_POST[InvitationCode::META_EXPIRATION]);
			}
			if (isset($_POST[InvitationCode::META_USERS_LIMIT])) {
				$codeObj->setUsersLimit($_POST[InvitationCode::META_USERS_LIMIT]);
			}
			if (isset($_POST[InvitationCode::META_EMAIL_VERIFICATION_STATUS])) {
				$codeObj->setEmailVerificationStatus($_POST[InvitationCode::META_EMAIL_VERIFICATION_STATUS]);
			}
			if (isset($_POST[InvitationCode::META_USER_ROLE])) {
				$codeObj->setUserRole($_POST[InvitationCode::META_USER_ROLE]);
			}
		}
	}
	
}