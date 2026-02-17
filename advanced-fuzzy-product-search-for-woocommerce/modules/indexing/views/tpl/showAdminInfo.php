<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="error notice">
	<p><?php HtmlAfsw::echoEscapedHtml($this->message); ?></p>
</div>
