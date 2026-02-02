<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woobewoo-pro-desc">
	<?php
		/* translators: %s url */ 
		echo sprintf(esc_html__('Please be advised that this feature available only in PRO version. You can %s today and have all PRO features of plugin!', 'advanced-fuzzy-search'), '<a href="' . esc_url($this->pro_url) . '" class="button button-mini" target="_blank">' . esc_html__('Get Pro', 'advanced-fuzzy-search') . '</a>');
	?>
</div>
