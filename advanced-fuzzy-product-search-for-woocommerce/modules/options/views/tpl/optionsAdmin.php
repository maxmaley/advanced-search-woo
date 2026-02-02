<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
$module = $this->getModule();
$days = $module->getScheduleDays();
$hours = $module->getScheduleHours();
$options = $this->options;
$bFull = HtmlAfsw::blockClasses('full');
$bLabel = HtmlAfsw::blockClasses('label');
$bValues = HtmlAfsw::blockClasses('values');
?>
<section class="row woobewoo-bar woobewoo-titlebar">
	<div class="col-9">
		<ul class="woobewoo-bar-controls">
			<li class="woobewoo-title-icon">
				<i class="fa fa-gear"></i>
			</li>
			<li class="woobewoo-title-text">
				<?php echo esc_html__('Plugin Settings', 'advanced-fuzzy-search'); ?>
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
	<form id="afswSettingsForm">
		<div class="woobewoo-item woobewoo-panel options-values">
			<div class="row row-options-block">
				<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
					<?php esc_html_e('Indexing', 'advanced-fuzzy-search'); ?>
				</div>
			</div>
			<div class="row row-options-block woobewoo-nosave">
				<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('For correct and fast search operation, the plugin creates index tables for product parameters. Initially, indexing must be started after setting up search fields, then index tables will be automatically rebuilt when editing/creating products. But if you have edited products with third-party plugins or methods and/or noticed that the search does not work correctly, then click this button to force the index tables to be rebuilt. If you have a lot of products, the process may take some time.', 'advanced-fuzzy-search'); ?>">
					<?php esc_html_e('Indexing products', 'advanced-fuzzy-search'); ?>
				</div>
				<div class="<?php echo esc_attr($bValues); ?>">
					<div class="options-value">
						<button class="button button-secondary button-mini" id="afswBtnRecalc"><i class="fa fa-refresh" aria-hidden="true"></i> <?php esc_html_e('Recalc', 'advanced-fuzzy-search'); ?></button>
					</div>
				</div>
			</div>
			<div class="row row-options-block">
				<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Disable automatic calculation of index tables after editing products. This can be useful if you add products only through imports. Then after importing, just do a full recalculation of the index tables once by clicking the button above.', 'advanced-fuzzy-search'); ?>">
					<?php esc_html_e('Disable automatic indexing', 'advanced-fuzzy-search'); ?>
				</div>
				<div class="<?php echo esc_attr($bValues); ?>">
					<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('disable_autoindexing', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'disable_autoindexing', false)
						));
						?>
					</div>
				</div>
			</div>
<?php
$enabled = UtilsAfsw::getArrayValue($options, 'use_schedule_indexing', false);
?>
			<div class="row row-options-block">
				<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Indexing will start at the selected time according to the schedule.', 'advanced-fuzzy-search'); ?>">
					<?php esc_html_e('Indexing on a schedule', 'advanced-fuzzy-search'); ?>
				</div>
				<div class="<?php echo esc_attr($bValues); ?>">
					<div class="options-value">
						<?php 
							HtmlAfsw::checkboxToggle('use_schedule_indexing', array(
								'checked' => $enabled
							));
							?>
					</div>
					<div class="options-value<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="use_schedule_indexing">
						<div class="options-label"><?php esc_html_e('day', 'advanced-fuzzy-search'); ?></div>
						<?php
							HtmlAfsw::selectBox('schedule_indexing_day', array(
								'options' => $days,
								'value' => UtilsAfsw::getArrayValue($options, 'schedule_indexing_day', 0, 1),
							));
							?>
					</div>
					<div class="options-value<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="use_schedule_indexing">
						<div class="options-label"><?php esc_html_e('hour', 'advanced-fuzzy-search'); ?></div>
						<?php
							HtmlAfsw::selectBox('schedule_indexing_hour', array(
								'options' => $hours,
								'value' => UtilsAfsw::getArrayValue($options, 'schedule_indexing_hour', 0, 1),
							));
							?>
					</div>
				</div>
			</div>
			<div class="row row-options-block">
				<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
					<?php esc_html_e('History', 'advanced-fuzzy-search'); ?>
				</div>
			</div>
			<div class="row row-options-block woobewoo-nosave">
				<div class="<?php echo esc_attr($bLabel); ?>">
					<?php esc_html_e('Clear history', 'advanced-fuzzy-search'); ?>
				</div>
				<div class="<?php echo esc_attr($bValues); ?>">
					<div class="options-value">
						<button class="button button-secondary button-mini" id="afswBtnHistory"><i class="fa fa-refresh" aria-hidden="true"></i> <?php esc_html_e('Clear', 'advanced-fuzzy-search'); ?></button>
					</div>
				</div>
			</div>
