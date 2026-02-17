<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="description description-wide afsw-search-description">
	<label for="edit-menu-item-afsw-search-<?php echo esc_attr($this->item_id); ?>">
		<?php 
			esc_html_e('Select field', 'advanced-fuzzy-search' );
			HtmlAfsw::selectbox('menu-item-afsw-search[' . $this->item_id . ']', array(
				'attrs' => 'id="edit-menu-item-afsw-search-' . $this->item_id . '" class="afsw-search-select"',
				'value' => $this->field_id,
				'options' => $this->fields,
			));
			?>
	</label>
</p>
