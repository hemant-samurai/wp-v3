<?php

?>

<form method="post" class="cmreg-s2members-levels-codes">

<table class="wp-list-table widefat fixed striped posts">
	<thead><tr>
		<th>Membership Level</th>
		<th>Code</th>
	</tr></thead>
	<tbody><?php foreach ($levels as $n => $level): ?>
		<tr>
			<td><?php echo $level['name']; ?></td>
			<td data-col="code">
				<?php printf('<input type="text" name="codes[%d]" readonly value="%s" />', $n, esc_attr($level['code'])); ?>
				<input type="button" value="Generate new" />
			</td>
		</tr>
	<?php endforeach; ?></tbody>
</table>

<p>
	<input type="hidden" name="nonce" value="<?php echo $nonce; ?>" />
	<input type="submit" value="Save" class="button button-primary" />
</p>

</form>