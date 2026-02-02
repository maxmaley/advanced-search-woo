<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class IndexingViewAfsw extends ViewAfsw {
	public function showAdminInfo() {
		
		if ($this->getModel()->isFieldUpdated()) {
			$this->assign( 'message',
				'<b>' . esc_html__('You have edited the search field!', 'advanced-fuzzy-search') . '</b><br/>' .
				esc_html__('For the search to work correctly, it is necessary to re-index the data.', 'advanced-fuzzy-search') .
				' <a href="' . FrameAfsw::_()->getModule('adminmenu')->getTabUrl('settings') . '">' . esc_html__('Go to plugin settings', 'advanced-fuzzy-search') . '</a>'
			);
			HtmlAfsw::echoEscapedHtml($this->getContent('showAdminInfo'));
		}
	}
}
