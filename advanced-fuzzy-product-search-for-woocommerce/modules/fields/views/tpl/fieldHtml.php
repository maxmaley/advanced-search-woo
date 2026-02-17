<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css" id="afsw-css-field-<?php echo esc_attr($this->fieldId); ?>">
<?php
HtmlAfsw::echoEscapedHtml($this->css);
?>
</style>
<?php
HtmlAfsw::echoEscapedHtml($this->html);
