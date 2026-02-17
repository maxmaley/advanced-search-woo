<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$options = UtilsAfsw::getArrayValue($this->settings, 'autocomplete', array(), 2);
$module = $this->getModule();
$modPath = $module->getModPath();
$sections = $module->getAutocompleteSections();
$optionPro = ( $this->is_pro ? '' : ' PRO' );
$orderList = UtilsAfsw::getArrayValue($options, 'order');
$orderArr = explode(',', $orderList);
?>
<div id="afswAutocompleteOptions" class="afsw-options-panel">
<?php
HtmlAfsw::hidden('autocomplete[order]', array('value' => $orderList, 'attrs' => 'id="afswAutocompleteOrder"'));
?>
	<div class="afsw-scroll-wrapper">
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Sections', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-sections-list" id="afswSectionsList">
<?php 
foreach ($sections as $section => $d) {
	if (!in_array($section, $orderArr)) {
		$orderArr[] = $section;
	}
}
foreach ($orderArr as $section) {
	if (!isset($sections[$section])) {
		continue;
	}
	include_once 'autocompleteSection' . strFirstUpAfsw($section) . '.php';
}
?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Cache autocomplete', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
<?php
	$enabled = UtilsAfsw::getArrayValue($options, 'use_cache', 0, 1);
?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?>">
				<?php 
					esc_html_e('Use caching', 'advanced-fuzzy-search'); 
				?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('autocomplete[use_cache]', array(
							'checked' => $enabled,
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="autocomplete[use_cache]">
			<div class="<?php echo esc_attr($cLabel); ?>">
				<?php 
					esc_html_e('Clear cache after', 'advanced-fuzzy-search'); 
				?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::number('autocomplete[keep_cache_count]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'keep_cache_count', 2, 1),
							'attrs' => 'min="1" class="woobewoo-width60" id="afswFieldWidth"'
						));
						?>
				</div>
				<div class="options-value">
					<?php 
						HtmlAfsw::selectBox('autocomplete[keep_cache_period]', array(
							'options' => array(
								'min' => __('Minutes', 'advanced-fuzzy-search'),
								'hours' => __('Hours', 'advanced-fuzzy-search'),
								'days' => __('Days', 'advanced-fuzzy-search')
							),
							'value' => UtilsAfsw::getArrayValue($options, 'keep_cache_period', 'hours'),
							'attr' => 'id="afswFieldWidthUnits"'
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="autocomplete[use_cache]">
			<div class="<?php echo esc_attr($cLabel); ?>">
				<?php 
					esc_html_e('Disable history caching', 'advanced-fuzzy-search'); 
				?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('autocomplete[disable_cache_history]', array(
							'checked' => $enabled,
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Popup', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
<?php
	$enabled = UtilsAfsw::getArrayValue($options, 'show_noresults', 0, 1);
?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?>">
				<?php 
					esc_html_e('Show No results', 'advanced-fuzzy-search'); 
				?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('autocomplete[show_noresults]', array(
							'checked' => $enabled,
						));
						?>
				</div>
				<div class="options-value<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="autocomplete[show_noresults]">
					<?php 
						HtmlAfsw::text('autocomplete[noresults_text]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'noresults_text', __('No results', 'advanced-fuzzy-search'), 0, false, false, true),
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Group sections', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Group sections', 'advanced-fuzzy-search'); ?>
			</div>			
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('autocomplete[group_sections]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'group_sections', false)
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Separate elements', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Separate items', 'advanced-fuzzy-search'); ?>
			</div>			
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('autocomplete[separate_terms]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'separate_terms', false)
						));
						?>
				</div>
			</div>
		</div>
<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabAutocompletePopup', array('options' => $options)); ?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Custom styles', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Custom styles', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?> afsw-media-block">
				<div class="options-value">
					<?php 
					if ($this->is_pro) {
						HtmlAfsw::button(array(
							'value' => esc_attr__('Select styles', 'advanced-fuzzy-search'),
							'attrs' => 'class="button button-mini button-minor afsw-select-custom-styles" data-type="auto_popup"'));
					} else {
						HtmlAfsw::proOptionLink();
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="afswAutocompletePreview" class="afsw-preview-panel">
	<div class="afsw-preview-title"><?php esc_html_e('Preview', 'advanced-fuzzy-search'); ?></div>
	<div class="afsw-preview-subtitle"><?php esc_html_e('Autocomplete', 'advanced-fuzzy-search'); ?></div>
	<div class="afsw-preview-wrap" id="afswAutocompletePreviewWrap">
	</div>
</div>
<div class="woobewoo-clear"></div>
