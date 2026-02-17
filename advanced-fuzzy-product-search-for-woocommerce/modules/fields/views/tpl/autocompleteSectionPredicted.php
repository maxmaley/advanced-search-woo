<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
				<div class="afsw-section" data-type="predicted">
					<div class="afsw-section-header">
						<div class="options-value">
						<?php 
							HtmlAfsw::checkboxToggle('autocomplete[show_predicted]', array(
								'checked' => $this->is_pro && UtilsAfsw::getArrayValue($options, 'show_predicted', false),
								'attrs' => $this->is_pro ? '' : 'disabled'
							));
							?>
						</div>
						<div class="afsw-section-title"><?php esc_html_e('Predicted products', 'advanced-fuzzy-search'); ?></div>
						<?php if ($this->is_pro) { ?>
							<a href="#" class="afsw-section-toggle"><i class="fa fa-chevron-down"></i></a>
						<?php 
						} else {
							HtmlAfsw::proOptionLink();
						}
						?>
					</div>
					<div class="afsw-section-options woobewoo-hidden">
<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'autocompleteSectionPredicted', array('options' => $options)); ?>
					</div>
				</div>
