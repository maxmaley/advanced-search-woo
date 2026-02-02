<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="woobewoo-bar woobewoo-titlebar">
	<ul class="woobewoo-bar-controls">
		<li class="woobewoo-title-icon">
			<i class="fa fa-list"></i>
		</li>
		<li class="woobewoo-title-text">
			<?php esc_html_e('Fields List', 'advanced-fuzzy-search'); ?>
		</li>
	</ul>
	<div class="woobewoo-clear"></div>
</section>
<section>
	<div class="woobewoo-item woobewoo-panel">
		<div class="woobewoo-main-container">
			<div class="woobewoo-table-list">
				<table id="afswFieldsList" data-settings="<?php echo esc_attr(htmlspecialchars(json_encode($this->settings), ENT_QUOTES, 'UTF-8')); ?>">
					<thead>
						<tr>
							<th><input type="checkbox" class="afswCheckAll"></th>
							<th><?php esc_html_e('ID', 'advanced-fuzzy-search'); ?></th>
							<th><?php esc_html_e('Title', 'advanced-fuzzy-search'); ?></th>
							<th><?php esc_html_e('Shortcode', 'advanced-fuzzy-search'); ?></th>
							<th><?php esc_html_e('Functions', 'advanced-fuzzy-search'); ?></th>
						</tr>
					</thead>
				</table>
			</div>
			<div class="woobewoo-clear"></div>
		</div>
		<div class="woobewoo-hidden">
			<div id="afswAddDialog" class="woobewoo-plugin" title="<?php echo esc_attr__('Enter product search field name', 'advanced-fuzzy-search'); ?>">
				<div>
					<form id="tableForm">
						<input id="addDialog_title" class="woobewoo-text woobewoo-width-full" type="text"/>
					</form>
					<div id="formError" class="woobewoo-hidden">
						<p></p>
					</div>
				</div>
			</div>
			<?php 
			if ($this->is_pro) {
				DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsFormMigration', array());
			}
			?>
		</div>
		<div class="woobewoo-clear"></div>
	</div>
</section>
