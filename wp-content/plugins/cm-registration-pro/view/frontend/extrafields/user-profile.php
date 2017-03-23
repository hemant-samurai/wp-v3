<?php

?>

<h3>CM Registration Extra Fields</h3>
<table class="form-table"><tbody>
	<?php foreach ($fields as $i => $field): ?>
		<?php if ($i == 0) continue; ?>
		<tr>
			<th valign="top"><?php echo esc_html($field['label']); ?>:</th>
			<td><?php echo esc_attr($field['value']); ?></td>
		</tr>
	<?php endforeach; ?>
</tbody></table>