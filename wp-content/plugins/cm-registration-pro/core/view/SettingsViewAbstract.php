<?php

namespace com\cminds\registration\view;

use com\cminds\registration\model\Settings;

abstract class SettingsViewAbstract {
	
	protected $categories = array();
	protected $subcategories = array();
	
	
	
	public function render() {
		$result = '';
		$categories = $this->getCategories();
		foreach ($categories as $category => $title) {
			$result .= $this->renderCategory($category);
		}
		return $result;
	}
	
	
	
	public function renderCategory($category) {
		$result = '';
		$subcategories = $this->getSubcategories();
		if (!empty($subcategories[$category])) {
			foreach ($subcategories[$category] as $subcategory => $title) {
				$result .= $this->renderSubcategory($category, $subcategory);
			}
		}
		return '<div class="settings-category settings-category-'. $category .'">'. apply_filters('cmreg-settings-category', $result, $category) .'</div>';
	}
	
	
	abstract protected function getCategories();
	
	
	abstract protected function getSubcategories();
	
	
	
	public function renderSubcategory($category, $subcategory) {
		$result = '';
		$subcategories = $this->getSubcategories();
		if (isset($subcategories[$category]) AND isset($subcategories[$category][$subcategory])) {
			$options = Settings::getOptionsConfigByCategory($category, $subcategory);
			foreach ($options as $name => $option) {
				$result .= $this->renderOption($name, $option);
			}
		}
		return '<div class="settings_'. $category .'_'. $subcategory .'">'. apply_filters('cmreg-settings-subcategory', $result, $category, $subcategory) .'</div>';
	}
	
	
	public function renderOption($name, array $option = array()) {
		if (empty($option)) $option = Settings::getOptionConfig($name);
		return $this->renderOptionTitle($option)
				. $this->renderOptionControls($name, $option)
				. $this->renderOptionDescription($option);
	}
	
	
	public function renderOptionTitle($option) {
		return $option['title'];
	}
	
	public function renderOptionControls($name, array $option = array()) {
		if (empty($option)) $option = Settings::getOptionConfig($name);
		switch ($option['type']) {
			case Settings::TYPE_BOOL:
				return $this->renderBool($name);
			case Settings::TYPE_INT:
				return $this->renderInputNumber($name);
			case Settings::TYPE_PERCENT:
				return $this->renderInputPercent($name);
			case Settings::TYPE_TEXTAREA:
				return $this->renderTextarea($name);
			case Settings::TYPE_RICH_TEXT:
				return $this->renderRichText($name);
			case Settings::TYPE_RADIO:
				return '<div class="multiline">' . $this->renderRadio($name, $option['options']) . '</div>';
			case Settings::TYPE_SELECT:
				return $this->renderSelect($name, $option['options']);
			case Settings::TYPE_MULTISELECT:
				return $this->renderMultiSelect($name, $option['options']);
			case Settings::TYPE_MULTICHECKBOX:
				return $this->renderMultiCheckbox($name, $option['options']);
			case Settings::TYPE_CSV_LINE:
				return $this->renderCSVLine($name);
			case Settings::TYPE_USERS_LIST:
				return $this->renderUsersList($name);
			case Settings::TYPE_CUSTOM:
				return $option['content'];
			case Settings::TYPE_EXTRA_FIELDS:
				return $this->renderExtraFields($name);
			default:
				return $this->renderInputText($name);
		}
	}
	
	public function renderOptionDescription($option) {
		return (isset($option['desc']) ? $option['desc'] : '');
	}
	
	
	protected function renderInputText($name, $value = null) {
		if (is_null($value)) {
			$value = Settings::getOption($name);
		}
		return sprintf('<input type="text" name="%s" value="%s" />', esc_attr($name), esc_attr($value));
	}
	
	protected function renderInputNumber($name) {
		return sprintf('<input type="number" name="%s" value="%s" />', esc_attr($name), esc_attr(Settings::getOption($name)));
	}
	
	protected function renderInputPercent($name) {
		return sprintf('<input type="number" min="0" max="100" step="1" name="%s" value="%s" />%%', esc_attr($name), esc_attr(Settings::getOption($name)));
	}
	
