<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$oScope = UtilsAfsw::getArrayValue($options, 'category', array(), 2);
$modeS = UtilsAfsw::getArrayValue($oScope, 'mode', 'fw');
$isWord = ( 'fw' == $modeS || 'pw' == $modeS );
?>
<div class="afsw-section-options woobewoo-hidden">
	<div class="row row-options-block">
		<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Select search mode. Note that the fuzzy logic only works for the modes: full words and part words.', 'advanced-fuzzy-search'); ?>">
			<?php esc_html_e('Search mode', 'advanced-fuzzy-search'); ?>
			</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php 
					HtmlAfsw::selectBox('search[category][mode]', array(
							'options' => $searchModes,
							'value' => $modeS,
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block<?php echo $isWord ? '' : ' woobewoo-hidden'; ?>" data-select="search[category][mode]" data-select-value="fw|pw">
		<div class="<?php echo esc_attr($cLabel); ?>">
			<?php esc_html_e('Search logic', 'advanced-fuzzy-search'); ?>
			</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php 
					HtmlAfsw::selectBox('search[category][logic]', array(
							'options' => $searchLogic,
							'value' => UtilsAfsw::getArrayValue($oScope, 'logic', 'or'),
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block">
		<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('If enabled, it will also search in parent categories. Еnable this option only if you have products that belong to a child category, but are not included in the parent categories, but you need the search to be performed on the parent categories as well.', 'advanced-fuzzy-search'); ?>">
			<?php esc_html_e('Search in parent categories', 'advanced-fuzzy-search'); ?>
		</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php
					HtmlAfsw::checkboxToggle('search[category][by_parent]', array(
						'checked' => UtilsAfsw::getArrayValue($oScope, 'by_parent', false, 1)
					));
					?>
			</div>
		</div>
	</div>
</div>
