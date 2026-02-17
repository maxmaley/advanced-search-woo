<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="afsw-section" data-type="global">
	<div class="afsw-section-header">
		<div class="options-value">
			<?php 
				HtmlAfsw::checkboxToggle('autocomplete[show_global]', array(
					'checked' => UtilsAfsw::getArrayValue($options, 'show_global', false)
				));
				?>
		</div>
		<div class="afsw-section-title"><?php esc_html_e('Global search history', 'advanced-fuzzy-search'); ?></div>
		<a href="#" class="afsw-section-toggle"><i class="fa fa-chevron-down"></i></a>
	</div>
	<div class="afsw-section-options woobewoo-hidden">
<?php 
	$oSection = UtilsAfsw::getArrayValue($options, 'global', array(), 2);
?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Max number of results', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Limit of results', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::number('autocomplete[global][limit]', array(
							'value' => UtilsAfsw::getArrayValue($oSection, 'limit', 3, 1),
							'attrs' => 'min="1" class="woobewoo-width60"'
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Use only this field history', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Only this field history', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('autocomplete[global][this_field]', array(
							'checked' => UtilsAfsw::getArrayValue($oSection, 'this_field', 0, 1)
						));
						?>
				</div>
			</div>
		</div>
	<?php 
		$showImages = UtilsAfsw::getArrayValue($oSection, 'show_images', 0, 1);
	?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Show images', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Show images', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('autocomplete[global][show_images]', array(
							'checked' => $showImages
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block  afsw-media-block<?php echo $showImages ? '' : ' woobewoo-hidden'; ?>" data-parent="autocomplete[global][show_images]">
			<div class="<?php echo esc_attr($cLabel); ?>">
				<?php esc_html_e('Images type', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($cValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::selectBox('autocomplete[global][icon_type]', array(
							'options' => array(
								'search' => __('search icon', 'advanced-fuzzy-search'),
								'custom' => __('custom icon', 'advanced-fuzzy-search') . $optionPro 
							),
							'value' => UtilsAfsw::getArrayValue($oSection, 'icon_type', 'search'),
						));
						?>
				</div>
				<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'autocompleteSectionGlobalImage', array('oSection' => $oSection)); ?>
			</div>
		</div>
		<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'autocompleteSectionGlobal', array('oSection' => $oSection)); ?>
	</div>
</div>
