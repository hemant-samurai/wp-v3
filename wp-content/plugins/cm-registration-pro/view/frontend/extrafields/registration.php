<?php

use com\cminds\registration\model\Labels;

$label = $field['label'];
if (!empty($field['required'])) {
	$label .= ' ' . Labels::getLocalized('field_required');
}

?>

<div class="cmreg-registration-extra-field">
	<input type="text" class="text" name="cmreg_extra_field[<?php echo esc_attr($field['meta_name']);
		?>]" maxlength="<?php echo esc_attr($field['maxlen']); ?>" placeholder="<?php echo esc_attr($label);
		?>" <?php if (!empty($field['required'])) echo 'required'; ?> />
</div>