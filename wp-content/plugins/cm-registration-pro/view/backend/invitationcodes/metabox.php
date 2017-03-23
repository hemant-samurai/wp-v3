<?php

use com\cminds\registration\model\Settings;

use com\cminds\registration\model\InvitationCode;

/* @var $code InvitationCode */

$emailVerificationStatus = $code->getEmailVerificationStatus();
$emailVerificationOptions = array(
	InvitationCode::EMAIL_VERIFICATION_NO => 'No',
	InvitationCode::EMAIL_VERIFICATION_YES => 'Yes',
	InvitationCode::EMAIL_VERIFICATION_GLOBAL => 'Follow global settings',
);
if (empty($emailVerificationOptions[$emailVerificationStatus])) {
	$emailVerificationStatus = InvitationCode::EMAIL_VERIFICATION_GLOBAL;
}

?><p class="cmreg-code">
	Code: <input type="text" name="<?php echo esc_attr(InvitationCode::META_CODE_STRING);
		?>" value="<?php echo esc_attr($code->getOrGenerateCodeString()); ?>" class="cmreg-code-input" />
	<input type="button" value="Generate new" class="cmreg-code-generate" />
</p>

<p class="cmreg-expiration">
	<strong>Expiration date:</strong>
	code will be valid until midnight<br />
	<input type="date" name="<?php echo esc_attr(InvitationCode::META_EXPIRATION); ?>" value="<?php echo esc_attr($code->getExpirationDateFormatted()); ?>" /> 00:00:00
</p>

<p class="cmreg-users-limit">
	Users limit: <input type="number" name="<?php echo esc_attr(InvitationCode::META_USERS_LIMIT); ?>" value="<?php echo esc_attr($code->getUsersLimit()); ?>" />
</p>

<?php if (Settings::getOption(Settings::OPTION_S2MEMBERS_ENABLE)): ?>
	<p>S2Members level: <select name="<?php echo InvitationCode::META_S2MEMBERS_LEVEL; ?>">
		<option value="0">-- none --</option><?php
		$currentLevel = $code->getS2MembersLevel();
		foreach ($levels as $n => $name) {
			printf('<option value="%d"%s>%s</option>', $n, selected($n, $currentLevel, false), $name);
		}
	?></select></p>
<?php endif; ?>

<p class="cmreg-email-verification">
Require email verification:<br />
<?php foreach ($emailVerificationOptions as $value => $label): ?>
	<?php printf('<label><input type="radio" name="%s" value="%s" %s /> %s</label><br />',
			esc_attr(InvitationCode::META_EMAIL_VERIFICATION_STATUS),
			esc_attr($value),
			checked($value, $emailVerificationStatus, false),
			$label
	); ?>
<?php endforeach; ?>
Global option is: <strong><?php echo (Settings::getOption(Settings::OPTION_REGISTER_EMAIL_VERIFICATION_ENABLE) ? 'enabled' : 'disabled');
	?></strong>
</p>

<p class="cmreg-user-role">
User role: <select name="<?php echo InvitationCode::META_USER_ROLE; ?>">
	<option value="">Follow global option</option>
	<?php foreach (Settings::getRolesOptions() as $roleName => $roleLabel): ?>
		<?php printf('<option value="%s"%s>%s</option>',
				esc_attr($roleName),
				selected($roleName, $code->getUserRole(), false),
				esc_html($roleLabel)
		); ?>
	<?php endforeach; ?>
	</select>
Global option is: <strong><?php echo Settings::getOption(Settings::OPTION_REGISTER_DEFAULT_ROLE); ?></strong>
</p>