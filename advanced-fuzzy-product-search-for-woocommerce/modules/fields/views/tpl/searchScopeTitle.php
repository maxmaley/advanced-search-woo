<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$oScope = UtilsAfsw::getArrayValue($options, 'title', array(), 2);
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
					HtmlAfsw::selectBox('search[title][mode]', array(
							'options' => $searchModes,
							'value' => $modeS,
						));
					?>
			</div>
		</div>
	</div>
	<div class="row row-options-block<?php echo $isWord ? '' : ' woobewoo-hidden'; ?>" data-select="search[title][mode]" data-select-value="fw|pw">
		<div class="<?php echo esc_attr($cLabel); ?>">
			<?php esc_html_e('Search logic', 'advanced-fuzzy-search'); ?>
			</div>
		<div class="<?php echo esc_attr($cValues); ?>">
			<div class="options-value">
				<?php 
					HtmlAfsw::selectBox('search[title][logic]', array(
							'options' => $searchLogic,
							'value' => UtilsAfsw::getArrayValue($oScope, 'logic', 'or'),
						));
					?>
			</div>
		</div>
	</div>
</div>
