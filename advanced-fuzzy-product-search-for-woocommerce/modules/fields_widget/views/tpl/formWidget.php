<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="afswWidgetRow afswMapRow">
	<div class="afswWidgetRowCell afswFirstCell">
		<label for="<?php echo esc_attr($this->widget->get_field_id('id')); ?>"><?php esc_html_e('Select field', 'advanced-fuzzy-search'); ?>:</label>
	</div>
	<div class="afswWidgetRowCell afswLastCell">
		<?php 
			HtmlAfsw::selectbox($this->widget->get_field_name('id'), array(
				'attrs' => 'id="' . $this->widget->get_field_id('id') . '"',
				'value' => isset($this->data['id']) ? $this->data['id'] : 0,
				'options' => $this->fields,
			));
			?>
	</div>
</div>
<?php
$js = 'jQuery(document).ready(function(){
		jQuery(\'.afswWidgetRowCell select option[value="0"]\').prop(\'disabled\',true);
	});';
FrameAfsw::_()->printInlineJs($js);
?>
