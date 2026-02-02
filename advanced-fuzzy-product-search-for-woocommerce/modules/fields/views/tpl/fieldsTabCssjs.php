<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
		<?php esc_html_e('Custom CSS', 'advanced-fuzzy-search'); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?>">
		<div class="options-value">
			<?php
				HtmlAfsw::textarea('add_css', array(
					'value' => UtilsAfsw::getArrayValue($this->settings, 'add_css'),
					'attrs' => 'class="woobewoo-width-full"',
					'cols' => '150',
					'rows' => '7'
				));
				?>
		</div>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
		<?php esc_html_e('Custom JS', 'advanced-fuzzy-search'); ?>
	</div>
</div>
<div class="row row-options-block">
	<div class="<?php echo esc_attr($bFull); ?>">
		<div class="options-value">
			<?php
				HtmlAfsw::textarea('add_js', array(
					'value' => UtilsAfsw::getArrayValue($this->settings, 'add_js'),
					'attrs' => 'class="woobewoo-width-full"',
					'cols' => '150',
					'rows' => '7'
				));
				?>
		</div>
	</div>
</div>
<div class="woobewoo-clear"></div>
