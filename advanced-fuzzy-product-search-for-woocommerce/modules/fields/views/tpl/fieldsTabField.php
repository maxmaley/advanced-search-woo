<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$options = UtilsAfsw::getArrayValue($this->settings, 'field', array(), 2);
$module = $this->getModule();
$modPath = $module->getModPath();
?>
<div id="afswFieldOptions" class="afsw-options-panel">
	<div class="afsw-scroll-wrapper">

		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Search field', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Search input placeholder', 'advanced-fuzzy-search'); ?>">
				<?php 
					esc_html_e('Input placeholder', 'advanced-fuzzy-search'); 
				?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::text('field[placeholder]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'placeholder', __('Search for products...', 'advanced-fuzzy-search'), 0, false, false, true),
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('View as', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('View as', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?> afsw-media-block">
				<div class="options-value">
					<?php 
						HtmlAfsw::selectBox('field[view_as]', array(
							'options' => array(
								'bar' => __('search bar', 'advanced-fuzzy-search'),
								'icon' =>  __('search icon', 'advanced-fuzzy-search') . $optionPro 
							),
							'value' => UtilsAfsw::getArrayValue($options, 'view_as', 'bar'),
						));
						?>
				</div>
				<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabFieldView', array('options' => $options)); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Full width field with submit button and button for search location.', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Field width', 'advanced-fuzzy-search'); ?>
			</div>			
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::number('field[width]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'width', '', 1),
							'attrs' => 'min="0" class="woobewoo-width80" id="afswFieldWidth"'
						));
						?>
				</div>
				<div class="options-value">
					<?php 
						HtmlAfsw::selectBox('field[width_units]', array(
							'options' => array(
								'' => 'px',
								'%' => '%'
							),
							'value' => UtilsAfsw::getArrayValue($options, 'width_units'),
							'attr' => 'id="afswFieldWidthUnits"'
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Search by Entern', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Search by Enter', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('field[search_by_enter]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'search_by_enter', 1, 1, false, true),
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Custom styles', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Field styles', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?> afsw-media-block">
				<div class="options-value">
					<?php 
					if ($this->is_pro) {
						HtmlAfsw::button(array(
							'value' => esc_attr__('Select styles', 'advanced-fuzzy-search'),
							'attrs' => 'class="button button-mini button-minor afsw-select-custom-styles" data-type="field"'));
					} else {
						HtmlAfsw::proOptionLink();
					}
					?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Submit button', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
<?php
	$enabled = UtilsAfsw::getArrayValue($options, 'show_submit', 1, 1, false, true);
?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Show submit button', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Show submit button', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('field[show_submit]', array(
							'checked' => $enabled
						));
						?>
				</div>
			</div>
		</div>
<?php
	$viewAs = UtilsAfsw::getArrayValue($options, 'button_as', 'text');
?>
		<div class="row row-options-block<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="field[show_submit]">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Button type', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Button type', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?> afsw-media-block">
				<div class="options-value">
					<?php 
						HtmlAfsw::selectBox('field[button_as]', array(
							'options' => array(
								'text' => __('text', 'advanced-fuzzy-search'),
								'search' => __('search icon', 'advanced-fuzzy-search'),
								'icon' => __('custom icon', 'advanced-fuzzy-search') . $optionPro,
							),
							'value' => $viewAs,
						));
						?>
				</div>
				<div class="options-value<?php echo 'text' == $viewAs ? '' : ' woobewoo-hidden'; ?>" data-select="field[button_as]" data-select-value="text">
					<?php 
						HtmlAfsw::text('field[button_text]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'button_text', __('Search', 'advanced-fuzzy-search')),
							));
						?>
				</div>
				<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabFieldButton', array('options' => $options)); ?>
			</div>
		</div>
		<div class="row row-options-block<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="field[show_submit]">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Custom styles', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Button styles', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?> afsw-media-block">
				<div class="options-value">
					<?php 
					if ($this->is_pro) {
						HtmlAfsw::button(array(
							'value' => esc_attr__('Select styles', 'advanced-fuzzy-search'),
							'attrs' => 'class="button button-mini button-minor afsw-select-custom-styles" data-type="button"'));
					} else {
						HtmlAfsw::proOptionLink();
					}
					?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Where button', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Show where button', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Show where button', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
					if ($this->is_pro) {
						HtmlAfsw::checkboxToggle('field[show_where]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'show_where', 1, 1, false, true)
						));
					} else {
						HtmlAfsw::proOptionLink();
					}
					?>
				</div>
			</div>
		</div>
<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabFieldWhere', array('options' => $options)); ?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Preloader', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('The minimum number of characters required to start loading the autocomplete.', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Minimum characters', 'advanced-fuzzy-search'); ?>
			</div>			
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::number('field[pre_min_chars]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'pre_min_chars', '3', 1),
							'attrs' => 'min="1" class="woobewoo-width80"'
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Specify a delay to start loading the autocomplete. The delay is specified in milliseconds between the last character input in the search field and the start of the preloader.', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Delay preloader', 'advanced-fuzzy-search'); ?>
			</div>			
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::number('field[pre_delay]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'pre_delay', 500, 1),
							'attrs' => 'min="1" class="woobewoo-width80"'
						));
						?>
				</div>
			</div>
		</div>
<?php
	$enabled = UtilsAfsw::getArrayValue($options, 'show_preloader', false);
?>
		<div class="row row-options-block afsw-media-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Show preloader', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Show preloader', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('field[show_preloader]', array(
							'checked' => $enabled
						));
						?>
				</div>
				<div class="options-value<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="field[show_preloader]">
					<?php 
						HtmlAfsw::selectBox('field[pre_type]', array(
							'options' => array(
								'spinner' => __('spinner', 'advanced-fuzzy-search'),
								'circle-o-notch' => __('circle', 'advanced-fuzzy-search'),
								'refresh' => __('refresh', 'advanced-fuzzy-search'),
								'cog' => __('cog', 'advanced-fuzzy-search'),
								'custom' => __('custom icon', 'advanced-fuzzy-search') . $optionPro 
							),
							'value' => UtilsAfsw::getArrayValue($options, 'pre_type', 'spinner'),
							));
						?>
				</div>
				<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabFieldPreload', array('options' => $options)); ?>
			</div>
		</div>
	</div>
</div>
<div id="afswFieldPreview" class="afsw-preview-panel">
	<div class="afsw-preview-title"><?php esc_html_e('Preview', 'advanced-fuzzy-search'); ?></div>
	<div class="afsw-preview-subtitle"><?php esc_html_e('Field', 'advanced-fuzzy-search'); ?></div>
	<div class="afsw-preview-wrap" id="afswFieldPreviewWrap">
	</div>
</div>
<div class="woobewoo-clear"></div>