	protected function renderCSVLine($name) {
		$value = Settings::getOption($name);
		if (is_array($value)) $value = implode(',', $value);
		return $this->renderInputText($name, $value);
	}
	
	
	protected function renderUsersList($name) {
		return sprintf('<div class="suggest-user" data-field-name="%s">
			<ul>%s</ul>
			<div><span>Find user:</span><input type="text" /> <input type="button" value="Add" /></div>
		</div>', $name, $this->renderUsersListItems($name));
	}
	
	
	protected function renderUsersListItems($name) {
		$value = Settings::getOption($name);
		if (!empty($value)) $users = get_users(array('include' => $value));
		$result = '';
		if (!empty($users)) foreach ($users as $user) {
			$result .= self::renderUsersListItem($name, $user->ID, $user->user_login);
		}
		return $result;
	}
	
	
	static public function renderUsersListItem($name, $userId, $login) {
		$template = '<li data-user-id="%d" data-user-login="%s">
			<a href="%s">%s</a> <a href="" class="btn-list-remove">&times;</a>
			<input type="hidden" name="%s[]" value="%d" /></li>';
		return sprintf($template,
			intval($userId),
			$login,
			esc_attr(get_edit_user_link($userId)),
			esc_html($login),
			$name,
			intval($userId)
		);
	}
	
	
	protected function renderTextarea($name) {
		return sprintf('<textarea name="%s" cols="60" rows="5">%s</textarea>', esc_attr($name), esc_html(Settings::getOption($name)));
	}
	
	
	protected function renderRichText($name) {
		ob_start();
		wp_editor(Settings::getOption($name), $name, array(
			'textarea_name' => $name,
			'textarea_rows' => 10,
		));
		return ob_get_clean();
	}
	
	
	protected function renderBool($name) {
		return $this->renderRadio($name, array(1 => 'Yes', 0 => 'No'), intval(Settings::getOption($name)));
	}
	
	
	protected function renderRadio($name, $options, $currentValue = null) {
		if (is_null($currentValue)) {
			$currentValue = Settings::getOption($name);
		}
		$result = '';
		$fieldName = esc_attr($name);
		foreach ($options as $value => $text) {
			$fieldId = esc_attr($name .'_'. $value);
			$result .= sprintf('<label><input type="radio" name="%s" id="%s" value="%s"%s /> %s</label>',
					$fieldName, $fieldId, esc_attr($value),
					( $currentValue == $value ? ' checked="checked"' : ''), esc_html($text)
			);
		}
		return $result;
	}
	
	
	protected function renderSelect($name, $options, $currentValue = null) {
		return sprintf('<div><select name="%s">%s</select></div>', esc_attr($name), $this->renderSelectOptions($name, $options, $currentValue));
	}
	
	
	protected function renderSelectOptions($name, $options, $currentValue = null) {
		if (is_null($currentValue)) {
			$currentValue = Settings::getOption($name);
		}
		$result = '';
		if (is_callable($options)) $options = call_user_func($options, $name);
		foreach ($options as $value => $text) {
			$result .= sprintf('<option value="%s"%s>%s</option>',
				esc_attr($value),
				( $this->isSelected($value, $currentValue) ? ' selected="selected"' : ''),
				esc_html($text)
			);
		}
		return $result;
	}
	
	
	protected function isSelected($option, $value) {
		if (is_array($value)) {
			return in_array($option, $value);
		} else {
			return ((string)$option == (string)$value);
		}
	}
	
	
	
	protected function renderMultiSelect($name, $options, $currentValue = null) {
		return sprintf('<div><select name="%s[]" multiple="multiple">%s</select>',
			esc_attr($name), $this->renderSelectOptions($name, $options, $currentValue));
	}
	

	protected function renderMultiCheckbox($name, $options, $currentValue = null) {
		$result = '';
		foreach ($options as $value => $label) {
			$result .= $this->renderMultiCheckboxItem($name, $value, $label, $currentValue);
		}
		return '<div>' . $result . '</div>';
	}
	
	
	protected function renderMultiCheckboxItem($name, $value, $label, $currentValue = null) {
		if (is_null($currentValue)) $currentValue = Settings::getOption($name);
		if (!is_array($currentValue)) $currentValue = array();
		return sprintf('<div><label><input type="checkbox" name="%s[]" value="%s"%s /> %s</label></div>',
			esc_attr($name),
			esc_attr($value),
			(in_array($value, $currentValue) ? ' checked="checked"' : ''),
			esc_html($label)
		);
	}
	
	
	protected function renderExtraFields($name) {
		
		$roles = Settings::getRolesOptions();
		$produceRolesOptions = function($currentRole) use ($roles) {
			$rolesOptions = array_map(function($roleLabel, $role) use ($currentRole) {
				return sprintf('<option value="%s"%s>%s</option>',
					esc_attr($role), selected($role, $currentRole, false), esc_html($roleLabel));
			}, $roles, array_keys($roles));
			return implode('', $rolesOptions);
		};
		
		$template = '<div class="cmreg-extra-field">
			<input type="text" name="%s[%d][%s]" value="%s" placeholder="Label" /><br />
			<input type="text" name="%s[%d][%s]" value="%s" placeholder="User meta name" /><br />
			<label title="Field will be disayed only if used shortcode [cmreg-registration-form role=some] with specific role parameter.">Only for role:
				<select name="%s[%d][%s]"><option value="0">-- any --</option>%s</select></label><br />
			<input type="number" name="%s[%d][%s]" value="%s" step="1" min="1" placeholder="Max length" />
			<label><input type="checkbox" name="%s[%d][%s]" value="1" %s /> Required</label>
			<span class="cmreg-extra-field-delete-btn" title="Delete field"><span class="dashicons dashicons-no-alt"></span>Delete</span>
		</div>';
		
		$values = Settings::getOption($name);
		if (!is_array($values)) $values = array();
		
		$out = '';
		
		$out .= sprintf($template,
			esc_attr($name), 0, 'label', '',
			esc_attr($name), 0, 'meta_name', '',
			esc_attr($name), 0, 'role', $produceRolesOptions(0),
			esc_attr($name), 0, 'maxlen', '',
			esc_attr($name), 0, 'required', ''
		);
		
		foreach ($values as $i => $value) {
			if ($i == 0) continue; // Ignore first template row
			$role = (isset($value['role']) ? $value['role'] : 0);
			$out .= sprintf($template,
				esc_attr($name), $i, 'label', $value['label'],
				esc_attr($name), $i, 'meta_name', $value['meta_name'],
				esc_attr($name), $i, 'role', $produceRolesOptions($role),
				esc_attr($name), $i, 'maxlen', $value['maxlen'],
				esc_attr($name), $i, 'required', checked(!empty($value['required']), true, false)
			);
		}
		
		$out .= '<input type="button" value="Add new field" class="cmreg-extra-fields-add-btn" />';
		
		return $out;
		
	}
	
	
}
