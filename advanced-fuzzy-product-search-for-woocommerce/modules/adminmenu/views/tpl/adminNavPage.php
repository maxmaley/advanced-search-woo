<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css">
.woobewoo-main {
	display: none;
}
.woobewoo-plugin-loader {
	width: 100%;
	height: 100%;
	min-height: 400px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #f9fafb;
}
.woobewoo-plugin-loader div {
	font-size: 15px;
	color: #6b7280;
	display: flex;
	align-items: center;
	gap: 12px;
	font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}
.woobewoo-plugin-loader i {
	color: #6366f1;
}
.afsw-nav-header {
	padding: 24px 20px 20px;
	border-bottom: 1px solid #e5e7eb;
	margin-bottom: 8px;
}
.afsw-nav-logo {
	display: flex;
	align-items: center;
	gap: 12px;
}
.afsw-nav-logo-icon {
	width: 40px;
	height: 40px;
	background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	font-size: 18px;
	box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
}
.afsw-nav-logo-text {
	font-size: 15px;
	font-weight: 600;
	color: #111827;
	line-height: 1.3;
}
.afsw-nav-logo-sub {
	font-size: 11px;
	font-weight: 400;
	color: #9ca3af;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.afsw-nav-section {
	padding: 0 12px;
	margin-top: 8px;
}
.afsw-nav-section-title {
	font-size: 11px;
	font-weight: 600;
	color: #9ca3af;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	padding: 12px 16px 8px;
}
</style>
<div class="woobewoo-wrap">
	<div class="woobewoo-plugin woobewoo-main">
		<section class="woobewoo-content">
			<nav class="woobewoo-navigation woobewoo-sticky <?php DispatcherAfsw::doAction('adminMainNavClassAdd'); ?>">
				<div class="afsw-nav-header">
					<div class="afsw-nav-logo">
						<div class="afsw-nav-logo-icon">
							<i class="fa fa-search"></i>
						</div>
						<div>
							<div class="afsw-nav-logo-text">Fuzzy Search</div>
							<div class="afsw-nav-logo-sub">for WooCommerce</div>
						</div>
					</div>
				</div>
				<div class="afsw-nav-section">
					<ul>
						<?php foreach ($this->tabs as $tabKey => $t) { ?>
							<?php
							if (isset($t['hidden']) && $t['hidden']) {
								continue;
							}
							?>
							<li class="woobewoo-tab-<?php echo esc_attr($tabKey); ?> <?php echo ( ( $this->activeTab == $tabKey || in_array($tabKey, $this->activeParentTabs) ) ? 'active' : '' ); ?>">
								<a href="<?php echo esc_url($t['url']); ?>" title="<?php echo esc_attr($t['label']); ?>"<?php echo empty($t['blank']) ? '' : ' target="_blank"'; ?>>
									<?php if (isset($t['fa_icon'])) { ?>
										<i class="fa <?php echo esc_attr($t['fa_icon']); ?>"></i>
									<?php } elseif (isset($t['wp_icon'])) { ?>
										<i class="dashicons-before <?php echo esc_attr($t['wp_icon']); ?>"></i>
									<?php } elseif (isset($t['icon'])) { ?>
										<i class="<?php echo esc_attr($t['icon']); ?>"></i>
									<?php } ?>
									<span class="sup-tab-label"><?php echo esc_html($t['label']); ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
			</nav>
			<div class="woobewoo-container woobewoo-<?php echo esc_attr($this->activeTab); ?>">
				<?php HtmlAfsw::echoEscapedHtml($this->content); ?>
				<div class="clear"></div>
			</div>
		</section>
	</div>
	<div class="woobewoo-plugin-loader">
		<div><i class="fa fa-spinner fa-spin"></i> Loading...</div>
	</div>
</div>

