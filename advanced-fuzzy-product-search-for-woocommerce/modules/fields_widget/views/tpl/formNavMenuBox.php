<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $nav_menu_selected_id;
?>
<div id="afsw-search-link" class="posttypediv">
	<div id="tabs-panel-afsw-search-link" class="tabs-panel tabs-panel-view-all tabs-panel-active">
		<ul id="afsw-search-link-checklist" class="categorychecklist form-no-clear">
			<li>
				<label class="menu-item-title">
					<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]"
						value="-1"/> <?php echo esc_html__( 'Advanced Fuzzy Search Field', 'advanced-fuzzy-search' ); ?>
				</label>
				<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom"/>
				<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="afsw_search_field_nav_item"/>
				<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="afsw-search-field-nav-item"/>
			</li>
		</ul>
	</div>
	<p class="button-controls">
		<span class="add-to-menu">
			<button type="submit"<?php disabled($nav_menu_selected_id, 0); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'woocommerce' ); ?>"
				name="add-post-type-menu-item" id="submit-afsw-search-link"><?php esc_html_e( 'Add to menu', 'woocommerce' ); ?></button>
			<span class="spinner"></span>
		</span>
	</p>
</div>
