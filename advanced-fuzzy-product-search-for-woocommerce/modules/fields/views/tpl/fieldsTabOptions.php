<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$options = UtilsAfsw::getArrayValue($this->settings, 'options', array(), 2);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
		<?php esc_html_e('General', 'advanced-fuzzy-search'); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Place the Fuzzy Search Field by adding this shortcode anywhere on your site or via standard WordPress widgets (Appearance->Widget)', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('Widget shortcode', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php
				$value = '[' . AFSW_SHORTCODE . ' id=' . $this->field_id . ']';
				HtmlAfsw::text('', array(
					'value' => $value,
					'attrs' => 'readonly class="woobewoo-shortcode woobewoo-width-full"',
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('PHP code lets display the Fuzzy Search Field through themes/plugins files (for example in the site footer). You can use any shortcode in this way.', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('PHP code', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php
				$value = "<?php echo do_shortcode('[" . AFSW_SHORTCODE . ' id=' . $this->field_id . "]'); ?>";
				HtmlAfsw::text('', array(
					'value' => $value,
					'attrs' => 'readonly class="woobewoo-shortcode woobewoo-width400"',
				));
				?>
		</div>
	</div>
</div>
<?php 
if ($this->current_theme['support']) { 
	/* translators: parent theme name */
	$parent = empty($this->current_theme['parent']) ? '' : ' (' . sprintf(__( 'child theme of <b>%s</b>', 'advanced-fuzzy-search' ), $this->current_theme['parent']) . ')';
	/* translators: 1: theme 2: parent theme */
	$tooltip = sprintf(__('You are using the %1$s theme%2$s. We support this theme so you can replace all default search fields.', 'advanced-fuzzy-search'), $this->current_theme['name'], $parent);
	?>
	<div class="row row-options-block">
		<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php echo esc_attr($tooltip); ?>">
			<?php esc_html_e('Replace search fields', 'advanced-fuzzy-search'); ?>
		</div>
		<div class="<?php echo esc_attr($bValues); ?>">
			<div class="options-value">
				<?php 
					HtmlAfsw::checkboxToggle('options[replace_theme_field]', array(
						'checked' => UtilsAfsw::getArrayValue($options, 'replace_theme_field', false )
					));
				?>
			</div>
		</div>
	</div>
<?php } ?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Display search field on', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('Display search field on', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlAfsw::selectBox('options[display_on]', array(
					'options' => array(
						'' => __('All devices', 'advanced-fuzzy-search'),
						'mobile' => __('Mobile only', 'advanced-fuzzy-search'),
						'desktop' => __('Desktop only', 'advanced-fuzzy-search')
					),
					'value' => UtilsAfsw::getArrayValue($options, 'display_on'),
				));
				?>
		</div>
	</div>
</div>
<?php 
	$output = UtilsAfsw::getArrayValue($options, 'output', 'search_page');
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Output results', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('Output results', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlAfsw::selectBox('options[output]', array(
					'options' => array(
						'search_page' => __('on the WooCommerce search page', 'advanced-fuzzy-search'),
						'same_page' => __('on the same page', 'advanced-fuzzy-search'),
						'redirect' => __('redirect to the page', 'advanced-fuzzy-search')
					),
					'value' => $output,
				));
				?>
		</div>
		<div class="options-value<?php echo 'redirect' == $output ? '' : ' woobewoo-hidden'; ?>" data-select="options[output]" data-select-value="redirect">
			<?php 
				HtmlAfsw::selectBox('options[redirect_page]', array(
					'options' => $this->pages,
					'value' => UtilsAfsw::getArrayValue($options, 'redirect_page'),
				));
				?>
		</div>
	</div>
</div>
<?php 
	$enable = UtilsAfsw::getArrayValue($options, 'save_history', 1, 1, false, true, true);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('The option must be enabled for the autocomplete to work by history.', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('Save history', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlAfsw::checkboxToggle('options[save_history]', array(
					'checked' => $enable
				));
				?>
		</div>
		<div class="options-value<?php echo $enable ? '' : ' woobewoo-hidden'; ?>" data-parent="options[save_history]">
			<div class="options-label"><?php esc_html_e('keep history', 'advanced-fuzzy-search'); ?></div>
			<?php 
				HtmlAfsw::selectBox('options[keep_history]', array(
					'options' => array(
						'day' => __('one day', 'advanced-fuzzy-search'),
						'week' => __('one week', 'advanced-fuzzy-search'),
						'month' => __('one month', 'advanced-fuzzy-search'),
						'year' => __('one year', 'advanced-fuzzy-search')
					),
					'value' => UtilsAfsw::getArrayValue($options, 'keep_history'),
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
		<?php esc_html_e('Ajax loading', 'advanced-fuzzy-search'); ?>
	</div>
</div>
<?php 
	$enable = UtilsAfsw::getArrayValue($options, 'enable_ajax', 1, 1, false, true, true);
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('This option enables Ajax search. Product filtering and displaying results in a browser will be run in the background without full page reload.', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('Enable Ajax', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlAfsw::checkboxToggle('options[enable_ajax]', array(
					'checked' => $enable
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block<?php echo $enable ? '' : ' woobewoo-hidden'; ?>" data-parent="options[enable_ajax]">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Product container selector', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('Product container selector', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?>">
		<div class="options-value">
			<?php 
				HtmlAfsw::text('options[output_container]', array(
					'value' => UtilsAfsw::getArrayValue($options, 'output_container'),
				));
				?>
		</div>
	</div>
</div>
<?php 
	$loader = UtilsAfsw::getArrayValue($options, 'loader_type');
?>
<div class="row row-options-block<?php echo $enable ? '' : ' woobewoo-hidden'; ?>" data-parent="options[enable_ajax]">
	<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Enable show loader icon while search results are loading.', 'advanced-fuzzy-search'); ?>">
		<?php esc_html_e('Show loader icon', 'advanced-fuzzy-search'); ?>
	</div>
	<div class="<?php echo esc_attr($bValues); ?> afsw-media-block">
		<div class="options-value">
			<?php 
				HtmlAfsw::selectBox('options[loader_type]', array(
					'options' => array(
						'' => __('none', 'advanced-fuzzy-search'),
						'woo' => __('woo-icon', 'advanced-fuzzy-search'),
						'icon' => __('custom icon', 'advanced-fuzzy-search') . $optionPro,
						'custom' => __('custom image', 'advanced-fuzzy-search') . $optionPro
					),
					'value' => $loader,
				));
				?>
		</div>
		<div class="options-value<?php echo 'woo' == $loader ? '' : ' woobewoo-hidden'; ?>" data-select="options[loader_type]" data-select-value="woo">
			<div class="afsw-preview-icon-woo">
				<div class="afsw-search-loader afsw-default-loader"></div>
			</div>
		</div>
		<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabOptionsLoader', array('options' => $options)); ?>
	</div>
</div>
<div class="woobewoo-clear"></div>
