<?php

use com\cminds\registration\controller\InvitationCodesController;

use com\cminds\registration\model\Labels;

?><div class="cmreg-invitation-code-field" data-input-visible="<?php echo intval($invitationCodeRequired OR !empty($invitationCode)); ?>">
	<a href=""><?php echo Labels::getLocalized('register_invitation_code_link'); ?></a>
	<input type="text" class="text" name="<?php echo InvitationCodesController::FIELD_INVITATION_CODE; ?>" <?php echo ($invitationCodeRequired ? 'required' : '');
		?> placeholder="<?php echo esc_attr(Labels::getLocalized('field_invitation_code')); ?>" value="<?php echo $invitationCode; ?>" />
</div>