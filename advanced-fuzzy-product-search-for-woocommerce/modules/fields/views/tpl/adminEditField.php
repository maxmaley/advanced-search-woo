<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$bLabel = HtmlAfsw::blockClasses('label');
$bValues = HtmlAfsw::blockClasses('values');
$bFull = HtmlAfsw::blockClasses('full');
HtmlAfsw::setColType('compact');
$cLabel = HtmlAfsw::blockClasses('label');
$cValues = HtmlAfsw::blockClasses('values');
$proClass = $this->is_pro ? '' : ' woobewoo-show-pro';
$optionPro = ( $this->is_pro ? '' : ' PRO' );
?>
<section class="row woobewoo-bar woobewoo-titlebar">
	<div class="col-9">
		<ul class="woobewoo-bar-controls">
			<li class="woobewoo-title-icon">
				<i class="fa fa-search"></i>
			</li>
			<li class="woobewoo-title-text">
				<?php echo esc_html__('Field name: ', 'advanced-fuzzy-search'); ?>
			</li>
			<li class="woobewoo-title-text p-0">
				<span id="afswTitleShell" class="afsw-title-shell" title="<?php esc_attr_e('Click to edit', 'advanced-fuzzy-search'); ?>">
					<span class="afsw-title-text"><?php echo esc_html($this->settings['title']); ?></span>
					<?php
						HtmlAfsw::text('title', array(
							'value' => $this->settings['title'],
							'attrs' => 'class="afswHidden"',
							'required' => true,
						));
						?>
					<i class="fa fa-fw fa-pencil"></i>
				</span>
			</li>
		</ul>
	</div>
	<div class="afsw-main-buttons col-3">
		<ul class="woobewoo-control-buttons">
			<li>
				<button id="afswBtnSave" class="button button-primary">
					<i class="fa fa-floppy-o" aria-hidden="true"></i><span><?php esc_html_e('Save', 'advanced-fuzzy-search'); ?></span>
				</button>
			</li>
		</ul>
	</div>
	<div class="woobewoo-clear"></div>
</section>
<section>
	<div class="row woobewoo-menu-tabs">
		<div class="col-12">
			<ul class="woobewoo-grbtn afsw-main-tabs">
				<?php foreach ($this->main_tabs as $key => $data) { ?>
					<li>
						<a href="#block-tab-<?php echo esc_attr($key); ?>" data-model="<?php echo esc_attr($key); ?>" class="button <?php echo ( !$data['pro'] || $this->is_pro ? '' : 'woobewoo-show-pro ' ) . ( empty($data['class']) ? '' : esc_attr($data['class']) ); ?>">
							<i class="fa fa-fw <?php echo esc_attr($data['icon']); ?>"></i><?php echo esc_html($data['label']); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<form id="afswEditFieldForm">
		<div class="woobewoo-item woobewoo-panel">
			<div class="afsw-main-tab-content">
				<?php foreach ($this->main_tabs as $key => $data) { ?>
					<div class="block-tab options-values" id="block-tab-<?php echo esc_attr($key); ?>">
						<?php 
						if ($data['pro']) {
							if ($this->is_pro) {
								DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTab' . strFirstUpAfsw($key), array());
							} else { 
								include 'fieldsProFeature.php';
							}
						} else {
							include_once 'fieldsTab' . strFirstUpAfsw($key) . '.php';
						}
						?>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="woobewoo-clear"></div>
		<?php 
			HtmlAfsw::hidden('mod', array('value' => 'fields'));
			HtmlAfsw::hidden('action', array('value' => 'saveField'));
			HtmlAfsw::hidden('id', array('value' => $this->field_id));
			HtmlAfsw::hidden('', array('value' => UtilsAfsw::jsonEncode($this->tr_strings), 'attrs' => 'id="afswLangStringsJson" class="woobewoo-nosave"'));
		?>
	</form>
</section>