<?php
$enabled = UtilsAfsw::getArrayValue($options, 'use_schedule_indexing', false);
?>
			<div class="row row-options-block">
				<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Clear history will start at the selected time according to the schedule.', 'advanced-fuzzy-search'); ?>">
					<?php esc_html_e('Clear on a schedule', 'advanced-fuzzy-search'); ?>
				</div>
				<div class="<?php echo esc_attr($bValues); ?>">
					<div class="options-value">
						<?php 
							HtmlAfsw::checkboxToggle('use_schedule_history', array(
								'checked' => $enabled
							));
							?>
					</div>
					<div class="options-value<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="use_schedule_history">
						<div class="options-label"><?php esc_html_e('day', 'advanced-fuzzy-search'); ?></div>
						<?php
							HtmlAfsw::selectBox('schedule_history_day', array(
								'options' => $days,
								'value' => UtilsAfsw::getArrayValue($options, 'schedule_history_day', 0, 1),
							));
							?>
					</div>
					<div class="options-value<?php echo $enabled ? '' : ' woobewoo-hidden'; ?>" data-parent="use_schedule_history">
						<div class="options-label"><?php esc_html_e('hour', 'advanced-fuzzy-search'); ?></div>
						<?php
							HtmlAfsw::selectBox('schedule_history_hour', array(
								'options' => $hours,
								'value' => UtilsAfsw::getArrayValue($options, 'schedule_history_hour', 0, 1),
							));
							?>
					</div>
				</div>
			</div>
			<div class="row row-options-block">
				<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
					<?php esc_html_e('Logging', 'advanced-fuzzy-search'); ?>
				</div>
			</div>
			<div class="row row-options-block">
				<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Save debug messages to the WooCommerce SystemStatus Log.', 'advanced-fuzzy-search'); ?>">
					<?php esc_html_e('Enable logging', 'advanced-fuzzy-search'); ?>
				</div>
				<div class="<?php echo esc_attr($bValues); ?>">
					<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('logging', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'logging', false)
						));
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="woobewoo-clear"></div>
		<?php 
			HtmlAfsw::hidden('mod', array('value' => 'options'));
			HtmlAfsw::hidden('action', array('value' => 'saveOptions'));
			HtmlAfsw::hidden('', array('value' => UtilsAfsw::jsonEncode($this->tr_strings), 'attrs' => 'id="afswLangStringsJson" class="woobewoo-nosave"'));
		?>
	</form>
</section>
<div class="woobewoo-hidden woobewoo-nosave">
	<div id="afswDialogRecalc" title="<?php esc_attr_e('Start indexing product parameters', 'advanced-fuzzy-search'); ?>">
		<div class="afsw-info-desc">
			<?php esc_html_e('For correct and fast search operation, the plugin creates index tables for product parameters. Initially, indexing must be started after setting up search fields, then index tables will be automatically rebuilt when editing/creating products. But if you have edited products with third-party plugins or methods and/or noticed that the search does not work correctly, then click this button to force the index tables to be rebuilt. If you have a lot of products, the process may take some time.', 'advanced-fuzzy-search'); ?>
		</div>
		<div class="woobewoo-center options-values">
			<div class="options-value">
				<?php HtmlAfsw::checkboxToggle('in_cron'); ?>
				<div class="options-label"><?php esc_html_e('run in background', 'advanced-fuzzy-search'); ?></div>
			</div>
		</div>
	</div>
	<div id="afswDialogHistory" title="<?php esc_attr_e('Clear history', 'advanced-fuzzy-search'); ?>">
		<div class="afsw-info-desc">
			<?php esc_html_e('From time to time it is worth clearing the customer search history by the search fields. This will reduce the database and speed up autocomplete completion in the global history and user history sections, if you use them. The history will be cleared according to the option Keep history of the search fields.', 'advanced-fuzzy-search'); ?>
		</div>
		<div class="woobewoo-center options-values">
			<div class="options-value">
				<?php HtmlAfsw::checkboxToggle('in_cron'); ?>
				<div class="options-label"><?php esc_html_e('run in background', 'advanced-fuzzy-search'); ?></div>
			</div>
		</div>
	</div>
</div>
