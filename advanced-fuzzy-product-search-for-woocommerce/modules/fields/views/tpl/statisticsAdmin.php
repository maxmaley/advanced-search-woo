<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="woobewoo-bar woobewoo-titlebar">
	<ul class="woobewoo-bar-controls">
		<li class="woobewoo-title-icon">
			<i class="fa fa-line-chart"></i>
		</li>
		<li class="woobewoo-title-text">
			<?php echo esc_html__('Statistics', 'advanced-fuzzy-search'); ?>
		</li>
	</ul>
	<div class="woobewoo-clear"></div>
</section>
<section>
	<div class="woobewoo-item woobewoo-panel woobewoo-pro-panel">
		<div class="woobewoo-main-container">
			<div class="woobewoo-table-list">
				<?php include 'fieldsProFeature.php'; ?>
			</div>
			<div class="woobewoo-clear"></div>
		</div>
		<div class="woobewoo-clear"></div>
	</div>
</section>
