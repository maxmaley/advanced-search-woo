<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="afswAdminFooterShell afswHidden">
	<div class="afswAdminFooterCell">
		<?php echo esc_html(AFSW_WP_PLUGIN_NAME); ?>
		<?php esc_html_e('Version', 'advanced-fuzzy-search'); ?>:
		<a target="_blank" href="http://wordpress.org/plugins/advanced-fuzzy-search/changelog/"><?php echo esc_html(AFSW_VERSION); ?></a>
	</div>
	<div class="afswAdminFooterCell">|</div>
	<?php if (!FrameAfsw::_()->getModule(implode('', array('l', 'ic', 'e', 'ns', 'e')))) { ?>
	<div class="afswAdminFooterCell">
		<?php esc_html_e('Go', 'advanced-fuzzy-search'); ?>&nbsp;<a target="_blank" href="<?php echo esc_url($this->getModule()->getMainLink()); ?>"><?php esc_html_e('PRO', 'advanced-fuzzy-search'); ?></a>
	</div>
	<div class="afswAdminFooterCell">|</div>
	<?php } ?>
	<div class="afswAdminFooterCell">
		<a target="_blank" href="https://wordpress.org/support/plugin/advanced-fuzzy-search"><?php esc_html_e('Support', 'advanced-fuzzy-search'); ?></a>
	</div>
	<div class="afswAdminFooterCell">|</div>
	<div class="afswAdminFooterCell">
		Add your <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/advanced-fuzzy-search?filter=5#postform">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on wordpress.org.
	</div>
</div>
