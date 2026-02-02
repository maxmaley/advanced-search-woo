<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$oScope = UtilsAfsw::getArrayValue($options, 'attribute', array(), 2);
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
					HtmlAfsw::selectBox('search[attribute][mode]', array(
							'options' => $searchModes,
							'value' => $modeS,
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block<?php echo $isWord ? '' : ' woobewoo-hidden'; ?>" data-select="search[attribute][mode]" data-select-value="fw|pw">
		<div class="<?php echo esc_attr($cLabel); ?>">
			<?php esc_html_e('Search logic', 'advanced-fuzzy-search'); ?>
			</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php 
					HtmlAfsw::selectBox('search[attribute][logic]', array(
							'options' => $searchLogic,
							'value' => UtilsAfsw::getArrayValue($oScope, 'logic', 'or'),
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block">
		<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Select product attributes', 'advanced-fuzzy-search'); ?>">
			<?php esc_html_e('Attributes', 'advanced-fuzzy-search'); ?>
			</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php 
					HtmlAfsw::selectlist('search[attribute][list]', array(
						'options' => $module->getModel()->getAttributesDisplay(),
						'attrs' => 'data-placeholder="' . __('Select attributes', 'advanced-fuzzy-search') . '"',
						'value' => UtilsAfsw::getArrayValue($oScope, 'list'),
					));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block">
		<div class="<?php echo esc_attr($cLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('If enabled, it will also search by local attributes. Enable only if you actually have local attributes in products and if you realy need to search by them, as indexing these attributes is very resource intensive.', 'advanced-fuzzy-search'); ?>">
			<?php esc_html_e('Search local attributes', 'advanced-fuzzy-search'); ?>
		</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php
					HtmlAfsw::checkboxToggle('search[attribute][by_local]', array(
						'checked' => UtilsAfsw::getArrayValue($oScope, 'by_local', false, 1)
					));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block">
		<div class="<?php echo esc_attr($cLabel); ?>">
			<?php 
				esc_html_e('For variable products', 'advanced-fuzzy-search'); 
			?>
		</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php 
					HtmlAfsw::selectBox('search[attribute][for_variable]', array(
							'options' => $forVariable,
							'value' => UtilsAfsw::getArrayValue($oScope, 'for_variable'),
						));
					?>
			</div>
		</div>
	</div>
</div>
